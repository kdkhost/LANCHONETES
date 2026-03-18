@extends('layouts.pwa')
@section('titulo', 'Busca — ' . $q)

@section('conteudo')
<div class="page-header">
    <button onclick="history.back()" class="btn-voltar"><i class="bi bi-arrow-left"></i></button>
    <h1 class="page-title">Busca</h1>
</div>

<div class="busca-topo">
    <form action="/buscar" method="GET" class="busca-form-bar">
        <div class="busca-input-wrap">
            <i class="bi bi-search busca-icone"></i>
            <input type="text" name="q" value="{{ $q }}" class="busca-input" placeholder="Buscar produtos..." autofocus>
            @if($q)
            <a href="/buscar" class="busca-clear"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>
</div>

@if($q)
<div class="busca-resultado-info">
    @if($produtos->total() > 0)
    <span>{{ $produtos->total() }} resultado(s) para <strong>"{{ $q }}"</strong></span>
    @else
    <span>Nenhum resultado para <strong>"{{ $q }}"</strong></span>
    @endif
</div>
@endif

@if($produtos->count() > 0)
<div class="produtos-grid">
    @foreach($produtos as $produto)
    <a href="{{ route('cliente.produto', ['lojaSlug' => $loja->slug, 'slug' => $produto->slug]) }}" class="produto-card">
        <div class="produto-img-wrap">
            @if($produto->imagem_principal)
            <img src="{{ $produto->imagem_url }}" alt="{{ $produto->nome }}" class="produto-img" loading="lazy">
            @else
            <div class="produto-img-placeholder"><i class="bi bi-image"></i></div>
            @endif
            @if(!$produto->ativo)
            <span class="produto-badge-indisponivel">Indisponível</span>
            @endif
        </div>
        <div class="produto-info">
            <h3 class="produto-nome">{{ $produto->nome }}</h3>
            @if($produto->descricao)
            <p class="produto-desc">{{ Str::limit($produto->descricao, 60) }}</p>
            @endif
            <div class="produto-preco-row">
                @if($produto->preco_promocional)
                <span class="produto-preco-de">R$ {{ number_format($produto->preco, 2, ',', '.') }}</span>
                <span class="produto-preco">R$ {{ number_format($produto->preco_promocional, 2, ',', '.') }}</span>
                @else
                <span class="produto-preco">R$ {{ number_format($produto->preco, 2, ',', '.') }}</span>
                @endif
            </div>
        </div>
    </a>
    @endforeach
</div>
{{ $produtos->appends(['q' => $q])->links('vendor.pagination.simple-bootstrap') }}
@else
<div class="estado-vazio">
    <i class="bi bi-search fs-1"></i>
    <p>Nenhum produto encontrado.</p>
    <a href="{{ route('cliente.loja', $loja->slug) }}" class="btn btn-primario mt-2">
        <i class="bi bi-house"></i> Ver cardápio completo
    </a>
</div>
@endif

<style>
.busca-topo { padding: 8px 12px 4px; background: var(--cor-card); position: sticky; top: var(--header-height); z-index: 50; }
.busca-form-bar { width: 100%; }
.busca-input-wrap { position: relative; }
.busca-icone { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--cor-texto-muted); }
.busca-input { width: 100%; padding: 10px 40px 10px 38px; border: 1.5px solid var(--cor-borda); border-radius: 12px; font-family: inherit; font-size: 0.95rem; background: var(--cor-fundo); color: var(--cor-texto); }
.busca-input:focus { outline: none; border-color: var(--cor-primaria); }
.busca-clear { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--cor-texto-muted); text-decoration: none; }
.busca-resultado-info { padding: 10px 16px; font-size: 0.85rem; color: var(--cor-texto-muted); }
</style>
@endsection
