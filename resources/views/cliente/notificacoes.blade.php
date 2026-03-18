@extends('layouts.pwa')
@section('titulo', 'Notificações')

@section('conteudo')
<div class="page-header">
    <button onclick="history.back()" class="btn-voltar"><i class="bi bi-arrow-left"></i></button>
    <h1 class="page-title">Notificações</h1>
    @if($notificacoes->total() > 0)
    <button onclick="marcarTodasLidas()" class="btn-ver-todas" style="margin-left:auto;font-size:.78rem;background:none;border:none;color:var(--cor-primaria);font-weight:700;cursor:pointer">
        Marcar todas como lidas
    </button>
    @endif
</div>

<div class="notif-lista">
    @forelse($notificacoes as $notif)
    <div class="notif-item {{ $notif->lida ? 'notif-lida' : '' }}" id="notif-{{ $notif->id }}" onclick="marcarLida({{ $notif->id }}, this)">
        <div class="notif-icone" style="background:{{ $notif->cor ?? '#FF6B35' }}20;color:{{ $notif->cor ?? '#FF6B35' }}">
            <i class="bi bi-{{ $notif->icone ?? 'bell' }}"></i>
        </div>
        <div class="notif-corpo">
            <div class="notif-titulo">{{ $notif->titulo }}</div>
            <div class="notif-msg">{{ $notif->mensagem }}</div>
            <div class="notif-data">{{ $notif->created_at->diffForHumans() }}</div>
        </div>
        @if(!$notif->lida)
        <div class="notif-badge-nao-lida"></div>
        @endif
    </div>
    @empty
    <div class="estado-vazio" style="padding:60px 20px">
        <i class="bi bi-bell-slash fs-1 d-block text-muted mb-2"></i>
        <p class="text-muted">Nenhuma notificação ainda.</p>
    </div>
    @endforelse
</div>

{{ $notificacoes->links('vendor.pagination.simple-bootstrap') }}

<style>
.notif-lista { padding: 8px 0 80px; }
.notif-item { display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; background: var(--cor-card); border-bottom: 1px solid var(--cor-borda); cursor: pointer; transition: background .15s; position: relative; }
.notif-item:hover { background: var(--cor-fundo); }
.notif-lida { opacity: .7; }
.notif-icone { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.notif-corpo { flex: 1; min-width: 0; }
.notif-titulo { font-weight: 800; font-size: .9rem; margin-bottom: 2px; }
.notif-msg { font-size: .82rem; color: var(--cor-texto-muted); line-height: 1.4; }
.notif-data { font-size: .75rem; color: var(--cor-texto-muted); margin-top: 4px; }
.notif-badge-nao-lida { width: 10px; height: 10px; border-radius: 50%; background: var(--cor-primaria); flex-shrink: 0; margin-top: 4px; }
</style>
@endsection

@push('scripts')
<script>
async function marcarLida(id, el) {
    if (el.classList.contains('notif-lida')) return;
    await fetch(`/notificacoes/${id}/lida`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' }
    }).catch(() => {});
    el.classList.add('notif-lida');
    el.querySelector('.notif-badge-nao-lida')?.remove();
}

async function marcarTodasLidas() {
    await fetch('/notificacoes/todas-lidas', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' }
    }).catch(() => {});
    document.querySelectorAll('.notif-item').forEach(el => {
        el.classList.add('notif-lida');
        el.querySelector('.notif-badge-nao-lida')?.remove();
    });
    mostrarToast('Todas as notificações marcadas como lidas', 'sucesso');
}
</script>
@endpush
