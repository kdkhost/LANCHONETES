<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return $request->expectsJson()
                ? response()->json(['erro' => 'Não autenticado'], 401)
                : redirect()->route('login');
        }

        if (!$request->user()->hasRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json(['erro' => 'Acesso negado'], 403);
            }
            abort(403, 'Você não tem permissão para acessar esta página.');
        }

        return $next($request);
    }
}
