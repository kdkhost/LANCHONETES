@php
    $min   = $pedido->created_at->diffInMinutes(now());
    $cls   = $min < 15 ? 'ok' : ($min < 25 ? 'alerta' : 'urgente');
    $lbl   = $min < 1 ? 'agora' : ($min < 60 ? $min.'min' : floor($min/60).'h'.($min%60 > 0 ? $min%60 .'m' : ''));
    $urg   = $min >= 25 ? ' urgente' : '';
    $tipo  = $pedido->tipo_entrega === 'retirada';
    $btnCl = $pedido->status === 'confirmado' ? 'preparar' : 'pronto';
    $btnTx = $pedido->status === 'confirmado' ? '<i class="bi bi-fire"></i> Iniciar Preparo' : '<i class="bi bi-check-circle-fill"></i> Marcar como Pronto';
@endphp
<div class="card-pedido{{ $urg }}" id="card-{{ $pedido->id }}" data-id="{{ $pedido->id }}" data-status="{{ $pedido->status }}">
    <div class="card-header">
        <span class="card-numero">{{ $pedido->numero }}</span>
        <span class="card-tempo {{ $cls }}">{{ $lbl }}</span>
    </div>
    <div class="card-cliente">
        <i class="bi bi-person-fill"></i> {{ $pedido->usuario->nome }}
        <span class="card-tipo {{ $tipo ? 'retirada' : 'entrega' }}">
            {{ $tipo ? '🏪 Retirada' : '🛵 Entrega' }}
        </span>
    </div>
    <div class="itens-lista">
        @foreach($pedido->itens as $item)
        <div class="item-linha">
            <span class="item-qtd">{{ $item->quantidade }}x</span>
            <div>
                <div class="item-nome">{{ $item->produto_nome }}</div>
                @if($item->observacoes)
                <div class="item-obs">⚠️ {{ $item->observacoes }}</div>
                @endif
                @if($item->adicionais->isNotEmpty())
                <div class="item-adicionais">
                    + {{ $item->adicionais->map(fn($a) => $a->quantidade.'x '.$a->adicional_nome)->join(', ') }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @if($pedido->observacoes)
    <div class="card-obs-geral">📝 {{ $pedido->observacoes }}</div>
    @endif
    <button class="btn-avancar {{ $btnCl }}" onclick="avancarStatus({{ $pedido->id }}, this)">
        {!! $btnTx !!}
    </button>
</div>
