<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CepService;

class CepApiController extends Controller
{
    public function __construct(private CepService $cepService) {}

    public function buscar(string $cep)
    {
        $resultado = $this->cepService->buscar($cep);
        if (!$resultado['sucesso']) {
            return response()->json(['erro' => $resultado['erro']], 422);
        }
        return response()->json($resultado);
    }
}
