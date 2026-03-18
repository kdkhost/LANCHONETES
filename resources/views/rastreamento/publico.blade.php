<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#FF6B35">
    <title>Rastreamento — {{ $entrega->pedido->numero }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Nunito', sans-serif; background: #f8f9fa; }
        .rast-header { background: #FF6B35; color: #fff; padding: 16px; text-align: center; }
        .rast-header h1 { font-size: 1.1rem; font-weight: 700; }
        .rast-header p  { font-size: 0.85rem; opacity: .9; }
        #mapa { width: 100%; height: 55vh; z-index: 1; }
        .rast-info { padding: 16px; }
        .status-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 20px; font-weight: 700; font-size: 0.95rem;
            margin-bottom: 12px;
        }
        .rast-linha { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        .rast-linha:last-child { border: none; }
        .rast-entregador { display: flex; align-items: center; gap: 12px; background: #fff; border-radius: 12px; padding: 12px; margin-top: 12px; box-shadow: 0 2px 8px #0001; }
        .rast-entregador i { font-size: 2rem; color: #FF6B35; }
        .rast-entregador-info small { color: #888; }
        .pulse-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #28a745; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%,100%{ transform: scale(1); opacity:1; } 50%{ transform: scale(1.4); opacity:.6; } }
        .leaflet-marker-icon { border-radius: 50%; }
        .atualizado-em { font-size: 0.75rem; color: #aaa; text-align: center; margin-top: 8px; }
    </style>
</head>
<body>
<div class="rast-header">
    <h1><i class="bi bi-geo-alt-fill"></i> Acompanhe sua entrega</h1>
    <p>Pedido {{ $entrega->pedido->numero }} — {{ $entrega->pedido->loja->nome }}</p>
</div>

<div id="mapa"></div>

<div class="rast-info">
    @php
        $cores = config('lanchonete.pedido.cores_status');
        $labels = config('lanchonete.pedido.status');
        $statusCor = $cores[$entrega->pedido->status] ?? '#6c757d';
        $statusLabel = $labels[$entrega->pedido->status] ?? $entrega->pedido->status;
    @endphp
    <span class="status-badge" style="background:{{ $statusCor }}20; color:{{ $statusCor }}">
        <span class="pulse-dot" style="background:{{ $statusCor }}"></span>
        {{ $statusLabel }}
    </span>

    <div class="rast-linha">
        <span><i class="bi bi-shop text-muted"></i> Loja</span>
        <strong>{{ $entrega->pedido->loja->nome }}</strong>
    </div>
    <div class="rast-linha">
        <span><i class="bi bi-geo-alt text-muted"></i> Destino</span>
        <strong>{{ $entrega->pedido->endereco_bairro }}, {{ $entrega->pedido->endereco_cidade }}</strong>
    </div>
    @if($entrega->tempo_estimado_min)
    <div class="rast-linha">
        <span><i class="bi bi-clock text-muted"></i> Tempo estimado</span>
        <strong>≈ {{ $entrega->tempo_estimado_min }} min</strong>
    </div>
    @endif
    @if($entrega->distancia_km)
    <div class="rast-linha">
        <span><i class="bi bi-signpost text-muted"></i> Distância</span>
        <strong>{{ number_format($entrega->distancia_km, 1) }} km</strong>
    </div>
    @endif

    @if($entrega->entregador)
    <div class="rast-entregador">
        <i class="bi bi-bicycle"></i>
        <div class="rast-entregador-info">
            <strong>{{ $entrega->entregador->usuario->nome }}</strong><br>
            <small>Entregador</small>
            @if($entrega->entregador->veiculo)
            <small> • {{ $entrega->entregador->veiculo }} {{ $entrega->entregador->placa_veiculo }}</small>
            @endif
        </div>
    </div>
    @endif

    <div class="atualizado-em" id="atualizadoEm">Atualizando...</div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const TOKEN_RAST  = '{{ $entrega->token_rastreamento }}';
const STATUS_API  = `/rastreamento/${TOKEN_RAST}/status`;
const LAT_DESTINO = {{ $entrega->latitude_destino ?? 'null' }};
const LNG_DESTINO = {{ $entrega->longitude_destino ?? 'null' }};
const LAT_COLETA  = {{ $entrega->latitude_coleta ?? 'null' }};
const LNG_COLETA  = {{ $entrega->longitude_coleta ?? 'null' }};
const LAT_ATUAL   = {{ $entrega->latitude_atual ?? ($entrega->latitude_coleta ?? 'null') }};
const LNG_ATUAL   = {{ $entrega->longitude_atual ?? ($entrega->longitude_coleta ?? 'null') }};

const mapaCentro  = LAT_ATUAL ? [LAT_ATUAL, LNG_ATUAL] : [-15.77972, -47.92972];
const mapa = L.map('mapa', { zoomControl: true }).setView(mapaCentro, 14);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 19
}).addTo(mapa);

const iconEntregador = L.divIcon({
    html: '<div style="background:#FF6B35;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid #fff;box-shadow:0 2px 8px #0004"><span style="font-size:18px">🛵</span></div>',
    className: '', iconSize: [36, 36], iconAnchor: [18, 18]
});
const iconDestino = L.divIcon({
    html: '<div style="background:#28a745;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid #fff;box-shadow:0 2px 8px #0004"><span style="font-size:16px">🏠</span></div>',
    className: '', iconSize: [32, 32], iconAnchor: [16, 32]
});
const iconLoja = L.divIcon({
    html: '<div style="background:#007bff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid #fff;box-shadow:0 2px 8px #0004"><span style="font-size:16px">🍔</span></div>',
    className: '', iconSize: [32, 32], iconAnchor: [16, 32]
});

let marcadorEntregador = null;
let rotaLine = null;

if (LAT_ATUAL && LNG_ATUAL) {
    marcadorEntregador = L.marker([LAT_ATUAL, LNG_ATUAL], { icon: iconEntregador }).addTo(mapa);
}
if (LAT_DESTINO && LNG_DESTINO) {
    L.marker([LAT_DESTINO, LNG_DESTINO], { icon: iconDestino }).addTo(mapa).bindPopup('Seu endereço');
}
if (LAT_COLETA && LNG_COLETA) {
    L.marker([LAT_COLETA, LNG_COLETA], { icon: iconLoja }).addTo(mapa).bindPopup('Loja');
}

function atualizarMapa(lat, lng) {
    if (!lat || !lng) return;
    if (marcadorEntregador) {
        marcadorEntregador.setLatLng([lat, lng]);
    } else {
        marcadorEntregador = L.marker([lat, lng], { icon: iconEntregador }).addTo(mapa);
    }
    mapa.panTo([lat, lng], { animate: true, duration: 1 });
}

function buscarStatus() {
    fetch(STATUS_API)
        .then(r => r.json())
        .then(data => {
            if (data.latitude && data.longitude) {
                atualizarMapa(parseFloat(data.latitude), parseFloat(data.longitude));
            }
            if (data.atualizado_em) {
                const dt = new Date(data.atualizado_em);
                document.getElementById('atualizadoEm').textContent =
                    'Atualizado às ' + dt.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
            if (data.pedido_status === 'entregue') {
                clearInterval(pollingInterval);
                document.getElementById('atualizadoEm').textContent = '✅ Pedido entregue!';
            }
        })
        .catch(() => {});
}

buscarStatus();
const pollingInterval = setInterval(buscarStatus, 8000);

// WebSocket via Reverb (se disponível)
if (window.Echo) {
    window.Echo.channel(`rastreamento.${TOKEN_RAST}`)
        .listen('.localizacao.atualizada', data => {
            atualizarMapa(parseFloat(data.latitude), parseFloat(data.longitude));
        });
}
</script>
</body>
</html>
