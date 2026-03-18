<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Loja;

class VerificarPlanoAtivo
{
    public function handle(Request $request, Closure $next, string $funcionalidade = null)
    {
        $loja = $this->getLojaAtual($request);
        
        if (!$loja) {
            return $next($request); // Sem loja, não verifica
        }

        // Se está em trial, permite tudo
        if ($loja->estaEmTrial()) {
            return $next($request);
        }

        // Verificações específicas por funcionalidade
        if ($funcionalidade) {
            return $this->verificarFuncionalidade($request, $next, $loja, $funcionalidade);
        }

        // Verificação geral de bloqueio
        if ($loja->estaBloqueadaPorPlano()) {
            return $this->respostaBloqueado($request, $loja);
        }

        return $next($request);
    }

    private function getLojaAtual(Request $request): ?Loja
    {
        // Tenta obter do container (admin)
        if (app()->bound('loja_atual')) {
            return app('loja_atual');
        }

        // Tenta obter do usuário autenticado
        if (Auth::check() && Auth::user()->loja) {
            return Auth::user()->loja;
        }

        // Tenta obter da URL (página pública)
        $slug = $request->segment(1);
        if ($slug && !in_array($slug, ['admin', 'api', 'login', 'register'])) {
            return Loja::where('slug', $slug)->first();
        }

        return null;
    }

    private function verificarFuncionalidade(Request $request, Closure $next, Loja $loja, string $funcionalidade)
    {
        $bloqueado = false;
        $mensagem = '';

        switch ($funcionalidade) {
            case 'produtos':
                $bloqueado = !$loja->podeCriarProdutos();
                $mensagem = 'Para cadastrar produtos, você precisa assinar um plano. Seu trial expirou.';
                break;

            case 'pagamento':
                $bloqueado = !$loja->podeConfigurarPagamento();
                $mensagem = 'Para configurar gateway de pagamento, você precisa assinar um plano. Seu trial expirou.';
                break;

            case 'vendas':
                $bloqueado = !$loja->podeVender();
                $mensagem = 'Para realizar vendas, você precisa assinar um plano. Seu trial expirou.';
                break;

            default:
                $bloqueado = $loja->estaBloqueadaPorPlano();
                $mensagem = 'Esta funcionalidade requer uma assinatura ativa. Seu trial expirou.';
                break;
        }

        if ($bloqueado) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => true,
                    'message' => $mensagem,
                    'bloqueado' => true,
                    'dias_trial' => $loja->dias_restantes_trial,
                    'status_plano' => $loja->status_plano,
                ], 403);
            }

            // Redirecionar para página de upgrade
            return redirect()->route('admin.planos.upgrade')
                ->with('alerta', $mensagem);
        }

        return $next($request);
    }

    private function respostaBloqueado(Request $request, Loja $loja)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'error' => true,
                'message' => 'Sua assinatura expirou. Renove seu plano para continuar usando o sistema.',
                'bloqueado' => true,
                'dias_trial' => $loja->dias_restantes_trial,
                'status_plano' => $loja->status_plano,
            ], 403);
        }

        // Se é página pública da loja, mostrar aviso
        if ($request->routeIs('cliente.*')) {
            session()->flash('plano_expirado', true);
            return $next($request);
        }

        // Se é admin, redirecionar para upgrade
        return redirect()->route('admin.planos.upgrade')
            ->with('alerta', 'Sua assinatura expirou. Renove seu plano para continuar.');
    }
}
