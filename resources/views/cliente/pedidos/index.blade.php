@extends('layouts.pwa')
@section('titulo', 'Meus Pedidos')

@section('conteudo')
<div class="page-header">
    <button onclick="history.back()" class="btn-voltar"><i class="bi bi-arrow-left"></i></button>
    <h1 class="page-title">Meus Pedidos</h1>
</div>

@if($pedidos->count())
<div class="pedidos-lista-cliente">
    @foreach($pedidos as $pedido)
    @php
        $cores  = config('lanchonete.pedido.cores_status');
        $labels = config('lanchonete.pedido.status');
        $cor    = $cores[$pedido->status] ?? '#6c757d';
    @endphp
    <a href="{{ route('cliente.pedidos.show', $pedido) }}" class="pedido-card-cliente">
        <div class="pedido-card-top">
            <div>
                <span class="pedido-card-numero">#{{ $pedido->numero }}</span>
                <span class="pedido-card-loja">{{ $pedido->loja->nome }}</span>
            </div>
            <span class="pedido-card-badge" style="background:{{ $cor }}20; color:{{ $cor }}">
                {{ $labels[$pedido->status] ?? $pedido->status }}
            </span>
        </div>
        <div class="pedido-card-itens">
            {{ $pedido->itens->pluck('produto_nome')->take(3)->join(', ') }}
            @if($pedido->itens->count() > 3) e mais {{ $pedido->itens->count() - 3 }} item(ns)@endif
        </div>
        <div class="pedido-card-footer">
            <span class="pedido-card-data">{{ $pedido->created_at->format('d/m/Y H:i') }}</span>
            <span class="pedido-card-total">R$ {{ number_format($pedido->total, 2, ',', '.') }}</span>
        </div>
    </a>
    @endforeach
</div>
{{ $pedidos->links('vendor.pagination.simple-bootstrap') }}
@else
<div class="empty-state py-5">
    <i class="bi bi-bag-x fs-1 text-muted d-block mb-3"></i>
    <p class="text-muted">Você ainda não fez nenhum pedido.</p>
    <a href="{{ route('cliente.home') }}" class="btn btn-primario mt-2">
        <i class="bi bi-shop"></i> Ver Restaurantes
    </a>
</div>
@endif

<style>
.pedidos-lista-cliente { padding: 8px 12px; }
.pedido-card-cliente {
    display: block; background: var(--cor-card); border-radius: 14px;
    padding: 14px; margin-bottom: 10px; box-shadow: var(--sombra-sm);
    text-decoration: none; color: var(--cor-texto); transition: transform var(--transition);
    border: 1px solid var(--cor-borda);
}
.pedido-card-cliente:active { transform: scale(.99); }
.pedido-card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; }
.pedido-card-numero { font-weight: 800; font-size: 0.88rem; color: var(--cor-primaria); font-family: monospace; display: block; }
.pedido-card-loja { font-size: 0.78rem; color: var(--cor-texto-muted); }
.pedido-card-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; white-space: nowrap; }
.pedido-card-itens { font-size: 0.85rem; color: var(--cor-texto-muted); margin-bottom: 8px; line-height: 1.4; }
.pedido-card-footer { display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem; }
.pedido-card-data { color: var(--cor-texto-muted); }
.pedido-card-total { font-weight: 800; color: var(--cor-texto); }
</style>
@endsection
