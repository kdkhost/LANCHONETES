<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Entrega;
use App\Models\Loja;
use App\Models\BairroEntrega;

class EntregaService
{
    public function __construct(
        private CepService $cepService,
        private EvolutionApiService $evolutionService
    ) {}

    public function calcularTaxa(Loja $loja, array $dadosEndereco): array
    {
        if ($loja->tipo_taxa_entrega === 'gratis') {
            return ['taxa' => 0, 'tempo_min' => $loja->tempo_entrega_min, 'tempo_max' => $loja->tempo_entrega_max, 'disponivel' => true];
        }

        if ($loja->tipo_taxa_entrega === 'bairro') {
            return $this->calcularPorBairro($loja, $dadosEndereco);
        }

        if ($loja->tipo_taxa_entrega === 'por_km') {
            return $this->calcularPorKm($loja, $dadosEndereco);
        }

        return [
            'taxa'       => (float) $loja->taxa_entrega_fixa,
            'tempo_min'  => $loja->tempo_entrega_min,
            'tempo_max'  => $loja->tempo_entrega_max,
            'disponivel' => true,
        ];
    }

    private function calcularPorBairro(Loja $loja, array $dadosEndereco): array
    {
        $bairroNome = strtolower(trim($dadosEndereco['bairro'] ?? ''));
        $cidade     = strtolower(trim($dadosEndereco['cidade'] ?? ''));

        $bairro = BairroEntrega::where('loja_id', $loja->id)
            ->where('ativo', true)
            ->whereRaw('LOWER(nome) = ?', [$bairroNome])
            ->whereRaw('LOWER(cidade) = ?', [$cidade])
            ->first();

        if (!$bairro) {
            return ['taxa' => 0, 'tempo_min' => 0, 'tempo_max' => 0, 'disponivel' => false, 'erro' => 'Bairro não atendido'];
        }

        return [
            'taxa'       => (float) $bairro->taxa,
            'tempo_min'  => $bairro->tempo_estimado_min,
            'tempo_max'  => $bairro->tempo_estimado_max,
            'disponivel' => true,
        ];
    }

    private function calcularPorKm(Loja $loja, array $dadosEndereco): array
    {
        $latLoja = $loja->latitude;
        $lngLoja = $loja->longitude;
        $latDest = $dadosEndereco['latitude'] ?? null;
        $lngDest = $dadosEndereco['longitude'] ?? null;

        if (!$latLoja || !$lngLoja || !$latDest || !$lngDest) {
            return [
                'taxa'       => (float) $loja->taxa_entrega_fixa,
                'tempo_min'  => $loja->tempo_entrega_min,
                'tempo_max'  => $loja->tempo_entrega_max,
                'disponivel' => true,
            ];
        }

        $distancia = $this->cepService->calcularDistanciaKm($latLoja, $lngLoja, $latDest, $lngDest);

        if ($distancia > $loja->raio_entrega_km) {
            return ['taxa' => 0, 'tempo_min' => 0, 'tempo_max' => 0, 'disponivel' => false, 'erro' => 'Fora da área de entrega'];
        }

        $kmCobrado = max(0, $distancia - (float) $loja->km_gratis);
        $taxa      = $kmCobrado * (float) $loja->taxa_por_km;
        $tempoMin  = $loja->tempo_entrega_min + (int) ($distancia * 3);
        $tempoMax  = $loja->tempo_entrega_max + (int) ($distancia * 4);

        return [
            'taxa'        => round($taxa, 2),
            'tempo_min'   => $tempoMin,
            'tempo_max'   => $tempoMax,
            'disponivel'  => true,
            'distancia_km'=> $distancia,
        ];
    }

    public function criarEntrega(Pedido $pedido): Entrega
    {
        $entregador = $pedido->loja->funcionarios()
            ->where('e_entregador', true)
            ->where('ativo', true)
            ->where('disponivel_entregas', true)
            ->first();

        $entrega = Entrega::create([
            'pedido_id'         => $pedido->id,
            'entregador_id'     => $entregador?->id,
            'status'            => 'aguardando',
            'taxa_entrega'      => $pedido->taxa_entrega,
            'latitude_coleta'   => $pedido->loja->latitude,
            'longitude_coleta'  => $pedido->loja->longitude,
            'latitude_destino'  => $pedido->endereco_latitude,
            'longitude_destino' => $pedido->endereco_longitude,
        ]);

        $pedido->update(['link_rastreamento' => route('rastreamento.publico', $entrega->token_rastreamento)]);

        $this->evolutionService->enviarLinkRastreamento($pedido);

        return $entrega;
    }

    public function atualizarLocalizacaoEntregador(Entrega $entrega, float $lat, float $lng): void
    {
        $entrega->atualizarLocalizacao($lat, $lng);
        $entrega->entregador?->atualizarLocalizacao($lat, $lng);

        broadcast(new \App\Events\LocalizacaoEntregadorAtualizada($entrega, $lat, $lng))->toOthers();
    }
}
