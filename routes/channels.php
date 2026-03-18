<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('pedido.{pedidoId}', function ($user, $pedidoId) {
    $pedido = \App\Models\Pedido::find($pedidoId);
    return $pedido && ($user->id === $pedido->usuario_id || $user->isAdmin());
});

Broadcast::channel('loja.{lojaId}', function ($user, $lojaId) {
    return $user->loja_id == $lojaId || $user->isAdmin();
});

Broadcast::channel('rastreamento.{token}', function ($user, $token) {
    return true;
});
