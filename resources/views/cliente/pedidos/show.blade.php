@extends('layouts.pwa')
@section('titulo', 'Pedido #' . $pedido->numero)

@section('conteudo')
<div class="page-header">
    <button onclick="history.back()" class="btn-voltar"><i class="bi bi-arrow-left"></i></button>
    <h1 class="page-title">Pedido #{{ $pedido->numero }}</h1>
</div>

{{-- Status Timeline --}}
<div class="status-timeline">
    @php
        $statusOrdem = ['aguardando_pagamento','pagamento_aprovado','confirmado','em_preparo','pronto','saiu_para_entrega','entregue'];
        $statusAtual = array_search($pedido->status, $statusOrdem);
        $cores       = config('lanchonete.pedido.cores_status');
        $labels      = config('lanchonete.pedido.status');
    @endphp
    <div class="status-atual-badge" style="background:{{ $cores[$pedido->status] ?? '#6c757d' }}20; color:{{ $cores[$pedido->status] ?? '#6c757d' }}; border:1.5px solid {{ $cores[$pedido->status] ?? '#6c757d' }}">
        <span class="pulse-dot-sm" style="background:{{ $cores[$pedido->status] ?? '#6c757d' }}"></span>
        {{ $labels[$pedido->status] ?? $pedido->status }}
    </div>
    @if($pedido->tipo_entrega === 'entrega' && $pedido->link_rastreamento && in_array($pedido->status, ['saiu_para_entrega']))
    <a href="{{ $pedido->link_rastreamento }}" target="_blank" class="btn btn-sm btn-outline mt-2">
        <i class="bi bi-geo-alt-fill"></i> Rastrear ao Vivo
    </a>
    @endif
</div>

{{-- Detalhes do Pedido --}}
<div class="pedido-show-card">
    <h3 class="pedido-show-titulo"><i class="bi bi-bag"></i> Itens do Pedido</h3>
    @foreach($pedido->itens as $item)
    <div class="pedido-item-linha">
        <div class="pedido-item-info">
            <span class="pedido-item-qtd">{{ $item->quantidade }}x</span>
            <div>
                <span class="pedido-item-nome">{{ $item->produto_nome }}</span>
                @foreach($item->adicionais as $adic)
                <small class="pedido-item-adicional">+ {{ $adic->adicional_nome }}</small>
                @endforeach
                @if($item->observacoes)
                <small class="pedido-item-obs"><i class="bi bi-chat-text"></i> {{ $item->observacoes }}</small>
                @endif
            </div>
        </div>
        <span class="pedido-item-preco">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</span>
    </div>
    @endforeach
</div>

{{-- Resumo de Valores --}}
<div class="pedido-show-card">
    <h3 class="pedido-show-titulo"><i class="bi bi-receipt"></i> Resumo</h3>
    <div class="resumo-linha"><span>Subtotal</span><span>R$ {{ number_format($pedido->subtotal, 2, ',', '.') }}</span></div>
    @if($pedido->taxa_entrega > 0)
    <div class="resumo-linha"><span>Taxa de Entrega</span><span>R$ {{ number_format($pedido->taxa_entrega, 2, ',', '.') }}</span></div>
    @endif
    @if($pedido->desconto > 0)
    <div class="resumo-linha text-success"><span>Desconto ({{ $pedido->cupom_codigo }})</span><span>-R$ {{ number_format($pedido->desconto, 2, ',', '.') }}</span></div>
    @endif
    <div class="resumo-linha resumo-total"><span>Total</span><span>R$ {{ number_format($pedido->total, 2, ',', '.') }}</span></div>
</div>

{{-- Pagamento --}}
@if($pedido->pagamento)
<div class="pedido-show-card">
    <h3 class="pedido-show-titulo"><i class="bi bi-credit-card"></i> Pagamento</h3>
    <div class="resumo-linha"><span>Método</span><strong>{{ $pedido->pagamento->metodo_label }}</strong></div>
    <div class="resumo-linha"><span>Status</span>
        <span style="color:{{ $pedido->pagamento->status === 'aprovado' ? '#28a745' : ($pedido->pagamento->status === 'recusado' ? '#dc3545' : '#ffc107') }}; font-weight:700">
            {{ $pedido->pagamento->status_label }}
        </span>
    </div>
    @if($pedido->pagamento->parcelas > 1)
    <div class="resumo-linha"><span>Parcelas</span><strong>{{ $pedido->pagamento->parcelas }}x</strong></div>
    @endif
</div>
@endif

