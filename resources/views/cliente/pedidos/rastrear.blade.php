@extends('layouts.pwa')
@section('titulo', 'Rastrear Pedido')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
#mapaRastreamento { height: 300px; width: 100%; border-radius: 0; }
</style>
@endpush

@section('conteudo')
<div class="page-header">
    <button onclick="history.back()" class="btn-voltar"><i class="bi bi-arrow-left"></i></button>
    <h1 class="page-title">Rastreamento</h1>
</div>

@php
    $entrega   = $pedido->entrega;
    $cores     = config('lanchonete.pedido.cores_status');
    $cor       = $cores[$pedido->status] ?? '#6c757d';
    $labels    = config('lanchonete.pedido.status');
@endphp

{{-- Status do pedido --}}
<div class="status-timeline px-3 pb-2">
    <div class="status-atual-badge" style="background:{{ $cor }}20;color:{{ $cor }};border:1.5px solid {{ $cor }}">
        <span class="pulse-dot-sm" style="background:{{ $cor }}"></span>
        {{ $labels[$pedido->status] ?? $pedido->status }}
    </div>
</div>

{{-- Mapa (se saiu para entrega) --}}
@if($pedido->status === 'saiu_para_entrega' && $entrega)
<div id="mapaRastreamento"></div>
<div class="rastreio-info-bar">
    <div class="rastreio-entregador">
        <img src="{{ $entrega->funcionario?->usuario->foto_perfil_url ?? asset('img/avatar-default.png') }}"
            class="rastreio-avatar">
        <div>
            <div class="rastreio-nome">{{ $entrega->funcionario?->usuario->nome ?? 'Entregador' }}</div>
            <div class="rastreio-veiculo text-muted">
                <small>{{ $entrega->funcionario?->veiculo ?? 'Veículo' }}
                @if($entrega->funcionario?->placa_veiculo) · {{ $entrega->funcionario->placa_veiculo }} @endif</small>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Timeline --}}
