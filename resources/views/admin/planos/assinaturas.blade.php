@extends('layouts.admin')
@section('titulo', 'Minhas Assinaturas')

@section('conteudo')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="mb-1">📋 Histórico de Assinaturas</h2>
        <p class="text-muted mb-0">Visualize todas as suas assinaturas e pagamentos</p>
    </div>
    <a href="{{ route('admin.planos.upgrade') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nova Assinatura
    </a>
</div>

<div class="card-admin">
    <div class="card-admin-body">
        @if($assinaturas->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Plano</th>
                        <th>Período</th>
                        <th>Status</th>
                        <th>Data Início</th>
                        <th>Data Fim</th>
                        <th>Valor</th>
                        <th>Método</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assinaturas as $assinatura)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="plano-icon">
                                    @if($assinatura->plano->slug === 'gratuita')
                                        <i class="bi bi-gift"></i>
                                    @else
                                        <i class="bi bi-star"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $assinatura->plano->nome }}</div>
                                    <small class="text-muted">{{ $assinatura->plano->descricao }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">
                                {{ ucfirst($assinatura->periodo) }}
                            </span>
                        </td>
                        <td>
                            <span class="assinatura-status" style="background-color: {{ $assinatura->status_cor }}; color: white;">
                                {{ $assinatura->status_label }}
                            </span>
                        </td>
                        <td>{{ $assinatura->data_inicio->format('d/m/Y') }}</td>
                        <td>
                            @if($assinatura->data_fim)
                                {{ $assinatura->data_fem->format('d/m/Y') }}
                                @if($assinatura->data_fem->isPast())
                                <br><small class="text-danger">Expirada</small>
                                @elseif($assinatura->data_fem->diffInDays(now()) <= 7)
                                <br><small class="text-warning">{{ $assinatura->dias_restantes_assinatura }} dias restantes</small>
                                @endif
                            @else
                                <span class="text-muted">Indeterminado</span>
                            @endif
                        </td>
                        <td>
                            @if($assinatura->valor_pago > 0)
                                <strong>R$ {{ number_format($assinatura->valor_pago, 2, ',', '.') }}</strong>
                            @else
                                <span class="text-muted">Grátis</span>
                            @endif
                        </td>
                        <td>
                            @switch($assinatura->metodo_pagamento)
                                @case('mercadopago')
                                    <span class="badge bg-primary">MercadoPago</span>
                                    @break
                                @case('manual')
                                    <span class="badge bg-secondary">Manual</span>
                                    @break
                                @default
                                    <span class="badge bg-light text-dark">-</span>
                            @endswitch
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @if($assinatura->gateway_id && $assinatura->metodo_pagamento === 'mercadopago')
                                <button class="btn btn-outline-info" onclick="verDetalhesPagamento('{{ $assinatura->gateway_id }}')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @endif
                                
                                @if($assinatura->estaAtiva())
                                <form method="POST" action="{{ route('admin.planos.cancelar', $assinatura) }}" onsubmit="return confirm('Tem certeza que deseja cancelar esta assinatura?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Mostrando {{ $assinaturas->firstItem() }} a {{ $assinaturas->lastItem() }} de {{ $assinaturas->total() }} assinaturas
            </div>
            {{ $assinaturas->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h4 class="mt-3">Nenhuma assinatura encontrada</h4>
            <p class="text-muted">Você ainda não possui assinaturas registradas.</p>
            <a href="{{ route('admin.planos.upgrade') }}" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle"></i> Assinar um Plano
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Modal de detalhes do pagamento -->
<div class="modal fade" id="detalhesPagamentoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Pagamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detalhesPagamentoContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.plano-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.assinatura-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script>
function verDetalhesPagamento(paymentId) {
    const modal = new bootstrap.Modal(document.getElementById('detalhesPagamentoModal'));
    const content = document.getElementById('detalhesPagamentoContent');
    
    // Mostrar loading
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-2">Buscando detalhes do pagamento...</p>
        </div>
    `;
    
    modal.show();
    
    // Buscar detalhes do pagamento
    fetch(`/admin/planos/pagamento/${paymentId}/detalhes`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = `
                    <div class="row">
                        <div class="col-6">
                            <strong>ID do Pagamento:</strong>
                        </div>
                        <div class="col-6">
                            ${data.payment.id}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <strong>Status:</strong>
                        </div>
                        <div class="col-6">
                            <span class="badge bg-${data.payment.status === 'approved' ? 'success' : 'warning'}">
                                ${data.payment.status}
                            </span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <strong>Valor:</strong>
                        </div>
                        <div class="col-6">
                            R$ ${parseFloat(data.payment.amount).toFixed(2)}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <strong>Método:</strong>
                        </div>
                        <div class="col-6">
                            ${data.payment.payment_method_id || '-'}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <strong>Data Criação:</strong>
                        </div>
                        <div class="col-6">
                            ${new Date(data.payment.date_created).toLocaleString('pt-BR')}
                        </div>
                    </div>
                    @if(data.payment.date_approved)
                    <div class="row mt-2">
                        <div class="col-6">
                            <strong>Data Aprovação:</strong>
                        </div>
                        <div class="col-6">
                            ${new Date(data.payment.date_approved).toLocaleString('pt-BR')}
                        </div>
                    </div>
                    @endif
                    <div class="row mt-2">
                        <div class="col-6">
                            <strong>Email do Pagador:</strong>
                        </div>
                        <div class="col-6">
                            ${data.payment.payer_email}
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Erro ao carregar detalhes: ${data.error}
                    </div>
                `;
            }
        })
        .catch(error => {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Erro ao carregar detalhes do pagamento.
                </div>
            `;
        });
}
</script>
@endpush