{{-- Endereço de Entrega --}}
@if($pedido->tipo_entrega === 'entrega')
<div class="pedido-show-card">
    <h3 class="pedido-show-titulo"><i class="bi bi-geo-alt"></i> Endereço de Entrega</h3>
    <p class="endereco-texto">
        {{ $pedido->endereco_logradouro }}, {{ $pedido->endereco_numero }}
        @if($pedido->endereco_complemento) — {{ $pedido->endereco_complemento }}@endif
        <br>{{ $pedido->endereco_bairro }}, {{ $pedido->endereco_cidade }}/{{ $pedido->endereco_estado }}
        <br>CEP: {{ $pedido->endereco_cep }}
    </p>
</div>
@endif

{{-- Observações --}}
@if($pedido->observacoes)
<div class="pedido-show-card">
    <h3 class="pedido-show-titulo"><i class="bi bi-chat-text"></i> Observações</h3>
    <p class="text-muted">{{ $pedido->observacoes }}</p>
</div>
@endif

{{-- Ações --}}
<div class="pedido-acoes">
    @if($pedido->podeCancelar())
    <button onclick="cancelarPedido({{ $pedido->id }})" class="btn btn-outline-danger w-100 mb-2">
        <i class="bi bi-x-circle"></i> Cancelar Pedido
    </button>
    @endif
    @if($pedido->status === 'entregue' && !$pedido->avaliacao)
    <a href="{{ route('cliente.avaliar', $pedido) }}" class="btn btn-primario w-100 mb-2">
        <i class="bi bi-star"></i> Avaliar Pedido
    </a>
    @endif
    <a href="{{ route('cliente.loja', $pedido->loja->slug) }}" class="btn btn-outline w-100">
        <i class="bi bi-arrow-repeat"></i> Pedir Novamente
    </a>
</div>

<style>
.status-timeline { padding: 16px; text-align: center; }
.status-atual-badge {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; border-radius: 20px; font-weight: 700; font-size: 1rem;
}
.pulse-dot-sm { width: 8px; height: 8px; border-radius: 50%; animation: pulse 1.5s infinite; }
@keyframes pulse { 0%,100%{transform:scale(1);opacity:1} 50%{transform:scale(1.5);opacity:.5} }
.pedido-show-card {
    background: var(--cor-card); margin: 8px; border-radius: 14px;
    padding: 14px 16px; box-shadow: var(--sombra-sm);
}
.pedido-show-titulo {
    font-size: 0.9rem; font-weight: 800; margin: 0 0 10px;
    display: flex; align-items: center; gap: 6px;
}
.pedido-show-titulo i { color: var(--cor-primaria); }
.pedido-item-linha {
    display: flex; align-items: flex-start; justify-content: space-between;
    padding: 8px 0; border-bottom: 1px solid var(--cor-borda); gap: 8px;
}
.pedido-item-linha:last-child { border: none; }
.pedido-item-info { display: flex; align-items: flex-start; gap: 8px; flex: 1; }
.pedido-item-qtd { font-weight: 800; color: var(--cor-primaria); min-width: 24px; }
.pedido-item-nome { font-weight: 700; font-size: 0.9rem; display: block; }
.pedido-item-adicional { color: var(--cor-texto-muted); font-size: 0.78rem; display: block; }
.pedido-item-obs { color: var(--cor-info, #17a2b8); font-size: 0.78rem; display: block; }
.pedido-item-preco { font-weight: 800; white-space: nowrap; font-size: 0.9rem; }
.resumo-linha { display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.88rem; border-bottom: 1px solid var(--cor-borda); }
.resumo-linha:last-child { border: none; }
.resumo-total { font-size: 1rem; font-weight: 800; color: var(--cor-primaria); }
.text-success { color: #28a745; }
.endereco-texto { font-size: 0.9rem; line-height: 1.6; margin: 0; color: var(--cor-texto); }
.pedido-acoes { padding: 8px 12px 20px; }
.btn-outline-danger { background: transparent; color: var(--cor-erro); border: 1.5px solid var(--cor-erro); }
.mb-2 { margin-bottom: 8px; }
</style>
@endsection

@push('scripts')
<script>
async function cancelarPedido(pedidoId) {
    const motivo = prompt('Motivo do cancelamento (opcional):');
    if (motivo === null) return;
    const res  = await fetch(`/pedidos/${pedidoId}/cancelar`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ motivo })
    });
    const data = await res.json();
    if (data.sucesso) { mostrarToast('Pedido cancelado.', 'info'); setTimeout(() => location.reload(), 800); }
    else mostrarToast(data.erro || 'Não foi possível cancelar.', 'erro');
}

// Polling de status
@if(!in_array($pedido->status, ['entregue', 'cancelado', 'recusado']))
setInterval(() => {
    fetch('/pedidos/{{ $pedido->id }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(() => {})
        .catch(() => {});
}, 20000);
@endif
</script>
@endpush
