@extends('layouts.pwa')
@section('titulo', 'Mapa de Entrega')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
#mapaEntregador { height: calc(100vh - 180px); width: 100%; }
.mapa-info-bar {
    position: fixed; bottom: 72px; left: 0; right: 0;
    background: var(--cor-card); border-top: 1px solid var(--cor-borda);
    padding: 12px 16px; z-index: 400;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    box-shadow: 0 -4px 16px rgba(0,0,0,.08);
}
.mapa-info-destino { font-size: 0.88rem; font-weight: 700; flex: 1; }
.mapa-info-sub { font-size: 0.75rem; color: var(--cor-texto-muted); margin-top: 2px; }
.btn-navegar { padding: 10px 18px; background: var(--cor-primaria); color: #fff; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; font-size: 0.88rem; display: flex; align-items: center; gap: 6px; text-decoration: none; }
</style>
@endpush

@section('conteudo')
<div class="page-header">
    <button onclick="history.back()" class="btn-voltar"><i class="bi bi-arrow-left"></i></button>
    <h1 class="page-title">Entrega #{{ $entrega->pedido->numero }}</h1>
</div>

<div id="mapaEntregador"></div>

<div class="mapa-info-bar">
    <div>
        <div class="mapa-info-destino">
            <i class="bi bi-geo-alt-fill text-danger"></i>
            {{ $entrega->pedido->endereco_logradouro }}, {{ $entrega->pedido->endereco_numero }}
        </div>
        <div class="mapa-info-sub">
            {{ $entrega->pedido->endereco_bairro }}, {{ $entrega->pedido->endereco_cidade }}
        </div>
    </div>
    <a href="https://maps.google.com/maps?daddr={{ urlencode($entrega->pedido->endereco_logradouro . ', ' . $entrega->pedido->endereco_numero . ', ' . $entrega->pedido->endereco_bairro . ', ' . $entrega->pedido->endereco_cidade) }}"
       target="_blank" class="btn-navegar">
        <i class="bi bi-map"></i> Navegar
    </a>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const destinoLat = {{ $entrega->pedido->endereco_latitude ?? $entrega->pedido->loja->latitude ?? -23.5505 }};
const destinoLng = {{ $entrega->pedido->endereco_longitude ?? $entrega->pedido->loja->longitude ?? -46.6333 }};

const mapa = L.map('mapaEntregador').setView([destinoLat, destinoLng], 14);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 19
}).addTo(mapa);

const iconeDestino = L.divIcon({
    html: '<div style="background:#dc3545;width:36px;height:36px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3)"></div>',
    iconAnchor: [18, 36], popupAnchor: [0, -36], className: ''
});
const iconeEntregador = L.divIcon({
    html: '<div style="background:#FF6B35;width:32px;height:32px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;font-size:16px">🛵</div>',
    iconAnchor: [16, 16], className: ''
});

L.marker([destinoLat, destinoLng], { icon: iconeDestino })
    .addTo(mapa)
    .bindPopup('<strong>Destino da Entrega</strong><br>{{ $entrega->pedido->endereco_logradouro }}, {{ $entrega->pedido->endereco_numero }}')
    .openPopup();

let marcadorEntregador = null;

function atualizarPosicaoEntregador(lat, lng) {
    if (marcadorEntregador) {
        marcadorEntregador.setLatLng([lat, lng]);
    } else {
        marcadorEntregador = L.marker([lat, lng], { icon: iconeEntregador })
            .addTo(mapa)
            .bindPopup('Sua posição atual');
    }
}

// Rastrear posição do entregador
if (navigator.geolocation) {
    navigator.geolocation.watchPosition(pos => {
        const { latitude, longitude } = pos.coords;
        atualizarPosicaoEntregador(latitude, longitude);

        // Enviar ao servidor
        fetch(`/entregador/entregas/{{ $entrega->id }}/localizacao`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ latitude, longitude })
        }).catch(() => {});
    }, null, { enableHighAccuracy: true, maximumAge: 5000, timeout: 10000 });
}
</script>
@endpush
