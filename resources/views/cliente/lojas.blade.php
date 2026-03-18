@extends('layouts.pwa')
@section('titulo', 'Restaurantes')

@section('conteudo')
<div class="lojas-header">
    <h1 class="lojas-titulo">🍔 Restaurantes</h1>
    <div class="busca-rapida px-0 pt-0">
        <div class="input-busca">
            <i class="bi bi-search"></i>
            <input type="text" id="inputBuscaLoja" placeholder="Buscar restaurante..." autocomplete="off">
        </div>
    </div>
</div>

{{-- Categorias/Filtros --}}
<div class="lojas-filtros">
    <button class="filtro-btn active" data-filtro="">Todos</button>
    <button class="filtro-btn" data-filtro="aberta">Abertos agora</button>
    <button class="filtro-btn" data-filtro="entrega">Com entrega</button>
    <button class="filtro-btn" data-filtro="retirada">Retirada</button>
</div>

{{-- Lista de Lojas --}}
<div class="lojas-grid" id="lojasGrid">
    @forelse($lojas as $loja)
    <a href="{{ route('cliente.loja', $loja->slug) }}" class="loja-card {{ !$loja->estaAberta() ? 'loja-fechada' : '' }}"
       data-aberta="{{ $loja->estaAberta() ? '1' : '0' }}"
       data-entrega="{{ $loja->aceita_entrega ? '1' : '0' }}"
       data-retirada="{{ $loja->aceita_retirada ? '1' : '0' }}">

        <div class="loja-card-banner">
            <img src="{{ $loja->banner_url }}" alt="{{ $loja->nome }}" loading="lazy" class="loja-banner-img">
            @if(!$loja->estaAberta())
            <div class="loja-fechada-overlay">
                <span>Fechado</span>
            </div>
            @endif
            @if($loja->logo)
            <img src="{{ $loja->logo_url }}" alt="{{ $loja->nome }}" class="loja-logo-sobre">
            @endif
        </div>

        <div class="loja-card-info">
            <div class="loja-card-nome-row">
                <h2 class="loja-card-nome">{{ $loja->nome }}</h2>
                <span class="loja-status {{ $loja->estaAberta() ? 'aberta' : 'fechada' }}">
                    <i class="bi bi-circle-fill"></i>
                    {{ $loja->estaAberta() ? 'Aberta' : 'Fechada' }}
                </span>
            </div>
            <p class="loja-card-desc">{{ Str::limit($loja->descricao, 60) }}</p>
            <div class="loja-card-meta">
                <span><i class="bi bi-clock"></i> {{ $loja->tempo_entrega_min }}–{{ $loja->tempo_entrega_max }} min</span>
                @if($loja->aceita_entrega)
                <span>
                    <i class="bi bi-bicycle"></i>
                    @if($loja->tipo_taxa_entrega === 'gratis') Grátis
                    @elseif($loja->taxa_entrega_fixa > 0) R$ {{ number_format($loja->taxa_entrega_fixa, 2, ',', '.') }}
                    @else A calcular
                    @endif
                </span>
                @endif
                @if($loja->pedido_minimo > 0)
                <span><i class="bi bi-bag-check"></i> Mín. R$ {{ number_format($loja->pedido_minimo, 2, ',', '.') }}</span>
                @endif
            </div>
        </div>
    </a>
    @empty
    <div class="empty-state py-5">
        <i class="bi bi-shop fs-1 d-block mb-3 text-muted"></i>
        <p class="text-muted">Nenhum restaurante disponível no momento.</p>
    </div>
    @endforelse
</div>

<style>
.lojas-header { padding: 16px 16px 8px; background: var(--cor-card); }
.lojas-titulo { font-size: 1.3rem; font-weight: 800; margin: 0 0 12px; }
.lojas-filtros {
    display: flex; gap: 8px; padding: 8px 12px; overflow-x: auto;
    scrollbar-width: none; background: var(--cor-fundo);
}
.lojas-filtros::-webkit-scrollbar { display: none; }
.filtro-btn {
    padding: 7px 16px; border-radius: 20px; border: 1.5px solid var(--cor-borda);
    background: var(--cor-card); font-family: inherit; font-size: 0.82rem; font-weight: 700;
    white-space: nowrap; cursor: pointer; transition: all var(--transition);
}
.filtro-btn.active { background: var(--cor-primaria); color: #fff; border-color: var(--cor-primaria); }
.lojas-grid { padding: 12px; display: flex; flex-direction: column; gap: 12px; }
.loja-card {
    display: block; background: var(--cor-card); border-radius: 16px;
    overflow: hidden; text-decoration: none; color: var(--cor-texto);
    box-shadow: var(--sombra-sm); transition: transform var(--transition);
    border: 1px solid var(--cor-borda);
}
.loja-card:active { transform: scale(.99); }
.loja-card-banner { position: relative; aspect-ratio: 16/7; overflow: hidden; background: #f0f0f0; }
.loja-banner-img { width: 100%; height: 100%; object-fit: cover; }
.loja-fechada-overlay {
    position: absolute; inset: 0; background: rgba(0,0,0,.55);
    display: flex; align-items: center; justify-content: center;
}
.loja-fechada-overlay span { color: #fff; font-weight: 800; font-size: 1.1rem; }
.loja-logo-sobre {
    position: absolute; bottom: 10px; left: 12px;
    width: 52px; height: 52px; border-radius: 10px; object-fit: cover;
    border: 2.5px solid #fff; box-shadow: var(--sombra-sm);
}
.loja-card-info { padding: 12px 14px 14px; }
.loja-card-nome-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 4px; }
.loja-card-nome { font-size: 1rem; font-weight: 800; margin: 0; }
.loja-status { font-size: 0.72rem; font-weight: 700; display: flex; align-items: center; gap: 4px; white-space: nowrap; }
.loja-status i { font-size: 0.45rem; }
.loja-status.aberta { color: var(--cor-sucesso); }
.loja-status.fechada { color: var(--cor-texto-muted); }
.loja-card-desc { font-size: 0.82rem; color: var(--cor-texto-muted); margin: 0 0 8px; line-height: 1.4; }
.loja-card-meta { display: flex; gap: 12px; flex-wrap: wrap; font-size: 0.78rem; color: var(--cor-texto-muted); font-weight: 600; }
.loja-card-meta i { color: var(--cor-primaria); }
.loja-fechada .loja-banner-img { filter: grayscale(.6); }
</style>
@endsection

@push('scripts')
<script>
let filtroAtivo = '';

document.querySelectorAll('.filtro-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        filtroAtivo = this.dataset.filtro;
        aplicarFiltros();
    });
});

document.getElementById('inputBuscaLoja').addEventListener('input', function() {
    aplicarFiltros(this.value.trim().toLowerCase());
});

function aplicarFiltros(busca = '') {
    document.querySelectorAll('.loja-card').forEach(card => {
        const nome   = card.querySelector('.loja-card-nome')?.textContent.toLowerCase() || '';
        const aberta = card.dataset.aberta === '1';
        const entrega = card.dataset.entrega === '1';
        const retirada= card.dataset.retirada === '1';
        let visivel   = true;
        if (busca && !nome.includes(busca)) visivel = false;
        if (filtroAtivo === 'aberta'   && !aberta)   visivel = false;
        if (filtroAtivo === 'entrega'  && !entrega)  visivel = false;
        if (filtroAtivo === 'retirada' && !retirada) visivel = false;
        card.style.display = visivel ? '' : 'none';
    });
}
</script>
@endpush
