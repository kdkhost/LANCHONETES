<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Funcionario;
use App\Services\PedidoService;
use App\Services\EvolutionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoAdminController extends Controller
{
    public function __construct(
        private PedidoService $pedidoService,
        private EvolutionApiService $evolutionService
    ) {}

    public function index(Request $request)
    {
        $usuario = Auth::user();
        $query   = Pedido::with(['usuario', 'itens', 'pagamento', 'entrega'])
            ->when($usuario->loja_id, fn($q) => $q->where('loja_id', $usuario->loja_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->busca, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('numero', 'like', "%{$request->busca}%")
                   ->orWhereHas('usuario', fn($u) => $u->where('nome', 'like', "%{$request->busca}%"));
            }))
            ->when($request->data, fn($q) => $q->whereDate('created_at', $request->data))
            ->latest();

        $pedidos    = $query->paginate(20)->withQueryString();
        $statusOpts = config('lanchonete.pedido.status');

        return view('admin.pedidos.index', compact('pedidos', 'statusOpts'));
    }

    public function show(Pedido $pedido)
    {
        $pedido->load(['usuario', 'itens.adicionais', 'pagamento', 'entrega.entregador.usuario', 'loja']);
        $entregadores = Funcionario::where('loja_id', $pedido->loja_id)
            ->where('e_entregador', true)
            ->where('ativo', true)
            ->with('usuario')
            ->get();

        return view('admin.pedidos.show', compact('pedido', 'entregadores'));
    }

    public function atualizarStatus(Request $request, Pedido $pedido)
    {
        $request->validate([
            'status'      => 'required|string',
            'observacao'  => 'nullable|string|max:500',
        ]);

        $statusValidos = array_keys(config('lanchonete.pedido.status'));
        if (!in_array($request->status, $statusValidos)) {
            return response()->json(['erro' => 'Status inválido'], 422);
        }

        $this->pedidoService->atualizarStatus($pedido, $request->status, $request->observacao);

        return response()->json([
            'sucesso'      => true,
            'mensagem'     => 'Status atualizado com sucesso.',
            'novo_status'  => $pedido->fresh()->status_label,
        ]);
    }

    public function atribuirEntregador(Request $request, Pedido $pedido)
    {
        $request->validate(['entregador_id' => 'required|exists:funcionarios,id']);

        $pedido->update(['entregador_id' => $request->entregador_id]);

        if ($pedido->entrega) {
            $pedido->entrega->update(['entregador_id' => $request->entregador_id]);
        }

        return response()->json(['sucesso' => true, 'mensagem' => 'Entregador atribuído.']);
    }

    public function enviarMensagem(Request $request, Pedido $pedido)
    {
        $request->validate(['mensagem' => 'required|string|max:1000']);

        $usuario = $pedido->usuario;
        if (!$usuario->whatsapp) {
            return response()->json(['erro' => 'Cliente sem WhatsApp cadastrado.'], 422);
        }

        $enviado = $this->evolutionService->enviarMensagemManual($usuario->whatsapp, $request->mensagem);

        return response()->json([
            'sucesso'  => $enviado,
            'mensagem' => $enviado ? 'Mensagem enviada!' : 'Falha ao enviar mensagem.',
        ]);
    }

    public function kanban()
    {
        $usuario = Auth::user();
        $lojaId  = $usuario->loja_id;

        $colunas = [];
        $statusVisiveis = ['confirmado', 'em_preparo', 'pronto', 'saiu_para_entrega'];

        foreach ($statusVisiveis as $status) {
            $colunas[$status] = Pedido::with(['usuario', 'itens'])
                ->when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
                ->where('status', $status)
                ->orderBy('created_at')
                ->get();
        }

        $statusOpts = config('lanchonete.pedido.status');

        return view('admin.pedidos.kanban', compact('colunas', 'statusOpts'));
    }

    public function ultimos(Request $request)
    {
        $lojaId  = Auth::user()->loja_id;
        $pendentes = Pedido::where('loja_id', $lojaId)
            ->where('status', 'aguardando_pagamento')
            ->count();

        $ultimo = Pedido::where('loja_id', $lojaId)
            ->latest()
            ->first();

        return response()->json([
            'pendentes'     => $pendentes,
            'ultimo_id'     => $ultimo?->id,
            'ultimo_numero' => $ultimo?->numero,
        ]);
    }
}
