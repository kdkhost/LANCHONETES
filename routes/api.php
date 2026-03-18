<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CepApiController;
use App\Http\Controllers\Api\ProdutoApiController;
use App\Http\Controllers\Api\PedidoApiController;

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/cep/{cep}',                       [CepApiController::class, 'buscar'])->name('api.cep');
    Route::get('/produtos/{lojaSlug}',             [ProdutoApiController::class, 'porLoja'])->name('api.produtos');
    Route::get('/produtos/{id}/detalhe',           [ProdutoApiController::class, 'show'])->name('api.produto');
    Route::get('/cupom/{lojaSlug}/{codigo}',       [\App\Http\Controllers\Api\CupomApiController::class, 'verificar'])->name('api.cupom');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/usuario', fn(Request $r) => $r->user())->name('api.usuario');

    Route::prefix('pedidos')->name('api.pedidos.')->group(function () {
        Route::get('/',            [PedidoApiController::class, 'index'])->name('index');
        Route::post('/',           [PedidoApiController::class, 'store'])->name('store');
        Route::get('/{pedido}',    [PedidoApiController::class, 'show'])->name('show');
        Route::post('/{pedido}/status', [PedidoApiController::class, 'atualizarStatus'])->name('status');
    });

    Route::post('/entregador/localizacao/{entrega}', [\App\Http\Controllers\Entregador\EntregadorController::class, 'atualizarLocalizacao']);
});
