@extends('layouts.admin')
@section('titulo', 'Meu Plano')

@section('conteudo')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="mb-1">💳 Meu Plano</h2>
        <p class="text-muted mb-0">Gerencie sua assinatura e visualize os benefícios</p>
    </div>
    <a href="{{ route('admin.planos.upgrade') }}" class="btn btn-primary">
        <i class="bi bi-arrow-up-circle"></i> Fazer Upgrade
    </a>
</div>

{{-- Status atual --}}
<div class="card-admin mb-4">
    <div class="card-admin-header">
        <h3><i class="bi bi-info-circle"></i> Status da Assinatura</h3>
    </div>
    <div class="card-admin-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="plano-status-icon {{ $loja->estaEmTrial() ? 'trial' : ($assinatura?->estaAtiva() ? 'ativo' : 'bloqueado') }}">
                        @if($loja->estaEmTrial())
                            <i class="bi bi-clock-history"></i>
                        @elseif($assinatura?->estaAtiva())
                            <i class="bi bi-check-circle"></i>
                        @else
                            <i class="bi bi-x-circle"></i>
                        @endif
                    </div>
                    <div>
                        <div class="h4 mb-1">{{ $loja->status_plano }}</div>
                        <div class="text-muted">
                            @if($loja->estaEmTrial())
                                Período de teste gratuito de 14 dias
                            @elseif($assinatura?->estaAtiva())
                                Assinatura {{ $assinatura->plano->nome }} - {{ ucfirst($assinatura->periodo) }}
                            @else
                                Sua assinatura expirou
                            @endif
                        </div>
                    </div>
                </div>

                @if($loja->estaEmTrial())
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Atenção:</strong> Seu trial expira em <strong>{{ $loja->dias_restantes_trial }} dias</strong>. 
                    Após esse período, algumas funcionalidades serão bloqueadas até que você assine um plano.
                </div>
                @elseif($loja->estaBloqueadaPorPlano())
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-x-circle"></i>
                    <strong>Sua loja está bloqueada!</strong> Para continuar vendendo e gerenciando produtos, 
                    <a href="{{ route('admin.planos.upgrade') }}" class="alert-link">assine um plano agora</a>.
                </div>
                @endif
            </div>
            <div class="col-md-4 text-center">
                @if($assinatura?->plano)
                <div class="plano-card-compact">
                    <div class="plano-nome">{{ $assinatura->plano->nome }}</div>
                    <div class="plano-preco">{{ $assinatura->plano->getPrecoFormatado($assinatura->periodo) }}</div>
                    <div class="plano-periodo">{{ ucfirst($assinatura->periodo) }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Funcionalidades disponíveis --}}
<div class="card-admin mb-4">
    <div class="card-admin-header">
        <h3><i class="bi bi-check2-square"></i> Funcionalidades Disponíveis</h3>
    </div>
    <div class="card-admin-body">
        <div class="row">
            @php
                $funcionalidades = [
                    'produtos_ilimitados' => ['Produtos Ilimitados', 'bi-box-seam', $loja->podeCriarProdutos()],
                    'pagamento_online' => ['Pagamento Online', 'bi-credit-card', $loja->podeConfigurarPagamento()],
                    'relatorios_completos' => ['Relatórios Completos', 'bi-graph-up', true],
                    'estatisticas_visitas' => ['Estatísticas de Visitas', 'bi-eye', true],
                    'notificacoes_whatsapp' => ['Notificações WhatsApp', 'bi-whatsapp', true],
                    'cozinha_app' => ['App Cozinha', 'bi-egg-fried', true],
                    'nfe_integracao' => ['Integração NFe', 'bi-receipt', true],
                    'lgpd_compliance' => ['LGPD Compliance', 'bi-shield-check', true],
                    'popups_marketing' => ['Pop-ups Marketing', 'bi-window', true],
                    'suporte_prioritario' => ['Suporte Prioritário', 'bi-headset', $assinatura?->plano?->temRecurso('suporte_prioritario') ?? false],
                    'dominio_personalizado' => ['Domínio Personalizado', 'bi-globe', $assinatura?->plano?->temRecurso('dominio_personalizado') ?? false],
                    'api_acesso' => ['Acesso API', 'bi-code-slash', $assinatura?->plano?->temRecurso('api_acesso') ?? false],
                ];
            @endphp

            @foreach($funcionalidades as $chave => $info)
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="funcionalidade-item {{ $info[2] ? 'disponivel' : 'bloqueado' }}">
                    <div class="funcionalidade-icon">
                        <i class="bi {{ $info[1] }}"></i>
                    </div>
                    <div class="funcionalidade-texto">
                        <div class="funcionalidade-nome">{{ $info[0] }}</div>
                        <div class="funcionalidade-status">
                            @if($info[2])
                                <span class="text-success"><i class="bi bi-check-circle"></i> Disponível</span>
                            @else
                                <span class="text-muted"><i class="bi bi-x-circle"></i> Bloqueado</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Histórico de assinaturas --}}
