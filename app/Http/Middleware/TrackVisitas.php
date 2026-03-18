<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VisitaLoja;
use App\Models\VisitaProduto;
use App\Models\VisitaCategoria;

class TrackVisitas
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Apenas rastrear GET requests de páginas públicas (não admin, não API)
        if ($request->method() !== 'GET' || $request->is('admin/*') || $request->is('api/*')) {
            return $response;
        }

        // Detectar tipo de dispositivo
        $userAgent = $request->userAgent();
        $deviceType = $this->detectDevice($userAgent);

        $dadosVisita = [
            'ip'          => $request->ip(),
            'user_agent'  => substr($userAgent, 0, 255),
            'referer'     => substr($request->header('referer', ''), 0, 500),
            'device_type' => $deviceType,
        ];

        $usuarioId = Auth::id();

        // Rastrear visita à loja (homepage)
        if ($request->routeIs('cliente.loja') || $request->routeIs('cliente.home')) {
            $lojaSlug = $request->route('lojaSlug');
            if ($lojaSlug) {
                $loja = \App\Models\Loja::where('slug', $lojaSlug)->first();
                if ($loja) {
                    $this->registrarVisitaLoja($loja->id, $usuarioId, $dadosVisita);
                }
            }
        }

        // Rastrear visita a produto
        if ($request->routeIs('cliente.produto')) {
            $produtoSlug = $request->route('slug');
            $lojaSlug = $request->route('lojaSlug');
            
            if ($produtoSlug && $lojaSlug) {
                $loja = \App\Models\Loja::where('slug', $lojaSlug)->first();
                $produto = \App\Models\Produto::where('slug', $produtoSlug)
                    ->where('loja_id', $loja?->id)
                    ->first();
                
                if ($produto && $loja) {
                    $this->registrarVisitaProduto($produto->id, $loja->id, $usuarioId, $dadosVisita);
                }
            }
        }

        return $response;
    }

    private function registrarVisitaLoja(int $lojaId, ?int $usuarioId, array $dados): void
    {
        // Evitar duplicatas na mesma sessão (cooldown de 5 minutos)
        $chaveCache = "visita_loja_{$lojaId}_{$dados['ip']}";
        if (cache()->has($chaveCache)) {
            return;
        }

        VisitaLoja::registrar($lojaId, $usuarioId, $dados);
        cache()->put($chaveCache, true, now()->addMinutes(5));
    }

    private function registrarVisitaProduto(int $produtoId, int $lojaId, ?int $usuarioId, array $dados): void
    {
        $chaveCache = "visita_produto_{$produtoId}_{$dados['ip']}";
        if (cache()->has($chaveCache)) {
            return;
        }

        VisitaProduto::registrar($produtoId, $lojaId, $usuarioId, $dados);
        cache()->put($chaveCache, true, now()->addMinutes(5));
    }

    private function registrarVisitaCategoria(int $categoriaId, int $lojaId, ?int $usuarioId, array $dados): void
    {
        $chaveCache = "visita_categoria_{$categoriaId}_{$dados['ip']}";
        if (cache()->has($chaveCache)) {
            return;
        }

        VisitaCategoria::registrar($categoriaId, $lojaId, $usuarioId, $dados);
        cache()->put($chaveCache, true, now()->addMinutes(5));
    }

    private function detectDevice(?string $userAgent): string
    {
        if (!$userAgent) return 'unknown';

        $userAgent = strtolower($userAgent);

        if (preg_match('/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i', $userAgent)) {
            return 'mobile';
        }

        if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            return 'tablet';
        }

        return 'desktop';
    }
}
