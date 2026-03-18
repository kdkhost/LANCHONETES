<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->role;

        // Obter tours disponíveis para o role do usuário
        $toursDisponiveis = Tour::ativos()
            ->paraRole($role)
            ->ordenados()
            ->get();

        // Obter progresso dos tours do usuário
        $toursUsuario = [];
        foreach ($toursDisponiveis as $tour) {
            $toursUsuario[$tour->id] = $tour->getProgressoUsuario($user->id);
        }

        // Verificar se há tours pendentes para mostrar
        $tourPendente = null;
        foreach ($toursUsuario as $tourId => $progresso) {
            if ($progresso['status'] === 'pendente') {
                $tourPendente = Tour::find($tourId);
                break;
            }
        }

        return response()->json([
            'tours_disponiveis' => $toursDisponiveis,
            'progresso_usuario' => $toursUsuario,
            'tour_pendente' => $tourPendente,
            'tour_atual' => $this->getTourAtual($user),
        ]);
    }

    public function iniciar(Request $request, Tour $tour)
    {
        $user = Auth::user();

        // Verificar se o usuário pode iniciar este tour
        if (!$tour->podeIniciar($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Este tour não pode ser iniciado.'
            ], 403);
        }

        // Iniciar tour para o usuário
        $tourUsuario = $tour->iniciarParaUsuario($user->id);

        Log::info('Tour iniciado', [
            'user_id' => $user->id,
            'tour_id' => $tour->id,
            'tour_nome' => $tour->nome,
        ]);

        return response()->json([
            'success' => true,
            'tour_usuario' => $tourUsuario,
            'passo_atual' => $tour->getPrimeiroPasso(),
            'total_passos' => $tour->total_passos,
        ]);
    }

    public function avancar(Request $request, Tour $tour)
    {
        $user = Auth::user();

        // Verificar se o tour pode avançar
        $tourUsuario = $tour->usuarios()->where('user_id', $user->id)->first();
        if (!$tourUsuario || !$tourUsuario->podeAvancar()) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível avançar neste tour.'
            ], 403);
        }

        // Avançar passo
        $sucesso = $tour->avancarPasso($user->id);

        if (!$sucesso) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao avançar passo.'
            ], 500);
        }

        // Recarregar dados do tour
        $tourUsuario->refresh();
        $passoAtual = $tourUsuario->passo_atual;
        $proximoPasso = $tourUsuario->proximo_passo;

        // Se concluiu, registrar log
        if ($tourUsuario->status === 'concluido') {
            Log::info('Tour concluído', [
                'user_id' => $user->id,
                'tour_id' => $tour->id,
                'tour_nome' => $tour->nome,
                'duracao' => $tourUsuario->duracao,
            ]);
        }

        return response()->json([
            'success' => true,
            'concluido' => $tourUsuario->status === 'concluido',
            'passo_atual' => $passoAtual,
            'proximo_passo' => $proximoPasso,
            'percentual' => $tourUsuario->percentual_conclusao,
        ]);
    }

    public function voltar(Request $request, Tour $tour)
    {
        $user = Auth::user();

        // Verificar se o tour pode voltar
        $tourUsuario = $tour->usuarios()->where('user_id', $user->id)->first();
        if (!$tourUsuario || !$tourUsuario->podeVoltar()) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível voltar neste tour.'
            ], 403);
        }

        // Voltar passo
        $sucesso = $tourUsuario->voltar();

        if (!$sucesso) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao voltar passo.'
            ], 500);
        }

        $tourUsuario->refresh();

        return response()->json([
            'success' => true,
            'passo_atual' => $tourUsuario->passo_atual,
            'percentual' => $tourUsuario->percentual_conclusao,
        ]);
    }

    public function pular(Request $request, Tour $tour)
    {
        $user = Auth::user();

        // Verificar se o tour pode ser pulado
        $tourUsuario = $tour->usuarios()->where('user_id', $user->id)->first();
        if (!$tourUsuario) {
            return response()->json([
                'success' => false,
                'message' => 'Tour não encontrado.'
            ], 404);
        }

        // Pular tour
        $sucesso = $tour->pular($user->id);

        if (!$sucesso) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao pular tour.'
            ], 500);
        }

        Log::info('Tour pulado', [
            'user_id' => $user->id,
            'tour_id' => $tour->id,
            'tour_nome' => $tour->nome,
            'passo_atual' => $tourUsuario->passo_atual,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tour pulado com sucesso.'
        ]);
    }

    public function reiniciar(Request $request, Tour $tour)
    {
        $user = Auth::user();

        // Reiniciar tour para o usuário
        $tourUsuario = $tour->reiniciarParaUsuario($user->id);

        Log::info('Tour reiniciado', [
            'user_id' => $user->id,
            'tour_id' => $tour->id,
            'tour_nome' => $tour->nome,
        ]);

        return response()->json([
            'success' => true,
            'tour_usuario' => $tourUsuario,
            'message' => 'Tour reiniciado com sucesso.'
        ]);
    }

    public function progresso(Request $request)
    {
        $user = Auth::user();
        $role = $user->role;

        // Obter todos os tours e progresso
        $tours = Tour::ativos()
            ->paraRole($role)
            ->ordenados()
            ->get()
            ->map(function ($tour) use ($user) {
                $progresso = $tour->getProgressoUsuario($user->id);
                return [
                    'id' => $tour->id,
                    'nome' => $tour->nome,
                    'titulo' => $tour->titulo,
                    'descricao' => $tour->descricao,
                    'status' => $progresso['status'],
                    'percentual' => $progresso['percentual'],
                    'passo_atual' => $progresso['passo_atual'],
                    'total_passos' => $tour->total_passos,
                    'iniciado_em' => $progresso['iniciado_em'],
                    'concluido_em' => $progresso['concluido_em'],
                ];
            });

        // Estatísticas gerais
        $totalTours = $tours->count();
        $concluidos = $tours->where('status', 'concluido')->count();
        $emAndamento = $tours->where('status', 'em_andamento')->count();
        $pendentes = $tours->where('status', 'pendente')->count();

        return response()->json([
            'tours' => $tours,
            'estatisticas' => [
                'total' => $totalTours,
                'concluidos' => $concluidos,
                'em_andamento' => $emAndamento,
                'pendentes' => $pendentes,
                'percentual_geral' => $totalTours > 0 ? round(($concluidos / $totalTours) * 100, 2) : 0,
            ]
        ]);
    }

    public function listar(Request $request)
    {
        // Para admin super - ver todos os tours e progresso dos usuários
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $tours = Tour::with(['usuarios.user', 'usuarios.tour'])
            ->ordenados()
            ->get();

        return response()->json($tours);
    }

    public function criar(Request $request)
    {
        // Para admin super - criar novo tour
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'nome' => 'required|string|max:100|unique:tours',
            'titulo' => 'required|string|max:200',
            'descricao' => 'nullable|string',
            'passos' => 'required|array|min:1',
            'passos.*.id' => 'required|string',
            'passos.*.element' => 'required|string',
            'passos.*.title' => 'required|string|max:200',
            'passos.*.text' => 'required|string',
            'passos.*.buttons' => 'required|array',
            'target_role' => 'nullable|string|in:admin,super_admin,gerente,atendente,cozinheiro,entregador',
            'ordem' => 'integer|min:0',
            'ativo' => 'boolean',
        ]);

        $tour = Tour::create($request->all());

        return response()->json([
            'success' => true,
            'tour' => $tour,
            'message' => 'Tour criado com sucesso.'
        ]);
    }

    public function atualizar(Request $request, Tour $tour)
    {
        // Para admin super - atualizar tour
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'nome' => 'required|string|max:100|unique:tours,nome,' . $tour->id,
            'titulo' => 'required|string|max:200',
            'descricao' => 'nullable|string',
            'passos' => 'required|array|min:1',
            'target_role' => 'nullable|string|in:admin,super_admin,gerente,atendente,cozinheiro,entregador',
            'ordem' => 'integer|min:0',
            'ativo' => 'boolean',
        ]);

        $tour->update($request->all());

        return response()->json([
            'success' => true,
            'tour' => $tour,
            'message' => 'Tour atualizado com sucesso.'
        ]);
    }

    public function excluir(Tour $tour)
    {
        // Para admin super - excluir tour
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        // Verificar se há usuários com este tour
        if ($tour->usuarios()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir um tour que já foi iniciado por usuários.'
            ], 422);
        }

        $tour->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tour excluído com sucesso.'
        ]);
    }

    // Métodos auxiliares
    private function getTourAtual($user): ?array
    {
        $role = $user->role;
        
        // Buscar tour em andamento
        $tourEmAndamento = Tour::ativos()
            ->paraRole($role)
            ->whereHas('usuarios', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', 'em_andamento');
            })
            ->with(['usuarios' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->first();

        if ($tourEmAndamento) {
            $tourUsuario = $tourEmAndamento->usuarios->first();
            return [
                'tour' => $tourEmAndamento,
                'tour_usuario' => $tourUsuario,
                'passo_atual' => $tourUsuario->passo_atual,
                'proximo_passo' => $tourUsuario->proximo_passo,
                'pode_avancar' => $tourUsuario->podeAvancar(),
                'pode_voltar' => $tourUsuario->podeVoltar(),
                'esta_no_ultimo' => $tourUsuario->estaNoUltimoPasso(),
            ];
        }

        return null;
    }
}