<div class="card-admin">
    <div class="card-admin-header">
        <h3><i class="bi bi-clock-history"></i> Histórico de Assinaturas</h3>
        <a href="{{ route('admin.planos.assinaturas') }}" class="btn btn-sm btn-outline-primary">
            Ver Todas
        </a>
    </div>
    <div class="card-admin-body">
        @if($assinatura)
        <div class="assinatura-item">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="assinatura-plano">{{ $assinatura->plano->nome }}</div>
                    <div class="assinatura-periodo">{{ ucfirst($assinatura->periodo) }}</div>
                </div>
                <div class="col-md-2">
                    <span class="assinatura-status" style="background-color: {{ $assinatura->status_cor }}; color: white;">
                        {{ $assinatura->status_label }}
                    </span>
                </div>
                <div class="col-md-3">
                    <div class="assinatura-datas">
                        <div><small>Início:</small> {{ $assinatura->data_inicio->format('d/m/Y') }}</div>
                        @if($assinatura->data_fim)
                        <div><small>Fim:</small> {{ $assinatura->data_fem->format('d/m/Y') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="assinatura-valor">
                        {{ 'R$ ' . number_format($assinatura->valor_pago, 2, ',', '.') }}
                    </div>
                </div>
                <div class="col-md-2 text-end">
                    @if($assinatura->estaAtiva())
                    <form method="POST" action="{{ route('admin.planos.cancelar', $assinatura) }}" onsubmit="return confirm('Tem certeza que deseja cancelar sua assinatura?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @else
        <p class="text-muted text-center py-4">Nenhuma assinatura encontrada.</p>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.plano-status-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.plano-status-icon.trial { background: linear-gradient(135deg, #28a745, #20c997); }
.plano-status-icon.ativo { background: linear-gradient(135deg, #007bff, #6610f2); }
.plano-status-icon.bloqueado { background: linear-gradient(135deg, #dc3545, #fd7e14); }

.plano-card-compact {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
}

.plano-nome { font-weight: 700; font-size: 1.1rem; margin-bottom: 8px; }
.plano-preco { font-size: 1.8rem; font-weight: 800; margin-bottom: 4px; }
.plano-periodo { opacity: 0.9; font-size: 0.9rem; }

.funcionalidade-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.2s;
}

.funcionalidade-item.disponivel { background: #f8f9fa; border-color: #28a745; }
.funcionalidade-item.bloqueado { background: #f8f9fa; border-color: #dc3545; opacity: 0.7; }

.funcionalidade-icon { font-size: 20px; color: #6c757d; }
.funcionalidade-item.disponivel .funcionalidade-icon { color: #28a745; }
.funcionalidade-item.bloqueado .funcionalidade-icon { color: #dc3545; }

.funcionalidade-nome { font-weight: 600; font-size: 0.95rem; }
.funcionalidade-status { font-size: 0.85rem; }

.assinatura-item {
    padding: 16px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 12px;
}

.assinatura-plano { font-weight: 700; }
.assinatura-periodo { color: #6c757d; font-size: 0.9rem; }
.assinatura-status { padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
.assinatura-datas { font-size: 0.9rem; }
.assinatura-valor { font-weight: 700; font-size: 1.1rem; color: #28a745; }
</style>
@endpush
