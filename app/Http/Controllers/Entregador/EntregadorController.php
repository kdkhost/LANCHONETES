<?php

namespace App\Http\Controllers\Entregador;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Entrega;
use App\Models\Funcionario;
use App\Services\EntregaService;
use App\Services\EvolutionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EntregadorController extends Controller
{
    public function __construct(
        private EntregaService $entregaService,
        private EvolutionApiService $evolutionService
    ) {}

    public function dashboard()
    {
        $usuario     = Auth::user();
        $funcionario = $usuario->funcionario;

        if (!$funcionario || !$funcionario->e_entregador) {
            abort(403, 'Acesso restrito a entregadores.');
        }

        $entregasAtivas = Entrega::where('entregador_id', $funcionario->id)
            ->whereIn('status', ['aceito', 'coletado', 'em_rota'])
            ->with(['pedido.usuario', 'pedido.loja'])
            ->get();

        $entregasHoje = Entrega::where('entregador_id', $funcionario->id)
            ->whereDate('created_at', today())
            ->count();

        $faturamentoHoje = Entrega::where('entregador_id', $funcionario->id)
            ->whereDate('created_at', today())
            ->where('status', 'entregue')
            ->sum('taxa_entrega');

        $entregasPendentes = Entrega::where('status', 'aguardando')
            ->whereHas('pedido', fn($q) => $q->where('loja_id', $funcionario->loja_id))
            ->with(['pedido.usuario', 'pedido.loja'])
            ->get();

        return view('entregador.dashboard', compact(
            'funcionario', 'entregasAtivas', 'entregasHoje',
            'faturamentoHoje', 'entregasPendentes'
        ));
    }

    public function toggleDisponibilidade(Request $request)
    {
        $funcionario = Auth::user()->funcionario;
        if (!$funcionario) return response()->json(['erro' => 'Perfil não encontrado.'], 422);

        $funcionario->update(['disponivel_entregas' => !$funcionario->disponivel_entregas]);

        return response()->json([
            'sucesso'     => true,
            'disponivel'  => $funcionario->disponivel_entregas,
            'mensagem'    => $funcionario->disponivel_entregas ? 'Você está disponível para entregas.' : 'Você está offline.',
        ]);
    }

    public function aceitarEntrega(Entrega $entrega)
    {
        $funcionario = Auth::user()->funcionario;
        if ($entrega->status !== 'aguardando') {
            return response()->json(['erro' => 'Entrega não disponível.'], 422);
        }

        $entrega->update([
            'entregador_id' => $funcionario->id,
            'status'        => 'aceito',
            'aceito_em'     => now(),
        ]);

        return response()->json(['sucesso' => true, 'entrega' => $entrega->load('pedido.usuario')]);
    }

    public function coletarPedido(Entrega $entrega)
    {
        $funcionario = Auth::user()->funcionario;
        if ($entrega->entregador_id !== $funcionario->id) abort(403);

        $entrega->update(['status' => 'coletado', 'coletado_em' => now()]);
        $entrega->pedido->atualizarStatus('saiu_para_entrega');
        $this->evolutionService->notificarStatusPedido($entrega->pedido, 'saiu_para_entrega');
        $this->evolutionService->enviarLinkRastreamento($entrega->pedido);

        return response()->json(['sucesso' => true]);
    }

    public function atualizarLocalizacao(Request $request, Entrega $entrega)
    {
        $request->validate(['latitude' => 'required|numeric', 'longitude' => 'required|numeric']);
        $funcionario = Auth::user()->funcionario;
        if ($entrega->entregador_id !== $funcionario->id) abort(403);

        $this->entregaService->atualizarLocalizacaoEntregador(
            $entrega, $request->latitude, $request->longitude
        );

        return response()->json(['sucesso' => true]);
    }

    public function confirmarEntrega(Entrega $entrega)
    {
        $funcionario = Auth::user()->funcionario;
        if ($entrega->entregador_id !== $funcionario->id) abort(403);

        $entrega->update(['status' => 'entregue', 'entregue_em' => now()]);
        $entrega->pedido->atualizarStatus('entregue');
        $this->evolutionService->notificarStatusPedido($entrega->pedido, 'entregue');

        return response()->json(['sucesso' => true]);
    }

    public function historico()
    {
        $funcionario = Auth::user()->funcionario;
        $entregas    = Entrega::where('entregador_id', $funcionario->id)
            ->with(['pedido.usuario', 'pedido.loja'])
            ->latest()
            ->paginate(20);

        return view('entregador.historico', compact('entregas'));
    }

    public function mapa(Entrega $entrega)
    {
        $funcionario = Auth::user()->funcionario;
        if ($entrega->entregador_id !== $funcionario->id) abort(403);
        $entrega->load('pedido.loja');
        return view('entregador.mapa', compact('entrega'));
    }
}
