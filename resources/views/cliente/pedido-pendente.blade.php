@extends('layouts.pwa')
@section('titulo', 'Aguardando Pagamento')

@section('conteudo')
<div class="resultado-container">
    <div class="resultado-icone pendente">
        <i class="bi bi-clock-fill"></i>
    </div>
    <h1 class="resultado-titulo">Pedido em Análise</h1>
    <p class="resultado-subtitulo">Seu pagamento está sendo processado. Você receberá uma notificação assim que for confirmado.</p>

    <div class="resultado-card">
        <div class="resultado-linha">
            <span>Pedido</span>
            <strong>#{{ $pedido->numero }}</strong>
        </div>
        <div class="resultado-linha">
            <span>Valor</span>
            <strong>R$ {{ number_format($pedido->total, 2, ',', '.') }}</strong>
        </div>
        <div class="resultado-linha">
            <span>Status</span>
            <strong class="text-warning">Em análise</strong>
        </div>
    </div>

    <div class="resultado-acoes">
        <a href="{{ route('cliente.pedidos.show', $pedido) }}" class="btn btn-primario w-100 mb-2">
            <i class="bi bi-bag"></i> Ver Pedido
        </a>
        <a href="{{ route('cliente.loja', $pedido->loja->slug) }}" class="btn btn-outline w-100">
            <i class="bi bi-house"></i> Voltar ao Cardápio
        </a>
    </div>
</div>
@endsection
