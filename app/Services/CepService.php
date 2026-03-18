<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class CepService
{
    public function buscar(string $cep): array
    {
        $cep = preg_replace('/\D/', '', $cep);

        if (strlen($cep) !== 8) {
            return ['sucesso' => false, 'erro' => 'CEP inválido'];
        }

        return Cache::remember("cep_{$cep}", 86400, function () use ($cep) {
            try {
                $resposta = Http::timeout(5)
                    ->get("https://viacep.com.br/ws/{$cep}/json/");

                if ($resposta->failed() || isset($resposta->json()['erro'])) {
                    return ['sucesso' => false, 'erro' => 'CEP não encontrado'];
                }

                $dados = $resposta->json();

                return [
                    'sucesso'     => true,
                    'cep'         => $dados['cep'],
                    'logradouro'  => $dados['logradouro'] ?? '',
                    'complemento' => $dados['complemento'] ?? '',
                    'bairro'      => $dados['bairro'] ?? '',
                    'cidade'      => $dados['localidade'] ?? '',
                    'estado'      => $dados['uf'] ?? '',
                    'ibge'        => $dados['ibge'] ?? '',
                ];
            } catch (Exception $e) {
                return ['sucesso' => false, 'erro' => 'Erro ao consultar CEP'];
            }
        });
    }

    public function calcularDistanciaKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $raioTerra = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($raioTerra * $c, 2);
    }
}
