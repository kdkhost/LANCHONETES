@extends('layouts.admin')
@section('titulo', 'Pedidos')

@section('conteudo')
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <form method="GET" action="{{ route('admin.pedidos.index') }}" class="d-flex gap-2 flex-wrap align-items-center" style="flex:1">
        <input type="text" name="busca" value="{{ request('busca') }}" class="campo-input" placeholder="Nº pedido ou cliente..." style="width:200px">
        <select name="status" class="campo-input" style="width:auto">
            <option value="">Todos os status</option>
            @foreach(config('lanchonete.pedido.status') as $chave => $label)
            <option value="{{ $chave }}" {{ request('status') === $chave ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <input type="date" name="data" value="{{ request('data') }}" class="campo-input" style="width:auto">
        <button type="submit" class="btn btn-primary">Filtrar</button>
        @if(request()->hasAny(['busca','status','data']))
        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-secondary">Limpar</a>
        @endif
    </form>
    <a href="{{ route('admin.pedidos.kanban') }}" class="btn btn-outline-primary">
        <i class="bi bi-kanban"></i> Kanban
    </a>
</div>

<div class="card-admin">
    <div class="card-admin-body p-0">
        <table class="tabela-admin">
            <thead>
                <tr>
                    <th>Nº Pedido</th>
                    <th>Cliente</th>
                    <th>Itens</th>
                    <th>Total</th>
                    <th>Pagamento</th>
                    <th>Entrega</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th style="width:80px">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                @php
                    $cor = config("lanchonete.pedido.cores_status.{$pedido->status}", '#6c757d');
                    $lbl = config("lanchonete.pedido.status.{$pedido->status}", $pedido->status);
                @endphp
                <tr>
                    <td><strong class="text-primary" style="font-family:monospace">{{ $pedido->numero }}</strong></td>
                    <td>
                        <strong>{{ $pedido->usuario->nome }}</strong><br>
                        <small class="text-muted">{{ $pedido->usuario->telefone }}</small>
                    </td>
                    <td>{{ $pedido->itens->count() }} item(ns)</td>
                    <td><strong>R$ {{ number_format($pedido->total, 2, ',', '.') }}</strong></td>
                    <td>
                        <span class="badge {{ $pedido->pagamento?->status === 'aprovado' ? 'badge-success' : ($pedido->pagamento?->status === 'recusado' ? 'badge-danger' : 'badge-warning') }}">
                            {{ $pedido->pagamento?->metodo_label ?? '—' }}
                        </span>
                    </td>
                    <td>{{ $pedido->tipo_entrega === 'entrega' ? 'Entrega' : 'Retirada' }}</td>
                    <td>
                        <span class="badge-status" style="background:{{ $cor }}20;color:{{ $cor }};padding:4px 10px;border-radius:20px;font-size:.75rem;font-weight:700">
                            {{ $lbl }}
                        </span>
                    </td>
                    <td>
                        <small>{{ $pedido->created_at->format('d/m/Y') }}</small><br>
                        <small class="text-muted">{{ $pedido->created_at->format('H:i') }}</small>
                    </td>
                    <td>
                        <a href="{{ route('admin.pedidos.show', $pedido) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-bag-x fs-1 d-block mb-2"></i>
                        Nenhum pedido encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $pedidos->links('vendor.pagination.simple-bootstrap') }}
@endsection
