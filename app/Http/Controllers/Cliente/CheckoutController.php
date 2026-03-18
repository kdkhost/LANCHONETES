<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Loja;
use App\Models\Pedido;
use App\Models\Pagamento;
use App\Services\PedidoService;
use App\Services\MercadoPagoService;
use App\Services\EvolutionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class CheckoutController extends Controller
{
    public function __construct(
        private PedidoService $pedidoService,
        private EvolutionApiService $evolutionService
    ) {}

    public function index()
    {
        $loja    = app('loja_atual');
        $usuario = Auth::user();
        $enderecos = $usuario->enderecos()->where('ativo', true)->get();

        $temEntregador = $loja->temEntregadorDisponivel();

        return view('cliente.checkout', compact('loja', 'enderecos', 'temEntregador'));
    }

    public function calcularFrete(Request $request)
    {
        $request->validate([
            'cep'       => 'required|string|size:8',
            'bairro'    => 'nullable|string',
            'cidade'    => 'nullable|string',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $loja           = app('loja_atual');
        $entregaService = app(\App\Services\EntregaService::class);
        $resultado      = $entregaService->calcularTaxa($loja, $request->all());

        return response()->json($resultado);
    }

    public function criar(Request $request)
    {
        $request->validate([
            'carrinho'              => 'required|array|min:1',
            'carrinho.*.produto_id' => 'required|exists:produtos,id',
            'carrinho.*.quantidade' => 'required|integer|min:1',
            'tipo_entrega'          => 'required|in:entrega,retirada',
            'metodo_pagamento'      => 'required|string',
            'gorjeta'               => 'nullable|numeric|min:0|max:500',
            'endereco.cep'          => 'required_if:tipo_entrega,entrega',
            'endereco.logradouro'   => 'required_if:tipo_entrega,entrega',
            'endereco.numero'       => 'required_if:tipo_entrega,entrega',
            'endereco.bairro'       => 'required_if:tipo_entrega,entrega',
            'endereco.cidade'       => 'required_if:tipo_entrega,entrega',
            'endereco.estado'       => 'required_if:tipo_entrega,entrega',
        ], [
            'carrinho.required'           => 'O carrinho está vazio.',
            'metodo_pagamento.required'   => 'Selecione um método de pagamento.',
            'endereco.cep.required_if'    => 'Informe o CEP.',
            'endereco.logradouro.required_if' => 'Informe o logradouro.',
            'endereco.numero.required_if' => 'Informe o número.',
            'endereco.bairro.required_if' => 'Informe o bairro.',
        ]);

        $loja   = app('loja_atual');
        $metodo = $request->metodo_pagamento;

        if ($metodo === 'pagamento_entrega' && !$loja->aceita_pagamento_entrega) {
            return response()->json(['erro' => 'Pagamento na entrega não disponível.'], 422);
        }
        if ($metodo === 'pagamento_entrega' && !$loja->temEntregadorDisponivel()) {
            return response()->json(['erro' => 'Não há entregador disponível para pagamento na entrega.'], 422);
        }

        try {
            $pedido = $this->pedidoService->criarPedido(Auth::user(), $loja, $request->all());

            $mpService = new MercadoPagoService($loja);
            $resultado = match ($metodo) {
                'pix'            => $this->processarPix($pedido, $mpService),
                'cartao_credito',
                'cartao_debito'  => $this->processarCartao($pedido, $mpService, $request->all()),
                'pagamento_entrega',
                'dinheiro'       => $this->processarPagamentoEntrega($pedido),
                default          => throw new Exception('Método de pagamento inválido.'),
            };

            if ($loja->notificacoes_whatsapp) {
                $this->evolutionService->notificarPedidoNovo($pedido->load('itens', 'usuario', 'pagamento'));
            }

            return response()->json(array_merge(['sucesso' => true, 'pedido_id' => $pedido->id, 'numero' => $pedido->numero], $resultado));

        } catch (Exception $e) {
            return response()->json(['erro' => $e->getMessage()], 422);
        }
    }

    private function processarPix(Pedido $pedido, MercadoPagoService $mp): array
    {
        $resultado = $mp->criarPagamentoPix($pedido);
        if (!$resultado['sucesso']) throw new Exception($resultado['erro']);

        Pagamento::create([
            'pedido_id'       => $pedido->id,
            'metodo'          => 'pix',
            'status'          => 'pendente',
            'valor'           => $pedido->total,
            'mp_payment_id'   => $resultado['payment_id'],
            'pix_qr_code'     => $resultado['qr_code'],
            'pix_qr_code_base64' => $resultado['qr_code_base64'],
            'pix_expiracao'   => $resultado['expiracao'],
        ]);

        return [
            'tipo'           => 'pix',
            'qr_code'        => $resultado['qr_code'],
            'qr_code_base64' => $resultado['qr_code_base64'],
            'expiracao'      => $resultado['expiracao'],
        ];
    }

    private function processarCartao(Pedido $pedido, MercadoPagoService $mp, array $dados): array
    {
        $resultado = $mp->criarPagamentoCartao($pedido, $dados);
        if (!$resultado['sucesso']) throw new Exception($resultado['erro']);

        $status = $resultado['aprovado'] ? 'aprovado' : 'em_analise';
        Pagamento::create([
            'pedido_id'              => $pedido->id,
            'metodo'                 => $dados['metodo_pagamento'],
            'status'                 => $status,
            'valor'                  => $pedido->total,
            'mp_payment_id'          => $resultado['payment_id'],
            'mp_status'              => $resultado['status'],
            'mp_status_detail'       => $resultado['status_detail'],
            'parcelas'               => $dados['parcelas'] ?? 1,
            'bandeira_cartao'        => $dados['bandeira'] ?? null,
            'ultimos_digitos_cartao' => $dados['ultimos_digitos'] ?? null,
            'titular_cartao'         => $dados['titular'] ?? null,
            'pago_em'                => $resultado['aprovado'] ? now() : null,
        ]);

        if ($resultado['aprovado']) {
            $pedido->atualizarStatus('pagamento_aprovado');
        }

        return ['tipo' => 'cartao', 'aprovado' => $resultado['aprovado'], 'status' => $resultado['status_detail']];
    }

    private function processarPagamentoEntrega(Pedido $pedido): array
    {
        Pagamento::create([
            'pedido_id' => $pedido->id,
            'metodo'    => 'pagamento_entrega',
            'status'    => 'pendente',
            'valor'     => $pedido->total,
        ]);
        $pedido->atualizarStatus('confirmado');
        return ['tipo' => 'entrega'];
    }

    public function sucesso(Pedido $pedido)
    {
        return view('cliente.pedido-sucesso', compact('pedido'));
    }

    public function falha(Pedido $pedido)
    {
        return view('cliente.pedido-falha', compact('pedido'));
    }

    public function pendente(Pedido $pedido)
    {
        return view('cliente.pedido-pendente', compact('pedido'));
    }

    public function verificarPagamento(Pedido $pedido)
    {
        $pagamento = $pedido->pagamento;
        if (!$pagamento || !$pagamento->mp_payment_id) {
            return response()->json(['status' => $pagamento?->status ?? 'pendente']);
        }
        $loja  = $pedido->loja;
        $mp    = new MercadoPagoService($loja);
        $res   = $mp->consultarPagamento($pagamento->mp_payment_id);
        return response()->json(['status' => $res['status'] ?? 'pendente', 'aprovado' => $res['aprovado'] ?? false]);
    }
}
