<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🍳 Cozinha — {{ $loja->nome }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --cor-confirmado:#ffc107; --cor-preparo:#FF6B35; --cor-pronto:#28a745; }
        * { margin:0;padding:0;box-sizing:border-box }
        body { font-family:'Nunito',sans-serif;background:#0d1117;color:#e6edf3;min-height:100vh }
        header { background:#161b22;border-bottom:1px solid #30363d;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100 }
        .header-left { display:flex;align-items:center;gap:12px }
        .header-logo { font-size:1.6rem }
        .header-info h1 { font-size:1.1rem;font-weight:900;color:#fff }
        .header-info small { font-size:.75rem;color:#8b949e }
        .header-right { display:flex;align-items:center;gap:16px }
        .relogio { font-size:1.4rem;font-weight:900;color:#58a6ff;font-family:monospace }
        .btn-som { padding:8px 16px;border-radius:8px;border:1px solid #30363d;background:#21262d;color:#e6edf3;font-family:inherit;cursor:pointer;font-size:.85rem;display:flex;align-items:center;gap:6px;font-weight:700 }
        .btn-som.ativo { border-color:#28a745;color:#28a745 }
        .btn-som.mudo { border-color:#dc3545;color:#dc3545 }
        .contador-badge { background:#FF6B35;color:#fff;border-radius:50%;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:900 }

        .kanban { display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;padding:24px;align-items:start }
        .coluna { background:#161b22;border-radius:16px;border:1px solid #30363d;overflow:hidden }
        .coluna-header { padding:14px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #30363d }
        .coluna-titulo { font-size:1rem;font-weight:900;display:flex;align-items:center;gap:8px }
        .coluna-count { background:#30363d;border-radius:20px;padding:2px 10px;font-size:.8rem;font-weight:700 }
        .coluna[data-status="confirmado"] .coluna-header { background:rgba(255,193,7,.08);border-left:4px solid var(--cor-confirmado) }
        .coluna[data-status="em_preparo"] .coluna-header { background:rgba(255,107,53,.08);border-left:4px solid var(--cor-preparo) }
        .coluna-body { padding:12px;display:flex;flex-direction:column;gap:10px;min-height:200px }

        .card-pedido { background:#0d1117;border:1px solid #30363d;border-radius:12px;padding:14px;transition:.15s;position:relative;overflow:hidden }
        .card-pedido.urgente { border-color:#dc3545;animation:pulseBorder 1.5s ease-in-out infinite }
        @keyframes pulseBorder { 0%,100%{border-color:#dc3545}50%{border-color:#ff6b6b} }
        .card-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:10px }
        .card-numero { font-size:1.1rem;font-weight:900;color:#58a6ff;font-family:monospace }
        .card-tempo { font-size:.75rem;padding:3px 8px;border-radius:20px;font-weight:700 }
        .card-tempo.ok { background:rgba(40,167,69,.15);color:#28a745 }
        .card-tempo.alerta { background:rgba(255,193,7,.15);color:#ffc107 }
        .card-tempo.urgente { background:rgba(220,53,69,.15);color:#dc3545 }
        .card-cliente { font-size:.85rem;color:#8b949e;margin-bottom:8px;display:flex;align-items:center;gap:6px }
        .card-tipo { font-size:.75rem;font-weight:700;padding:2px 8px;border-radius:20px }
        .card-tipo.entrega { background:rgba(88,166,255,.15);color:#58a6ff }
        .card-tipo.retirada { background:rgba(88,255,109,.15);color:#3fb950 }

        .itens-lista { border-top:1px solid #30363d;padding-top:8px;margin-top:8px }
        .item-linha { display:flex;align-items:flex-start;gap:8px;padding:4px 0;font-size:.88rem }
        .item-qtd { font-weight:900;color:#FF6B35;min-width:28px }
        .item-nome { flex:1 }
        .item-obs { font-size:.75rem;color:#ffc107;margin-top:2px;font-style:italic }
        .item-adicionais { font-size:.75rem;color:#8b949e;margin-top:1px }

        .card-obs-geral { background:rgba(255,193,7,.08);border:1px solid rgba(255,193,7,.3);border-radius:8px;padding:6px 10px;font-size:.8rem;color:#ffc107;margin-top:8px }

        .btn-avancar { width:100%;margin-top:12px;padding:10px;border-radius:8px;border:none;font-family:inherit;font-weight:800;font-size:.9rem;cursor:pointer;transition:.15s;display:flex;align-items:center;justify-content:center;gap:6px }
        .btn-avancar.preparar { background:#ffc107;color:#000 }
        .btn-avancar.pronto { background:#28a745;color:#fff }
        .btn-avancar:hover { opacity:.85 }
        .btn-avancar:disabled { opacity:.4;cursor:not-allowed }

        .empty-col { text-align:center;color:#8b949e;font-size:.9rem;padding:32px 16px }
        .empty-col i { font-size:2rem;display:block;margin-bottom:8px }

        /* Alerta de novo pedido */
        .alerta-novo { position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(255,107,53,.15);display:none;align-items:center;justify-content:center;z-index:1000;animation:flashBg .5s ease-in-out 3 }
        @keyframes flashBg { 0%,100%{background:rgba(255,107,53,.0)}50%{background:rgba(255,107,53,.25)} }
        .alerta-novo-box { background:#161b22;border:3px solid #FF6B35;border-radius:20px;padding:32px 48px;text-align:center;box-shadow:0 0 60px rgba(255,107,53,.5) }
        .alerta-novo-box .icone { font-size:4rem;animation:bounce .5s ease-in-out 3 }
        @keyframes bounce { 0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)} }
        .alerta-novo-box h2 { font-size:2rem;font-weight:900;color:#FF6B35;margin:8px 0 4px }
        .alerta-novo-box p { color:#8b949e }
        .btn-dispensar { margin-top:16px;padding:10px 28px;background:#FF6B35;color:#fff;border:none;border-radius:10px;font-family:inherit;font-weight:800;font-size:1rem;cursor:pointer }
    </style>
</head>
<body>

<header>
    <div class="header-left">
        <div class="header-logo">🍳</div>
        <div class="header-info">
            <h1>Tela da Cozinha</h1>
            <small>{{ $loja->nome }}</small>
        </div>
    </div>
    <div class="header-right">
        <div class="relogio" id="relogio">00:00:00</div>
        <button class="btn-som ativo" id="btnSom" onclick="toggleSom()">
            <i class="bi bi-volume-up-fill"></i> Som <span class="contador-badge" id="totalPedidos">0</span>
        </button>
        <a href="{{ route('admin.pedidos.kanban') }}" class="btn-som" style="text-decoration:none">
            <i class="bi bi-kanban"></i> Kanban
        </a>
    </div>
</header>

{{-- Alerta visual de novo pedido --}}
<div class="alerta-novo" id="alertaNovo">
    <div class="alerta-novo-box">
        <div class="icone">🔔</div>
        <h2>NOVO PEDIDO!</h2>
        <p id="alertaTexto">Um novo pedido está aguardando preparo.</p>
        <button class="btn-dispensar" onclick="dispensarAlerta()">Ver Pedido</button>
    </div>
</div>

<div class="kanban" id="kanban">
    {{-- Confirmado --}}
    <div class="coluna" data-status="confirmado">
        <div class="coluna-header">
            <span class="coluna-titulo">
                <span>🟡</span> Confirmado
                <span class="coluna-count" id="count-confirmado">{{ $pedidos->where('status','confirmado')->count() }}</span>
            </span>
        </div>
        <div class="coluna-body" id="col-confirmado">
            @forelse($pedidos->where('status','confirmado') as $pedido)
            @include('admin.cozinha._card', ['pedido' => $pedido])
            @empty
            <div class="empty-col"><i class="bi bi-check-circle"></i>Nenhum pedido confirmado</div>
            @endforelse
        </div>
    </div>

    {{-- Em Preparo --}}
    <div class="coluna" data-status="em_preparo">
        <div class="coluna-header">
            <span class="coluna-titulo">
                <span>🔴</span> Em Preparo
                <span class="coluna-count" id="count-em_preparo">{{ $pedidos->where('status','em_preparo')->count() }}</span>
            </span>
        </div>
        <div class="coluna-body" id="col-em_preparo">
            @forelse($pedidos->where('status','em_preparo') as $pedido)
            @include('admin.cozinha._card', ['pedido' => $pedido])
            @empty
            <div class="empty-col"><i class="bi bi-fire"></i>Nenhum pedido em preparo</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Audio de notificação (Base64 inline para funcionar offline) --}}
<audio id="audioNovoPedido" preload="auto">
    <source src="{{ asset('sounds/novo-pedido.mp3') }}" type="audio/mpeg">
</audio>
<audio id="audioPronto" preload="auto">
    <source src="{{ asset('sounds/pronto.mp3') }}" type="audio/mpeg">
</audio>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let somAtivo = true;
let ultimoId  = {{ $pedidos->max('id') ?? 0 }};
let pedidosAtuais = {};

// Relógio
function atualizarRelogio() {
    const agora = new Date();
    document.getElementById('relogio').textContent =
        agora.toLocaleTimeString('pt-BR', {hour12: false});
}
setInterval(atualizarRelogio, 1000);
atualizarRelogio();

function toggleSom() {
    somAtivo = !somAtivo;
    const btn = document.getElementById('btnSom');
    btn.className = 'btn-som ' + (somAtivo ? 'ativo' : 'mudo');
    btn.innerHTML = `<i class="bi bi-volume-${somAtivo ? 'up-fill' : 'mute-fill'}"></i> Som <span class="contador-badge" id="totalPedidos">0</span>`;
    atualizarContador();
}

function tocarSom(id) {
    if (!somAtivo) return;
    const el = document.getElementById(id);
    if (el) { el.currentTime = 0; el.play().catch(() => {}); }
}

function atualizarContador() {
    const total = document.querySelectorAll('.card-pedido').length;
    document.querySelectorAll('.contador-badge').forEach(el => el.textContent = total);
    document.getElementById('totalPedidos').textContent = total;
}

function dispensarAlerta() {
    document.getElementById('alertaNovo').style.display = 'none';
}

function mostrarAlertaNovo(numero) {
    const el = document.getElementById('alertaNovo');
    document.getElementById('alertaTexto').textContent = `Pedido ${numero} aguarda preparo!`;
    el.style.display = 'flex';
    tocarSom('audioNovoPedido');
    setTimeout(() => { el.style.display = 'none'; }, 10000);
}

function tempoClass(minutos) {
    if (minutos < 15) return 'ok';
    if (minutos < 25) return 'alerta';
    return 'urgente';
}

function tempoLabel(minutos) {
    if (minutos < 1) return 'agora';
    if (minutos < 60) return `${minutos}min`;
    const h = Math.floor(minutos / 60);
    const m = minutos % 60;
    return `${h}h${m > 0 ? m + 'm' : ''}`;
}

function renderCard(p) {
    const cls = tempoClass(p.minutos_atras);
    const urgente = p.minutos_atras >= 25 ? ' urgente' : '';
    let itensHtml = '';
    p.itens.forEach(item => {
        let adicsHtml = item.adicionais.length
            ? `<div class="item-adicionais">+ ${item.adicionais.map(a => `${a.quantidade}x ${a.nome}`).join(', ')}</div>`
            : '';
        let obsHtml = item.observacoes ? `<div class="item-obs">⚠️ ${item.observacoes}</div>` : '';
        itensHtml += `<div class="item-linha"><span class="item-qtd">${p.quantidade || item.quantidade}x</span><div><div class="item-nome">${item.produto_nome}</div>${obsHtml}${adicsHtml}</div></div>`;
        // fix: use item.quantidade not p.quantidade
        itensHtml = itensHtml.replace(`>${p.quantidade || item.quantidade}x`, `>${item.quantidade}x`);
    });
    const obsGeralHtml = p.observacoes ? `<div class="card-obs-geral">📝 ${p.observacoes}</div>` : '';
    const btnLabel = p.status === 'confirmado'
        ? '<i class="bi bi-fire"></i> Iniciar Preparo'
        : '<i class="bi bi-check-circle-fill"></i> Marcar como Pronto';
    const btnClass = p.status === 'confirmado' ? 'preparar' : 'pronto';
    const tipoClass = p.tipo_entrega === 'retirada' ? 'retirada' : 'entrega';
    const tipoLabel = p.tipo_entrega === 'retirada' ? '🏪 Retirada' : '🛵 Entrega';

    return `<div class="card-pedido${urgente}" id="card-${p.id}" data-id="${p.id}" data-status="${p.status}">
        <div class="card-header">
            <span class="card-numero">${p.numero}</span>
            <span class="card-tempo ${cls}">${tempoLabel(p.minutos_atras)}</span>
        </div>
        <div class="card-cliente">
            <i class="bi bi-person-fill"></i> ${p.usuario_nome}
            <span class="card-tipo ${tipoClass}">${tipoLabel}</span>
        </div>
        <div class="itens-lista">${itensHtml}</div>
        ${obsGeralHtml}
        <button class="btn-avancar ${btnClass}" onclick="avancarStatus(${p.id}, this)">${btnLabel}</button>
    </div>`;
}

function avancarStatus(pedidoId, btn) {
    btn.disabled = true;
    fetch(`/admin/cozinha/${pedidoId}/avancar`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.sucesso) {
            if (data.novo_status === 'pronto') {
                document.getElementById('card-' + pedidoId)?.remove();
                tocarSom('audioPronto');
            } else {
                atualizarTela();
            }
            atualizarContador();
        } else {
            btn.disabled = false;
        }
    })
    .catch(() => { btn.disabled = false; });
}

function atualizarTela() {
    fetch('{{ route("admin.cozinha.pedidos") }}', {
        headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        const novos = data.pedidos;
        const novoUltimoId = data.ultimo_id;

        // Detectar novos pedidos
        if (novoUltimoId > ultimoId) {
            const novosPedidos = novos.filter(p => p.id > ultimoId);
            novosPedidos.forEach(p => mostrarAlertaNovo(p.numero));
            ultimoId = novoUltimoId;
        }

        // Re-renderizar colunas
        ['confirmado', 'em_preparo'].forEach(status => {
            const col = document.getElementById('col-' + status);
            const grupo = novos.filter(p => p.status === status);
            document.getElementById('count-' + status).textContent = grupo.length;

            grupo.forEach(p => {
                const existing = document.getElementById('card-' + p.id);
                if (!existing) {
                    const emptyMsg = col.querySelector('.empty-col');
                    if (emptyMsg) emptyMsg.remove();
                    col.insertAdjacentHTML('beforeend', renderCard(p));
                }
            });

            // Remover cards que não estão mais nesta coluna
            col.querySelectorAll('.card-pedido').forEach(card => {
                const id = parseInt(card.dataset.id);
                const ainda = grupo.find(p => p.id === id);
                if (!ainda) card.remove();
            });

            if (col.querySelectorAll('.card-pedido').length === 0) {
                const icons = { confirmado: 'check-circle', em_preparo: 'fire' };
                col.innerHTML = `<div class="empty-col"><i class="bi bi-${icons[status]}"></i>Nenhum pedido</div>`;
            }
        });

        atualizarContador();
    })
    .catch(() => {});
}

// Poll a cada 10 segundos
setInterval(atualizarTela, 10000);
atualizarContador();
</script>
</body>
</html>
