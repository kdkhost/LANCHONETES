@extends('layouts.pwa')
@section('titulo', 'Histórico de Entregas')

@section('conteudo')
<div class="page-header">
    <a href="{{ route('entregador.dashboard') }}" class="btn-voltar"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">Histórico</h1>
</div>

<div class="historico-container">
    @forelse($entregas as $entrega)
    @php
        $statusCor = ['entregue' => '#28a745', 'cancelado' => '#dc3545', 'em_rota' => '#ffc107'][$entrega->status] ?? '#6c757d';
        $statusLabel = ['entregue' => 'Entregue', 'cancelado' => 'Cancelado', 'em_rota' => 'Em rota', 'aceito' => 'Aceito', 'coletado' => 'Coletado'][$entrega->status] ?? $entrega->status;
    @endphp
    <div class="historico-card">
        <div class="historico-header">
            <div>
                <strong class="historico-numero">#{{ $entrega->pedido->numero }}</strong>
                <span class="historico-loja">{{ $entrega->pedido->loja->nome }}</span>
            </div>
            <span class="historico-status" style="background:{{ $statusCor }}20;color:{{ $statusCor }}">
                {{ $statusLabel }}
            </span>
        </div>
        <div class="historico-meta">
            <span><i class="bi bi-person"></i> {{ $entrega->pedido->usuario->nome }}</span>
            <span><i class="bi bi-calendar3"></i> {{ $entrega->created_at->format('d/m/Y') }}</span>
        </div>
        @if($entrega->pedido->endereco_bairro)
        <div class="historico-endereco">
            <i class="bi bi-geo-alt"></i>
            {{ $entrega->pedido->endereco_logradouro }}, {{ $entrega->pedido->endereco_numero }} — {{ $entrega->pedido->endereco_bairro }}
        </div>
        @endif
        <div class="historico-footer">
            <span class="historico-taxa">+ R$ {{ number_format($entrega->taxa_entrega ?? 0, 2, ',', '.') }}</span>
            @if($entrega->entregue_em)
            <small class="text-muted">Entregue às {{ $entrega->entregue_em->format('H:i') }}</small>
            @endif
        </div>
    </div>
    @empty
    <div class="estado-vazio">
        <i class="bi bi-bicycle fs-1"></i>
        <p>Nenhuma entrega realizada ainda.</p>
    </div>
    @endforelse
</div>
{{ $entregas->links('vendor.pagination.simple-bootstrap') }}

<style>
.historico-container { padding: 8px 12px 80px; }
.historico-card { background: var(--cor-card); border-radius: 14px; padding: 14px; margin-bottom: 10px; box-shadow: var(--sombra-sm); }
.historico-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 8px; }
.historico-numero { font-size: 0.9rem; font-weight: 800; font-family: monospace; display: block; }
.historico-loja { font-size: 0.78rem; color: var(--cor-texto-muted); }
.historico-status { padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
.historico-meta { display: flex; gap: 12px; font-size: 0.78rem; color: var(--cor-texto-muted); margin-bottom: 6px; }
.historico-endereco { font-size: 0.8rem; color: var(--cor-texto-muted); margin-bottom: 8px; }
.historico-footer { display: flex; align-items: center; justify-content: space-between; }
.historico-taxa { font-size: 0.95rem; font-weight: 800; color: var(--cor-sucesso); }
</style>
@endsection
