<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IdentificarLoja;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\LojaAtiva;
use App\Http\Middleware\TrackVisitas;
use App\Http\Middleware\VerificarPlanoAtivo;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            IdentificarLoja::class,
            TrackVisitas::class,
        ]);

        $middleware->alias([
            'role'       => CheckRole::class,
            'loja.ativa' => LojaAtiva::class,
            'plano.ativo'=> VerificarPlanoAtivo::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhook/*',
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
