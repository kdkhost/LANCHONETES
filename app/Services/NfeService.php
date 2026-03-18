<?php

namespace App\Services;

use App\Models\Loja;
use App\Models\Pedido;
use App\Models\NotaFiscal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class NfeService
{
    private string $baseUrl;
    private string $token;
    private string $ambiente;

    public function __construct(private Loja $loja)
    {
        $this->token    = $loja->nfe_token ?? '';
        $this->ambiente = $loja->nfe_ambiente ?? 'homologacao';
        $this->baseUrl  = $this->ambiente === 'producao'
            ? 'https://api.focusnfe.com.br/v2'
            : 'https://homologacao.focusnfe.com.br/v2';
    }

    public function emitir(Pedido $pedido): NotaFiscal
    {
        if (!$this->loja->nfe_ativo || !$this->token) {
            throw new Exception('NFe não configurada para esta loja.');
        }

        $pedido->load(['itens.produto', 'usuario', 'pagamento']);

        $nota = NotaFiscal::create([
            'pedido_id'     => $pedido->id,
            'loja_id'       => $this->loja->id,
            'serie'         => $this->loja->nfe_serie,
            'numero'        => $this->loja->nfe_numero_atual,
            'tipo'          => 'nfce',
            'ambiente'      => $this->ambiente,
            'status'        => 'processando',
            'valor_total'   => $pedido->total,
            'dados_emissao' => $this->montarDados($pedido),
        ]);

        try {
            $referencia = 'pedido-' . $pedido->id . '-' . time();
            $dados      = $this->montarDados($pedido);

            $resposta = Http::withBasicAuth($this->token, '')
                ->timeout(30)
                ->post("{$this->baseUrl}/nfce?ref={$referencia}&completo=1", $dados);

            $body = $resposta->json();

            if ($resposta->successful() || $resposta->status() === 201) {
                $nota->update([
                    'status'         => 'autorizada',
                    'chave_acesso'   => $body['chave_nfe'] ?? null,
                    'protocolo'      => $body['numero_protocolo'] ?? null,
                    'url_danfe'      => $body['danfe_url'] ?? null,
                    'resposta_sefaz' => $body,
                    'emitida_em'     => now(),
                ]);

                $this->loja->increment('nfe_numero_atual');
            } else {
                $nota->update([
                    'status'         => 'rejeitada',
                    'motivo_rejeicao'=> $body['mensagem'] ?? 'Erro desconhecido',
                    'resposta_sefaz' => $body,
                ]);
            }
        } catch (Exception $e) {
            Log::error('NfeService::emitir erro', ['pedido_id' => $pedido->id, 'error' => $e->getMessage()]);
            $nota->update(['status' => 'rejeitada', 'motivo_rejeicao' => $e->getMessage()]);
        }

        return $nota->fresh();
    }

    public function cancelar(NotaFiscal $nota, string $justificativa): bool
    {
        if (!$nota->estaAutorizada()) return false;

        try {
            $resposta = Http::withBasicAuth($this->token, '')
                ->delete("{$this->baseUrl}/nfce/{$nota->chave_acesso}", [
                    'justificativa' => $justificativa,
                ]);

            if ($resposta->successful()) {
                $nota->update([
                    'status'       => 'cancelada',
                    'cancelada_em' => now(),
                ]);
                return true;
            }
        } catch (Exception $e) {
            Log::error('NfeService::cancelar erro', ['nota_id' => $nota->id, 'error' => $e->getMessage()]);
        }

        return false;
    }

    public function consultarStatus(NotaFiscal $nota): array
    {
        try {
            $resposta = Http::withBasicAuth($this->token, '')
                ->get("{$this->baseUrl}/nfce/{$nota->chave_acesso}");

            return $resposta->json() ?? [];
        } catch (Exception $e) {
            return ['erro' => $e->getMessage()];
        }
    }

    private function montarDados(Pedido $pedido): array
    {
        $itens = $pedido->itens->map(function ($item, $idx) {
            return [
                'numero_item'            => $idx + 1,
                'codigo_produto'         => (string) ($item->produto_id ?? $idx + 1),
                'descricao'              => $item->produto_nome,
                'codigo_ncm'             => '21069090',
                'quantidade_comercial'   => $item->quantidade,
                'unidade_comercial'      => 'UN',
                'valor_unitario_comercial' => (float) $item->produto_preco,
                'valor_bruto'            => (float) $item->subtotal,
                'icms_origem'            => 0,
                'icms_modalidade_base_calculo' => 3,
                'icms_aliquota'          => 0,
                'icms_valor'             => 0,
                'pis_situacao_tributaria'  => '07',
                'cofins_situacao_tributaria' => '07',
            ];
        })->toArray();

        $pagamentos = [];
        if ($pedido->pagamento) {
            $meioPag = match($pedido->pagamento->metodo) {
                'pix'                => '17',
                'cartao_credito'     => '03',
                'cartao_debito'      => '04',
                'dinheiro'           => '01',
                'pagamento_entrega'  => '01',
                default              => '99',
            };
            $pagamentos[] = [
                'forma_pagamento' => $meioPag,
                'valor'           => (float) $pedido->total,
            ];
        } else {
            $pagamentos[] = ['forma_pagamento' => '99', 'valor' => (float) $pedido->total];
        }

        return [
            'cnpj_emitente'         => preg_replace('/\D/', '', $this->loja->nfe_cnpj_emitente ?? ''),
            'nome_emitente'         => $this->loja->nfe_razao_social ?? $this->loja->nome,
            'data_emissao'          => now()->toIso8601String(),
            'modalidade_frete'      => 9,
            'local_destino'         => 1,
            'presenca_comprador'    => 4,
            'natureza_operacao'     => 'Venda de Mercadoria',
            'forma_pagamento'       => 0,
            'serie'                 => (int) $this->loja->nfe_serie,
            'numero'                => $this->loja->nfe_numero_atual,
            'items'                 => $itens,
            'formas_pagamento'      => $pagamentos,
            'valor_produtos'        => (float) $pedido->subtotal,
            'valor_desconto'        => (float) ($pedido->desconto ?? 0),
            'valor_total'           => (float) $pedido->total,
        ];
    }
}
