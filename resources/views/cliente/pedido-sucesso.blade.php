@extends('layouts.pwa')
@section('titulo', 'Pedido Confirmado!')

@section('conteudo')
<div class="sucesso-container">
    <div class="sucesso-icone animate-pop">
        <i class="bi bi-check-circle-fill"></i>
    </div>
    <h1 class="sucesso-titulo">Pedido Confirmado!</h1>
    <p class="sucesso-subtitulo">Seu pedido foi recebido com sucesso.</p>

    <div class="sucesso-card">
        <div class="sucesso-linha">
            <span>Número do Pedido</span>
            <strong class="text-primaria">#{{ $pedido->numero }}</strong>
        </div>
        <div class="sucesso-linha">
            <span>Status</span>
            <span class="badge-status-inline" style="color:{{ $pedido->status_cor }}">
                {{ $pedido->status_label }}
            </span>
        </div>
        <div class="sucesso-linha">
            <span>Total Pago</span>
            <strong>R$ {{ number_format($pedido->total, 2, ',', '.') }}</strong>
        </div>
        <div class="sucesso-linha">
            <span>Tempo Estimado</span>
            <strong>{{ $pedido->tempo_estimado_min }} min</strong>
        </div>
        @if($pedido->tipo_entrega === 'entrega')
        <div class="sucesso-linha">
            <span>Entrega em</span>
            <strong>{{ $pedido->endereco_logradouro }}, {{ $pedido->endereco_numero }} — {{ $pedido->endereco_bairro }}</strong>
        </div>
        @else
        <div class="sucesso-linha">
            <span>Retirada</span>
            <strong>No balcão — {{ $pedido->loja->nome }}</strong>
        </div>
        @endif
    </div>

    <div class="sucesso-acoes">
        <a href="{{ route('cliente.pedidos.show', $pedido) }}" class="btn btn-primario w-100 mb-2">
            <i class="bi bi-bag-check"></i> Acompanhar Pedido
        </a>
        @if($pedido->tipo_entrega === 'entrega' && $pedido->link_rastreamento)
        <a href="{{ $pedido->link_rastreamento }}" class="btn btn-outline w-100 mb-2" target="_blank">
            <i class="bi bi-geo-alt"></i> Rastrear Entrega
        </a>
        @endif
        <a href="{{ route('cliente.loja', $pedido->loja->slug) }}" class="btn btn-outline w-100">
            <i class="bi bi-house"></i> Voltar ao Cardápio
        </a>
    </div>
</div>

<style>
.sucesso-container { display: flex; flex-direction: column; align-items: center; padding: 40px 20px 30px; text-align: center; }
.sucesso-icone { font-size: 5rem; color: var(--cor-sucesso); margin-bottom: 16px; }
.sucesso-icone i { display: block; }
.sucesso-titulo { font-size: 1.5rem; font-weight: 800; margin: 0 0 8px; }
.sucesso-subtitulo { color: var(--cor-texto-muted); margin: 0 0 24px; }
.sucesso-card { width: 100%; background: var(--cor-card); border-radius: 16px; padding: 16px; box-shadow: var(--sombra-sm); margin-bottom: 24px; text-align: left; }
.sucesso-linha { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--cor-borda); font-size: 0.9rem; }
.sucesso-linha:last-child { border: none; }
.sucesso-linha span:first-child { color: var(--cor-texto-muted); }
.sucesso-acoes { width: 100%; }
.text-primaria { color: var(--cor-primaria); }
.badge-status-inline { font-weight: 700; }
.animate-pop { animation: popIn .5s cubic-bezier(.34,1.56,.64,1) forwards; }
@keyframes popIn { 0%{ transform: scale(0); opacity:0; } 100%{ transform: scale(1); opacity:1; } }
.mb-2 { margin-bottom: 8px; }
</style>
@endsection

@push('scripts')
<script>
// Confetti simples
function confetti() {
    const colors = ['#FF6B35','#2C3E50','#28A745','#FFC107','#17A2B8'];
    for (let i = 0; i < 60; i++) {
        const el = document.createElement('div');
        el.style.cssText = `
            position:fixed; width:8px; height:8px; border-radius:50%;
            background:${colors[i % colors.length]};
            left:${Math.random()*100}vw; top:-10px;
            animation:confettiCair ${1 + Math.random()*2}s ease-in ${Math.random()*1}s forwards;
            z-index:9999; pointer-events:none;
        `;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }
}
const style = document.createElement('style');
style.textContent = '@keyframes confettiCair { to { transform: translateY(110vh) rotate(720deg); opacity:0; } }';
document.head.appendChild(style);
setTimeout(confetti, 300);
</script>
@endpush
