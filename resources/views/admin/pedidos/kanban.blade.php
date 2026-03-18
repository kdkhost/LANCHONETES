@extends('layouts.admin')
@section('titulo', 'Kanban de Pedidos')

@push('styles')
<style>
.kanban-board { display:flex; gap:12px; overflow-x:auto; padding-bottom:16px; min-height:calc(100vh - 160px); align-items:flex-start; }
.kanban-coluna { min-width:260px; max-width:280px; flex-shrink:0; display:flex; flex-direction:column; }
.kanban-col-header { padding:10px 14px; border-radius:10px 10px 0 0; font-weight:800; font-size:.85rem; display:flex; align-items:center; justify-content:space-between; color:#fff; }
.kanban-col-body { flex:1; padding:8px; min-height:80px; display:flex; flex-direction:column; gap:8px; background:rgba(0,0,0,.04); border-radius:0 0 10px 10px; border:1px solid #e4e6ea; border-top:none; }
.kanban-col-body.drag-over { background:rgba(255,107,53,.08); border-color:#FF6B35; }
.kanban-card { background:#fff; border-radius:10px; padding:12px; box-shadow:0 2px 8px rgba(0,0,0,.08); cursor:pointer; transition:transform .15s; border-left:3px solid transparent; user-select:none; }
.kanban-card:hover { transform:translateY(-2px); box-shadow:0 4px 16px rgba(0,0,0,.12); }
.kanban-card.dragging { opacity:.5; transform:scale(.97); }
.kanban-num { font-size:.72rem; font-weight:800; font-family:monospace; }
.kanban-cliente { font-weight:700; font-size:.88rem; margin:4px 0 2px; }
.kanban-meta { display:flex; justify-content:space-between; font-size:.75rem; color:#6c757d; }
.kanban-total { font-weight:800; font-size:.88rem; color:#FF6B35; }
.kanban-contagem { background:rgba(255,255,255,.25); padding:2px 8px; border-radius:10px; font-size:.75rem; font-weight:700; }
.kanban-vazio { text-align:center; padding:20px 10px; color:#aaa; font-size:.8rem; }
.kanban-vazio i { font-size:1.8rem; display:block; margin-bottom:6px; opacity:.4; }
</style>
@endpush

@section('conteudo')
<div class="d-flex align-items-center justify-content-between mb-3">
    <p class="text-muted mb-0 small">Arraste os cartões entre colunas para atualizar o status dos pedidos.</p>
    <a href="{{ route('admin.pedidos.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-list-ul"></i> Ver Lista
    </a>
</div>

<div class="kanban-board" id="kanbanBoard">
    @php
        $colunasConfig = [
            'confirmado'       => ['label' => 'Confirmado',     'cor' => '#007BFF'],
            'em_preparo'       => ['label' => 'Em Preparo',     'cor' => '#6F42C1'],
            'pronto'           => ['label' => 'Pronto',         'cor' => '#20C997'],
            'saiu_para_entrega'=> ['label' => 'Saiu p/ Entrega','cor' => '#FD7E14'],
            'entregue'         => ['label' => 'Entregue',       'cor' => '#28A745'],
        ];
    @endphp

    @foreach($colunasConfig as $status => $info)
    @php $colPedidos = $colunas[$status] ?? collect(); @endphp
    <div class="kanban-coluna">
        <div class="kanban-col-header" style="background:{{ $info['cor'] }}">
            <span>{{ $info['label'] }}</span>
            <span class="kanban-contagem">{{ $colPedidos->count() }}</span>
        </div>
        <div class="kanban-col-body" data-status="{{ $status }}" id="kanban-col-{{ $status }}">
            @forelse($colPedidos as $pedido)
            <div class="kanban-card" draggable="true"
                data-pedido-id="{{ $pedido->id }}"
                style="border-left-color:{{ $info['cor'] }}"
                onclick="window.location='{{ route('admin.pedidos.show', $pedido) }}'">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="kanban-num" style="color:{{ $info['cor'] }}">#{{ $pedido->numero }}</span>
                    <span class="kanban-total">R$ {{ number_format($pedido->total, 2, ',', '.') }}</span>
                </div>
                <div class="kanban-cliente">{{ Str::limit($pedido->usuario->nome, 22) }}</div>
                <div class="kanban-meta">
                    <span>{{ $pedido->itens->count() }} item(ns)</span>
                    <span>{{ $pedido->created_at->format('H:i') }}</span>
                </div>
                @if($pedido->tipo_entrega === 'entrega' && $pedido->endereco_bairro)
                <div class="kanban-meta mt-1">
                    <span><i class="bi bi-geo-alt"></i> {{ Str::limit($pedido->endereco_bairro, 20) }}</span>
                </div>
                @endif
            </div>
            @empty
            <div class="kanban-vazio">
                <i class="bi bi-inbox"></i>
                Nenhum pedido
            </div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', inicializarKanban);

// Auto-refresh a cada 30 segundos
setInterval(() => location.reload(), 30000);
</script>
@endpush
