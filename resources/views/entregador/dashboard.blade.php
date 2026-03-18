@extends('layouts.pwa')
@section('titulo', 'Painel do Entregador')

@push('styles')
<style>
.entregador-header { background: linear-gradient(135deg, var(--cor-primaria), #e55a2b); color: #fff; padding: 20px 16px; }
.entregador-header h1 { font-size: 1.2rem; font-weight: 800; margin: 0 0 4px; }
.entregador-header p  { font-size: 0.85rem; opacity: .85; margin: 0; }
.disponivel-toggle { display: flex; align-items: center; justify-content: space-between; background: #fff; margin: 12px; border-radius: 14px; padding: 14px 16px; box-shadow: var(--sombra-sm); }
.disponivel-toggle-info strong { display: block; font-size: 0.95rem; font-weight: 800; }
.disponivel-toggle-info span { font-size: 0.8rem; color: var(--cor-texto-muted); }
.switch-grande { position: relative; display: inline-block; width: 58px; height: 30px; }
.switch-grande input { display: none; }
.switch-grande-slider {
    position: absolute; inset: 0; background: var(--cor-borda); border-radius: 30px; cursor: pointer; transition: .3s;
}
.switch-grande-slider::after {
    content: ''; position: absolute; top: 3px; left: 3px;
    width: 24px; height: 24px; background: #fff; border-radius: 50%;
    transition: .3s; box-shadow: 0 2px 6px rgba(0,0,0,.2);
}
.switch-grande input:checked + .switch-grande-slider { background: var(--cor-sucesso); }
.switch-grande input:checked + .switch-grande-slider::after { transform: translateX(28px); }
.stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 0 12px 12px; }
.stat-card { background: #fff; border-radius: 12px; padding: 14px; text-align: center; box-shadow: var(--sombra-sm); }
.stat-card .valor { font-size: 1.5rem; font-weight: 800; color: var(--cor-primaria); }
.stat-card .label { font-size: 0.75rem; color: var(--cor-texto-muted); margin-top: 2px; }
.secao-titulo-ent { font-size: 0.95rem; font-weight: 800; padding: 12px 16px 6px; }
.entrega-card {
    background: #fff; margin: 6px 12px; border-radius: 12px; padding: 14px;
    box-shadow: var(--sombra-sm); border-left: 4px solid var(--cor-primaria);
}
.entrega-card-header { display: flex; justify-content: space-between; margin-bottom: 8px; }
.entrega-card-numero { font-size: 0.82rem; font-weight: 800; color: var(--cor-primaria); font-family: monospace; }
.entrega-card-cliente { font-weight: 700; font-size: 0.92rem; }
.entrega-card-end { font-size: 0.82rem; color: var(--cor-texto-muted); margin: 4px 0; }
.entrega-card-acoes { display: flex; gap: 8px; margin-top: 10px; }
.btn-entrega { flex: 1; padding: 10px; border: none; border-radius: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: opacity .2s; }
.btn-entrega-aceitar { background: var(--cor-sucesso); color: #fff; }
.btn-entrega-coletar  { background: var(--cor-info, #17a2b8); color: #fff; }
.btn-entrega-mapa     { background: var(--cor-secundaria); color: #fff; }
.btn-entrega-confirmar{ background: var(--cor-primaria); color: #fff; }
.btn-entrega:hover { opacity: .88; }
.empty-entregas { text-align: center; padding: 32px 16px; color: var(--cor-texto-muted); }
.empty-entregas i { font-size: 3rem; opacity: .35; display: block; margin-bottom: 8px; }
</style>
@endpush

@section('conteudo')
<div class="entregador-header">
    <h1>Olá, {{ Auth::user()->nome_abreviado }}! 👋</h1>
    <p>Painel do Entregador</p>
</div>

{{-- Toggle Disponibilidade --}}
<div class="disponivel-toggle">
    <div class="disponivel-toggle-info">
        <strong id="disponiveltxt">{{ $funcionario->disponivel_entregas ? 'Você está disponível' : 'Você está offline' }}</strong>
        <span id="disponiveldesc">{{ $funcionario->disponivel_entregas ? 'Recebendo novas entregas' : 'Não está recebendo entregas' }}</span>
    </div>
    <label class="switch-grande">
        <input type="checkbox" id="toggleDisponivel" {{ $funcionario->disponivel_entregas ? 'checked' : '' }}>
        <span class="switch-grande-slider"></span>
    </label>
</div>

{{-- Estatísticas do dia --}}
<div class="stats-row">
    <div class="stat-card">
        <div class="valor">{{ $entregasHoje }}</div>
        <div class="label">Entregas Hoje</div>
    </div>
    <div class="stat-card">
        <div class="valor">R$ {{ number_format($faturamentoHoje, 2, ',', '.') }}</div>
        <div class="label">Ganhos Hoje</div>
    </div>
</div>

{{-- Entregas Ativas --}}
@if($entregasAtivas->count())
<p class="secao-titulo-ent"><i class="bi bi-lightning-charge text-warning"></i> Em andamento ({{ $entregasAtivas->count() }})</p>
@foreach($entregasAtivas as $entrega)
<div class="entrega-card" id="entrega-{{ $entrega->id }}">
    <div class="entrega-card-header">
        <span class="entrega-card-numero">{{ $entrega->pedido->numero }}</span>
        <span class="badge-status" style="background:#FF6B3520;color:#FF6B35">{{ ucfirst($entrega->status) }}</span>
    </div>
    <div class="entrega-card-cliente">{{ $entrega->pedido->usuario->nome }}</div>
    <div class="entrega-card-end">
        <i class="bi bi-geo-alt"></i>
        {{ $entrega->pedido->endereco_logradouro }}, {{ $entrega->pedido->endereco_numero }} —
        {{ $entrega->pedido->endereco_bairro }}, {{ $entrega->pedido->endereco_cidade }}
    </div>
    <div class="entrega-card-acoes">
        @if($entrega->status === 'aceito')
        <button class="btn-entrega btn-entrega-coletar" onclick="coletarPedido({{ $entrega->id }})">
            <i class="bi bi-box-arrow-up"></i> Coletei
        </button>
        @elseif(in_array($entrega->status, ['coletado', 'em_rota']))
        <button class="btn-entrega btn-entrega-confirmar" onclick="confirmarEntrega({{ $entrega->id }})">
            <i class="bi bi-check-circle"></i> Entreguei
        </button>
        @endif
        <a href="{{ route('entregador.mapa', $entrega) }}" class="btn-entrega btn-entrega-mapa" style="text-align:center;text-decoration:none">
            <i class="bi bi-map"></i> Mapa
        </a>
    </div>
</div>
@endforeach
@endif

{{-- Entregas Pendentes (aguardando aceite) --}}
<p class="secao-titulo-ent"><i class="bi bi-clock text-info"></i> Aguardando aceite ({{ $entregasPendentes->count() }})</p>
@forelse($entregasPendentes as $entrega)
<div class="entrega-card" style="border-color:var(--cor-info,#17a2b8)">
    <div class="entrega-card-header">
        <span class="entrega-card-numero">{{ $entrega->pedido->numero }}</span>
        <span>R$ {{ number_format($entrega->taxa_entrega, 2, ',', '.') }}</span>
    </div>
    <div class="entrega-card-cliente">{{ $entrega->pedido->usuario->nome }}</div>
    <div class="entrega-card-end">
        <i class="bi bi-geo-alt"></i>
        {{ $entrega->pedido->endereco_bairro }}, {{ $entrega->pedido->endereco_cidade }}
    </div>
    <div class="entrega-card-acoes">
        <button class="btn-entrega btn-entrega-aceitar" onclick="aceitarEntrega({{ $entrega->id }}, this)">
            <i class="bi bi-check-lg"></i> Aceitar
        </button>
    </div>
</div>
@empty
<div class="empty-entregas">
    <i class="bi bi-bicycle"></i>
    <p>Nenhuma entrega disponível no momento</p>
</div>
@endforelse

<div style="height:20px"></div>
@endsection

@push('scripts')
<script>
document.getElementById('toggleDisponivel').addEventListener('change', async function() {
    const txt  = document.getElementById('disponiveltxt');
    const desc = document.getElementById('disponiveldesc');
    try {
        const res  = await fetch('/entregador/disponibilidade', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        txt.textContent  = data.disponivel ? 'Você está disponível' : 'Você está offline';
        desc.textContent = data.disponivel ? 'Recebendo novas entregas' : 'Não está recebendo entregas';
        mostrarToast(data.mensagem, data.disponivel ? 'sucesso' : 'info');
    } catch { mostrarToast('Erro ao alterar disponibilidade.', 'erro'); this.checked = !this.checked; }
});

async function aceitarEntrega(entregaId, btn) {
    btn.disabled = true; btn.textContent = 'Aceitando...';
    try {
        const res  = await fetch(`/entregador/entregas/${entregaId}/aceitar`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.sucesso) { mostrarToast('Entrega aceita!', 'sucesso'); setTimeout(() => location.reload(), 800); }
        else { mostrarToast(data.erro, 'erro'); btn.disabled = false; btn.textContent = 'Aceitar'; }
    } catch { mostrarToast('Erro.', 'erro'); btn.disabled = false; btn.textContent = 'Aceitar'; }
}

async function coletarPedido(entregaId) {
    if (!confirm('Confirmar coleta do pedido?')) return;
    const res  = await fetch(`/entregador/entregas/${entregaId}/coletar`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();
    if (data.sucesso) { mostrarToast('Saiu para entrega!', 'sucesso'); setTimeout(() => location.reload(), 800); }
    else mostrarToast(data.erro, 'erro');
}

async function confirmarEntrega(entregaId) {
    if (!confirm('Confirmar que o pedido foi entregue?')) return;
    const res  = await fetch(`/entregador/entregas/${entregaId}/confirmar`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();
    if (data.sucesso) { mostrarToast('Entrega confirmada! 🎉', 'sucesso'); setTimeout(() => location.reload(), 1000); }
    else mostrarToast(data.erro, 'erro');
}

// Atualizar localização em background
let geoWatcher = null;
if (navigator.geolocation) {
    geoWatcher = navigator.geolocation.watchPosition(pos => {
        const { latitude, longitude } = pos.coords;
        // Atualizar localização das entregas ativas
        document.querySelectorAll('[id^="entrega-"]').forEach(el => {
            const entregaId = el.id.replace('entrega-', '');
            fetch(`/entregador/entregas/${entregaId}/localizacao`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ latitude, longitude })
            }).catch(() => {});
        });
    }, null, { enableHighAccuracy: true, maximumAge: 10000 });
}

// Polling por novas entregas
setInterval(() => location.reload(), 30000);
</script>
@endpush
