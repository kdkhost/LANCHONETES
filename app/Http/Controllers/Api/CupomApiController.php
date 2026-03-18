<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cupom;
use App\Models\Loja;
use Illuminate\Support\Facades\Auth;

class CupomApiController extends Controller
{
    public function verificar(string $lojaSlug, string $codigo)
    {
        $loja  = Loja::where('slug', $lojaSlug)->firstOrFail();
        $cupom = Cupom::where('loja_id', $loja->id)
            ->where('codigo', strtoupper($codigo))
            ->first();

        if (!$cupom || !$cupom->estaValido()) {
            return response()->json(['valido' => false, 'erro' => 'Cupom inválido ou expirado.'], 422);
        }

        return response()->json([
            'valido'      => true,
            'tipo'        => $cupom->tipo,
            'valor'       => $cupom->valor,
            'descricao'   => $cupom->descricao,
            'frete_gratis'=> $cupom->tipo === 'frete_gratis',
        ]);
    }
}
