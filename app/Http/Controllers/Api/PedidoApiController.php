<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Entrega;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoApiController extends Controller
{
    public function __construct(private PedidoService $pedidoService) {}

    public function index()
    {
        $pedidos = Pedido::with(['itens', 'pagamento', 'loja'])
            ->where('usuario_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json(['pedidos' => $pedidos]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'loja_id'       => 'required|exists:lojas,id',
            'itens'         => 'required|array|min:1',
            'tipo_entrega'  => 'required|in:entrega,retirada',
            'metodo_pagamento' => 'required|string',
        ]);

        try {
            $pedido = $this->pedidoService->criarPedido($request->all(), Auth::user());
            return response()->json(['sucesso' => true, 'pedido' => $pedido->load('itens')], 201);
        } catch (\Exception $e) {
            return response()->json(['erro' => $e->getMessage()], 422);
        }
    }

    public function show(Pedido $pedido)
    {
        if ($pedido->usuario_id !== Auth::id()) abort(403);

        $pedido->load(['itens', 'pagamento', 'entrega', 'loja']);

        return response()->json([
            'id'            => $pedido->id,
            'numero'        => $pedido->numero,
            'status'        => $pedido->status,
            'status_label'  => config("lanchonete.pedido.status.{$pedido->status}", $pedido->status),
            'status_cor'    => $pedido->status_cor,
            'total'         => $pedido->total,
            'aprovado'      => $pedido->pagamento?->status === 'aprovado',
            'link_rastreamento' => $pedido->link_rastreamento,
            'atualizado_em' => $pedido->updated_at,
            'itens'         => $pedido->itens,
            'loja'          => ['id' => $pedido->loja->id, 'nome' => $pedido->loja->nome],
        ]);
    }

    public function atualizarStatus(Request $request, Pedido $pedido)
    {
        // Apenas entregador pode atualizar via API
        if ($pedido->entrega?->funcionario?->usuario_id !== Auth::id()) abort(403);

        $request->validate(['status' => 'required|string']);

        try {
            $this->pedidoService->atualizarStatus($pedido, $request->status, $request->observacao);
            return response()->json(['sucesso' => true, 'status' => $pedido->fresh()->status]);
        } catch (\Exception $e) {
            return response()->json(['erro' => $e->getMessage()], 422);
        }
    }

    public function localizacaoEntregador(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $entrega = Entrega::whereHas('pedido', fn($q) => $q->where('usuario_id', Auth::id()))
            ->whereIn('status', ['aceito', 'coletado', 'em_rota'])
            ->latest()
            ->first();

        if (!$entrega) {
            return response()->json(['erro' => 'Nenhuma entrega ativa'], 404);
        }

        return response()->json([
            'latitude'      => $entrega->latitude_atual,
            'longitude'     => $entrega->longitude_atual,
            'status'        => $entrega->status,
            'atualizado_em' => $entrega->updated_at,
        ]);
    }
}
