<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Cupom;
use App\Models\Loja;
use App\Models\Usuario;
use App\Models\Pagamento;
use App\Events\PedidoStatusAtualizado;
use Illuminate\Support\Facades\DB;
use Exception;

class PedidoService
{
    public function __construct(
        private EntregaService $entregaService,
        private MercadoPagoService $mercadoPagoService,
        private EvolutionApiService $evolutionService
    ) {}

    public function criarPedido(Usuario $usuario, Loja $loja, array $dados): Pedido
    {
        return DB::transaction(function () use ($usuario, $loja, $dados) {
            $carrinho   = $dados['carrinho'];
            $subtotal   = 0;
            $itensSalvar = [];

            foreach ($carrinho as $itemCarrinho) {
                $produto = Produto::findOrFail($itemCarrinho['produto_id']);
                if (!$produto->estaDisponivel()) {
                    throw new Exception("Produto '{$produto->nome}' não está disponível.");
                }

                $precoItem    = $produto->preco_atual;
                $precoAdic    = 0;
                $adicionaisSalvar = [];

                foreach ($itemCarrinho['adicionais'] ?? [] as $adicionalItem) {
                    $adicional  = \App\Models\Adicional::findOrFail($adicionalItem['id']);
                    $qtdAdic    = max(1, (int) ($adicionalItem['quantidade'] ?? 1));
                    $subAdic    = $adicional->preco * $qtdAdic;
                    $precoAdic += $subAdic;
                    $adicionaisSalvar[] = [
                        'adicional_id'    => $adicional->id,
                        'adicional_nome'  => $adicional->nome,
                        'adicional_preco' => $adicional->preco,
                        'quantidade'      => $qtdAdic,
                        'subtotal'        => $subAdic,
                    ];
                }

                $qtd      = max(1, (int) $itemCarrinho['quantidade']);
                $subItem  = ($precoItem + $precoAdic) * $qtd;
                $subtotal += $subItem;

                $itensSalvar[] = [
                    'produto_id'        => $produto->id,
                    'produto_nome'      => $produto->nome,
                    'produto_descricao' => $produto->descricao,
                    'produto_preco'     => $precoItem + $precoAdic,
                    'quantidade'        => $qtd,
                    'subtotal'          => $subItem,
                    'observacoes'       => $itemCarrinho['observacoes'] ?? null,
                    'adicionais'        => $adicionaisSalvar,
                ];
            }

            if ($subtotal < $loja->pedido_minimo) {
                throw new Exception("Pedido mínimo é R$ " . number_format($loja->pedido_minimo, 2, ',', '.'));
            }

            $tipoEntrega = $dados['tipo_entrega'] ?? 'entrega';
            $taxaEntrega = 0;
            $enderecoId  = null;
            $calcTaxa    = [];

            if ($tipoEntrega === 'entrega') {
                $dadosEndereco  = $dados['endereco'];
                $calcTaxa       = $this->entregaService->calcularTaxa($loja, $dadosEndereco);
                if (!$calcTaxa['disponivel']) {
                    throw new Exception($calcTaxa['erro'] ?? 'Endereço fora da área de entrega.');
                }
                $taxaEntrega = $calcTaxa['taxa'];
                $enderecoId  = $dadosEndereco['id'] ?? null;
            }

            $desconto = 0;
            $cupom    = null;
            if (!empty($dados['cupom_codigo'])) {
                $cupom = Cupom::where('loja_id', $loja->id)
                    ->where('codigo', strtoupper($dados['cupom_codigo']))
                    ->first();
                if ($cupom && $cupom->estaValido()) {
                    $desconto = $cupom->calcularDesconto($subtotal);
                    if ($cupom->tipo === 'frete_gratis') {
                        $taxaEntrega = 0;
                    }
                }
            }

            $total = $subtotal + $taxaEntrega - $desconto;

            $pedido = Pedido::create([
                'loja_id'             => $loja->id,
                'usuario_id'          => $usuario->id,
                'tipo_entrega'        => $tipoEntrega,
                'endereco_id'         => $enderecoId,
                'endereco_cep'        => $dados['endereco']['cep'] ?? null,
                'endereco_logradouro' => $dados['endereco']['logradouro'] ?? null,
                'endereco_numero'     => $dados['endereco']['numero'] ?? null,
                'endereco_complemento'=> $dados['endereco']['complemento'] ?? null,
                'endereco_bairro'     => $dados['endereco']['bairro'] ?? null,
                'endereco_cidade'     => $dados['endereco']['cidade'] ?? null,
                'endereco_estado'     => $dados['endereco']['estado'] ?? null,
                'endereco_latitude'   => $dados['endereco']['latitude'] ?? null,
                'endereco_longitude'  => $dados['endereco']['longitude'] ?? null,
                'subtotal'            => $subtotal,
                'taxa_entrega'        => $taxaEntrega,
                'desconto'            => $desconto,
                'total'               => $total,
                'observacoes'         => $dados['observacoes'] ?? null,
                'cupom_codigo'        => $cupom?->codigo,
                'tempo_estimado_min'  => $calcTaxa['tempo_max'] ?? $loja->tempo_entrega_max,
                'status'              => 'aguardando_pagamento',
            ]);

            foreach ($itensSalvar as $itemDados) {
                $adics = $itemDados['adicionais'];
                unset($itemDados['adicionais']);
                $item = $pedido->itens()->create($itemDados);
                foreach ($adics as $adicDados) {
                    $item->adicionais()->create($adicDados);
                }
            }

            if ($cupom) {
                $cupom->increment('usos_realizados');
            }

            return $pedido;
        });
    }

    public function atualizarStatus(Pedido $pedido, string $novoStatus, ?string $obs = null): void
    {
        $pedido->atualizarStatus($novoStatus, $obs);
        $pedido->load('loja', 'usuario', 'itens', 'pagamento', 'entrega.entregador.usuario');

        if ($pedido->loja->notificacoes_whatsapp) {
            $this->evolutionService->notificarStatusPedido($pedido, $novoStatus);
        }

        if ($novoStatus === 'confirmado' && $pedido->loja->notificacoes_whatsapp) {
            $this->evolutionService->notificarPedidoCozinha($pedido);
        }

        if ($novoStatus === 'saiu_para_entrega' && $pedido->tipo_entrega === 'entrega') {
            $this->entregaService->criarEntrega($pedido);
        }

        if ($novoStatus === 'entregue') {
            $pedido->update(['entregue_em' => now()]);
            if ($pedido->loja->notificacoes_whatsapp) {
                $this->evolutionService->notificarSolicitacaoAvaliacao($pedido);
            }
        }

        broadcast(new PedidoStatusAtualizado($pedido))->toOthers();
    }
}
