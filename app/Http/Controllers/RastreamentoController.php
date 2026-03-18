<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use Illuminate\Http\Request;

class RastreamentoController extends Controller
{
    public function publico(string $token)
    {
        $entrega = Entrega::where('token_rastreamento', $token)
            ->with(['pedido.loja', 'pedido.usuario', 'entregador.usuario'])
            ->firstOrFail();

        return view('rastreamento.publico', compact('entrega'));
    }

    public function status(string $token)
    {
        $entrega = Entrega::where('token_rastreamento', $token)
            ->with('pedido')
            ->firstOrFail();

        return response()->json([
            'status'         => $entrega->status,
            'pedido_status'  => $entrega->pedido->status,
            'latitude'       => $entrega->latitude_atual,
            'longitude'      => $entrega->longitude_atual,
            'destino_lat'    => $entrega->latitude_destino,
            'destino_lng'    => $entrega->longitude_destino,
            'atualizado_em'  => $entrega->localizacao_atualizada_em?->toISOString(),
            'entregador'     => $entrega->entregador?->usuario?->nome,
        ]);
    }
}
