@extends('layouts.admin')
@section('titulo', 'Upgrade de Plano')

@section('conteudo')
<div class="text-center mb-5">
    <h2 class="mb-2">🚀 Escolha seu Plano</h2>
    <p class="text-muted">Desbloqueie todos os recursos e leve sua loja ao próximo nível</p>
</div>

@if(session('alerta'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i> {{ session('alerta') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row justify-content-center">
    @foreach($planos as $plano)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="plano-card {{ $plano->destaque ? 'destaque' : '' }}">
            @if($plano->destaque)
            <div class="plano-badge">Mais Popular</div>
            @endif
            
            <div class="plano-header">
                <h3 class="plano-nome">{{ $plano->nome }}</h3>
                <div class="plano-preco">
                    <span class="preco-moeda">R$</span>
                    <span class="preco-valor">{{ number_format($plano->preco_mensal, 0, ',', '.') }}</span>
                    <span class="preco-periodo">/mês</span>
                </div>
                @if($plano->preco_anual)
                <div class="plano-preco-anual">
                    <span class="economia">Economia de R$ {{ number_format($plano->economia_anual, 0, ',', '.') }}/ano</span>
                    <div>
                        <span class="preco-moeda">R$</span>
                        <span class="preco-valor">{{ number_format($plano->preco_anual, 0, ',', '.') }}</span>
                        <span class="preco-periodo">/ano</span>
                    </div>
                </div>
                @endif
                <p class="plano-descricao">{{ $plano->descricao }}</p>
            </div>

            <div class="plano-recursos">
                @foreach($plano->recursos as $recurso => $ativo)
                @if($ativo)
                <div class="recurso-item">
                    <i class="bi bi-check-circle"></i>
                    <span>{{ $this->getRecursoNome($recurso) }}</span>
                </div>
                @endif
                @endforeach
            </div>

            <div class="plano-action">
                @if($plano->slug === 'gratuita')
                <button class="btn btn-outline-secondary w-100" disabled>
                    <i class="bi bi-check"></i> Plano Atual
                </button>
                @else
                <form method="POST" action="{{ route('admin.planos.checkout', $plano) }}">
                    @csrf
                    <input type="hidden" name="periodo" value="mensal">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-right"></i> Assinar Agora
                    </button>
                </form>
                
                @if($plano->preco_anual)
                <form method="POST" action="{{ route('admin.planos.checkout', $plano) }}" class="mt-2">
                    @csrf
                    <input type="hidden" name="periodo" value="anual">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-arrow-right"></i> Assinar Anual
                    </button>
                </form>
                @endif
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Comparação de planos --}}
<div class="card-admin mt-5">
    <div class="card-admin-header">
        <h3><i class="bi bi-columns-gap"></i> Comparação de Planos</h3>
    </div>
    <div class="card-admin-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Funcionalidade</th>
                        @foreach($planos as $plano)
                        <th class="text-center">{{ $plano->nome }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $todosRecursos = [];
                        foreach($planos as $plano) {
                            $todosRecursos = array_merge($todosRecursos, array_keys($plano->recursos));
                        }
                        $todosRecursos = array_unique($todosRecursos);
                    @endphp
                    
                    @foreach($todosRecursos as $recurso)
                    <tr>
                        <td>{{ $this->getRecursoNome($recurso) }}</td>
                        @foreach($planos as $plano)
                        <td class="text-center">
                            @if($plano->recursos[$recurso] ?? false)
                            <i class="bi bi-check-circle text-success"></i>
                            @else
                            <i class="bi bi-x-circle text-muted"></i>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('php')
    @php
        function getRecursoNome($recurso) {
            $nomes = [
                'produtos_ilimitados' => 'Produtos Ilimitados',
                'pedidos_ilimitados' => 'Pedidos Ilimitados',
                'pagamento_online' => 'Pagamento Online',
                'relatorios_completos' => 'Relatórios Completos',
                'estatisticas_visitas' => 'Estatísticas de Visitas',
                'notificacoes_whatsapp' => 'Notificações WhatsApp',
                'cozinha_app' => 'App Cozinha',
                'nfe_integracao' => 'Integração NFe',
                'lgpd_compliance' => 'LGPD Compliance',
                'popups_marketing' => 'Pop-ups Marketing',
                'suporte_prioritario' => 'Suporte Prioritário',
                'dominio_personalizado' => 'Domínio Personalizado',
                'api_acesso' => 'Acesso API',
            ];
            return $nomes[$recurso] ?? $recurso;
        }
    @endphp
@endsection
@endsection

@push('styles')
<style>
.plano-card {
    background: white;
    border-radius: 16px;
    padding: 32px 24px;
    border: 2px solid #e9ecef;
    position: relative;
    height: 100%;
    transition: all 0.3s ease;
}

.plano-card.destaque {
    border-color: #007bff;
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0,123,255,0.15);
}

.plano-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #007bff, #6610f2);
    color: white;
    padding: 6px 20px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.plano-header {
    text-align: center;
    margin-bottom: 32px;
}

.plano-nome {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 16px;
    color: #2c3e50;
}

.plano-preco {
    margin-bottom: 16px;
}

.preco-moeda { font-size: 1.2rem; font-weight: 600; }
.preco-valor { font-size: 3rem; font-weight: 800; }
.preco-periodo { font-size: 1rem; color: #6c757d; }

.plano-preco-anual {
    margin-bottom: 16px;
}

.economia {
    color: #28a745;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 8px;
}

.plano-descricao {
    color: #6c757d;
    font-size: 0.95rem;
    line-height: 1.6;
}

.plano-recursos {
    margin-bottom: 32px;
}

.recurso-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
}

.recurso-item:last-child {
    border-bottom: none;
}

.recurso-item i {
    color: #28a745;
    font-size: 1.1rem;
}

.recurso-item span {
    font-size: 0.95rem;
}

.plano-action {
    margin-top: auto;
}

.plano-card.destaque .btn-primary {
    background: linear-gradient(135deg, #007bff, #6610f2);
    border: none;
}

.table th {
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #e9ecef;
}

.table td {
    vertical-align: middle;
}

.table i {
    font-size: 1.2rem;
}
</style>
@endpush

@php
    function getRecursoNome($recurso) {
        $nomes = [
            'produtos_ilimitados' => 'Produtos Ilimitados',
            'pedidos_ilimitados' => 'Pedidos Ilimitados',
            'pagamento_online' => 'Pagamento Online',
            'relatorios_completos' => 'Relatórios Completos',
            'estatisticas_visitas' => 'Estatísticas de Visitas',
            'notificacoes_whatsapp' => 'Notificações WhatsApp',
            'cozinha_app' => 'App Cozinha',
            'nfe_integracao' => 'Integração NFe',
            'lgpd_compliance' => 'LGPD Compliance',
            'popups_marketing' => 'Pop-ups Marketing',
            'suporte_prioritario' => 'Suporte Prioritário',
            'dominio_personalizado' => 'Domínio Personalizado',
            'api_acesso' => 'Acesso API',
        ];
        return $nomes[$recurso] ?? $recurso;
    }
@endphp
