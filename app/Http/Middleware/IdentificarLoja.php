<?php

namespace App\Http\Middleware;

use App\Models\Loja;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class IdentificarLoja
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('loja') ?? $request->segment(1);
        $loja = null;

        if ($slug && $slug !== 'admin' && $slug !== 'api') {
            $loja = Loja::where('slug', $slug)->where('ativo', true)->first();
        }

        if (!$loja) {
            $loja = Loja::where('ativo', true)->first();
        }

        if ($loja) {
            app()->instance('loja_atual', $loja);
            View::share('lojaAtual', $loja);
            config(['lanchonete.pwa.cor_tema' => $loja->cor_primaria]);
        }

        return $next($request);
    }
}