<div class="rastreio-timeline">
    @php
        $etapas = [
            'aguardando_pagamento' => ['icon' => 'clock',           'label' => 'Pedido realizado'],
            'pagamento_aprovado'   => ['icon' => 'check-circle',    'label' => 'Pagamento aprovado'],
            'em_preparo'           => ['icon' => 'fire',            'label' => 'Em preparo'],
            'pronto'               => ['icon' => 'bag-check',       'label' => 'Pedido pronto'],
            'saiu_para_entrega'    => ['icon' => 'bicycle',         'label' => 'Saiu para entrega'],
            'entregue'             => ['icon' => 'house-check',     'label' => 'Entregue!'],
        ];
        $statusOrdem = array_keys($etapas);
        $idxAtual = array_search($pedido->status, $statusOrdem);
    @endphp

    @foreach($etapas as $etapaStatus => $etapa)
    @php
        $idxEtapa = array_search($etapaStatus, $statusOrdem);
        $completo = $idxEtapa <= $idxAtual;
        $atual    = $etapaStatus === $pedido->status;
    @endphp
    <div class="rastreio-etapa {{ $completo ? 'completo' : '' }} {{ $atual ? 'atual' : '' }}">
        <div class="rastreio-etapa-icone">
            <i class="bi bi-{{ $completo ? 'check-circle-fill' : $etapa['icon'] }}"></i>
        </div>
        <div class="rastreio-etapa-label">{{ $etapa['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- Informações do pedido --}}
<div class="rastreio-card">
    <div class="resumo-linha"><span>Pedido</span><strong>#{{ $pedido->numero }}</strong></div>
    <div class="resumo-linha"><span>Itens</span><strong>{{ $pedido->itens->count() }} item(ns)</strong></div>
    @if($pedido->tipo_entrega === 'entrega')
    <div class="resumo-linha"><span>Endereço</span>
        <span class="text-right" style="max-width:60%;text-align:right">
            {{ $pedido->endereco_logradouro }}, {{ $pedido->endereco_numero }}, {{ $pedido->endereco_bairro }}
        </span>
    </div>
    @endif
    <div class="resumo-linha"><span>Total</span><strong>R$ {{ number_format($pedido->total, 2, ',', '.') }}</strong></div>
</div>

<style>
.rastreio-info-bar { display:flex;align-items:center;padding:12px 16px;background:var(--cor-card);border-top:1px solid var(--cor-borda);margin-bottom:4px; }
.rastreio-entregador { display:flex;align-items:center;gap:10px; }
.rastreio-avatar { width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid var(--cor-primaria); }
.rastreio-nome { font-weight:700;font-size:.9rem; }
.rastreio-timeline { padding:16px 20px; background:var(--cor-card); margin:8px 0; }
.rastreio-etapa { display:flex;align-items:center;gap:12px;padding:8px 0;position:relative; }
.rastreio-etapa::before { content:'';position:absolute;left:14px;top:32px;bottom:-8px;width:2px;background:var(--cor-borda); }
.rastreio-etapa:last-child::before { display:none; }
.rastreio-etapa.completo::before { background:var(--cor-sucesso); }
.rastreio-etapa-icone { width:30px;height:30px;border-radius:50%;background:var(--cor-borda);display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;transition:all .3s; }
.rastreio-etapa.completo .rastreio-etapa-icone { background:#d4edda;color:var(--cor-sucesso); }
.rastreio-etapa.atual .rastreio-etapa-icone { background:var(--cor-primaria);color:#fff;box-shadow:0 0 0 4px rgba(255,107,53,.2);animation:pulse .8s infinite; }
.rastreio-etapa-label { font-size:.88rem;font-weight:600; }
.rastreio-etapa.completo .rastreio-etapa-label { color:var(--cor-sucesso); }
.rastreio-etapa.atual .rastreio-etapa-label { color:var(--cor-primaria);font-weight:800; }
.rastreio-card { background:var(--cor-card);margin:8px 12px;border-radius:14px;padding:14px;box-shadow:var(--sombra-sm); }
.resumo-linha { display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--cor-borda);font-size:.88rem; }
.resumo-linha:last-child { border:none; }
.resumo-linha span:first-child { color:var(--cor-texto-muted); }
</style>
@endsection

@push('scripts')
@if($pedido->status === 'saiu_para_entrega' && $entrega)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const destLat = {{ $pedido->endereco_latitude ?? $pedido->loja->latitude ?? -23.55 }};
const destLng = {{ $pedido->endereco_longitude ?? $pedido->loja->longitude ?? -46.63 }};
const entLat  = {{ $entrega->latitude_atual ?? 'null' }};
const entLng  = {{ $entrega->longitude_atual ?? 'null' }};

const mapa = L.map('mapaRastreamento').setView(
    entLat && entLng ? [entLat, entLng] : [destLat, destLng], 14
);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution:'© OpenStreetMap contributors', maxZoom:19
}).addTo(mapa);

const iconeDest = L.divIcon({
    html:'<div style="background:#dc3545;width:28px;height:28px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3)"></div>',
    iconAnchor:[14,14], className:''
});
const iconeEnt = L.divIcon({
    html:'<div style="font-size:24px;line-height:1">🛵</div>',
    iconAnchor:[12,12], className:''
});

L.marker([destLat, destLng], { icon: iconeDest }).addTo(mapa)
    .bindPopup('Seu endereço de entrega');

let marcadorEnt = null;
if (entLat && entLng) {
    marcadorEnt = L.marker([entLat, entLng], { icon: iconeEnt }).addTo(mapa);
}

// Polling a cada 10s para atualizar posição
setInterval(async () => {
    try {
        const r = await fetch('/pedidos/{{ $pedido->id }}/localizacao', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!r.ok) return;
        const d = await r.json();
        if (d.latitude && d.longitude) {
            const pos = [d.latitude, d.longitude];
            if (marcadorEnt) marcadorEnt.setLatLng(pos);
            else marcadorEnt = L.marker(pos, { icon: iconeEnt }).addTo(mapa);
        }
        if (d.status === 'entregue') setTimeout(() => location.reload(), 1000);
    } catch {}
}, 10000);
</script>
@endif
@endpush
