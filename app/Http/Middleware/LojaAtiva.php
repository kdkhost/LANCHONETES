<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LojaAtiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $loja = app('loja_atual');

        if (!$loja || !$loja->ativo) {
            return response()->view('errors.loja-inativa', [], 503);
        }

        return $next($request);
    }
}
