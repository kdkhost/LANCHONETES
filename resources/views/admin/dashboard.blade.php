@extends('layouts.admin')
@section('titulo', 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('conteudo')
<div class="dashboard-grid">

    {{-- Cards de métricas --}}
    <div class="metrics-row dashboard-cards">
        <div class="metric-card">
            <div class="metric-icon bg-primary-soft"><i class="bi bi-bag-check text-primary"></i></div>
            <div class="metric-info">
                <div class="metric-valor">{{ $pedidosHoje }}</div>
                <div class="metric-label">Pedidos Hoje</div>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-success-soft"><i class="bi bi-currency-dollar text-success"></i></div>
            <div class="metric-info">
                <div class="metric-valor">R$ {{ number_format($faturamentoHoje, 2, ',', '.') }}</div>
                <div class="metric-label">Faturamento Hoje</div>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-info-soft"><i class="bi bi-calendar-check text-info"></i></div>
            <div class="metric-info">
                <div class="metric-valor">{{ $pedidosMes }}</div>
                <div class="metric-label">Pedidos este Mês</div>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-warning-soft"><i class="bi bi-graph-up text-warning"></i></div>
            <div class="metric-info">
                <div class="metric-valor">R$ {{ number_format($faturamentoMes, 2, ',', '.') }}</div>
                <div class="metric-label">Faturamento Mês</div>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-secondary-soft"><i class="bi bi-people text-secondary"></i></div>
            <div class="metric-info">
                <div class="metric-valor">{{ $novosClientes }}</div>
                <div class="metric-label">Novos Clientes (30d)</div>
            </div>
        </div>
    </div>

    <div class="dashboard-row-2">
        {{-- Pedidos Recentes --}}
        <div class="card-admin">
            <div class="card-admin-header">
                <h3><i class="bi bi-clock-history"></i> Pedidos Recentes</h3>
                <a href="{{ route('admin.pedidos.index') }}" class="btn btn-sm btn-outline-primary">Ver Todos</a>
            </div>
            <div class="card-admin-body pedidos-recentes p-0">
                @forelse($pedidosAtivos as $pedido)
                <div class="pedido-linha" onclick="window.location='{{ route('admin.pedidos.show', $pedido) }}'">
                    <div class="pedido-linha-info">
                        <span class="pedido-numero">{{ $pedido->numero }}</span>
                        <span class="pedido-cliente">{{ $pedido->usuario->nome }}</span>
                        <span class="pedido-itens">{{ $pedido->itens->count() }} item(ns)</span>
                    </div>
                    <div class="pedido-linha-meta">
                        <span class="badge-status" style="background:{{ $pedido->status_cor }}20;color:{{ $pedido->status_cor }}">
                            {{ $pedido->status_label }}
                        </span>
                        <span class="pedido-total">R$ {{ number_format($pedido->total, 2, ',', '.') }}</span>
                    </div>
                </div>
                @empty
                <div class="empty-state py-4">
                    <i class="bi bi-check2-all text-success fs-1"></i>
                    <p class="mt-2 text-muted">Nenhum pedido em andamento</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Gráfico e Top Produtos --}}
        <div class="dashboard-col-right">
            <div class="card-admin mb-3">
                <div class="card-admin-header">
                    <h3><i class="bi bi-graph-up"></i> Vendas da Semana</h3>
                </div>
                <div class="card-admin-body">
                    <canvas id="graficoSemana" height="200"></canvas>
                </div>
            </div>
            <div class="card-admin">
                <div class="card-admin-header">
                    <h3><i class="bi bi-trophy"></i> Top Produtos (30d)</h3>
                </div>
                <div class="card-admin-body p-0">
                    @foreach($topProdutos as $i => $prod)
                    <div class="top-produto-linha">
                        <span class="top-pos">{{ $i + 1 }}</span>
                        <span class="top-nome">{{ $prod->produto_nome }}</span>
                        <span class="top-qtd">{{ $prod->total_vendido }}x</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Ações Rápidas --}}
    <div class="card-admin">
        <div class="card-admin-header">
            <h3><i class="bi bi-lightning"></i> Ações Rápidas</h3>
        </div>
        <div class="card-admin-body acoes-rapidas">
            <div class="acoes-rapidas-grid">
                {{-- Add tour classes to actions section --}}
                <div class="acoes-rapidas-item">
                    <div class="acoes-rapidas-icon bg-primary-soft"><i class="bi bi-plus text-primary"></i></div>
                    <div class="acoes-rapidas-info">
                        <span class="acoes-rapidas-label">Adicionar Pedido</span>
                    </div>
                </div>
                <div class="acoes-rapidas-item">
                    <div class="acoes-rapidas-icon bg-success-soft"><i class="bi bi-currency-dollar text-success"></i></div>
                    <div class="acoes-rapidas-info">
                        <span class="acoes-rapidas-label">Adicionar Pagamento</span>
                    </div>
                </div>
                <div class="acoes-rapidas-item">
                    <div class="acoes-rapidas-icon bg-info-soft"><i class="bi bi-calendar-check text-info"></i></div>
                    <div class="acoes-rapidas-info">
                        <span class="acoes-rapidas-label">Agendar Entrega</span>
                    </div>
                </div>
                <div class="acoes-rapidas-item">
                    <div class="acoes-rapidas-icon bg-warning-soft"><i class="bi bi-graph-up text-warning"></i></div>
                    <div class="acoes-rapidas-info">
                        <span class="acoes-rapidas-label">Ver Relatórios</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status dos pedidos hoje --}}
    <div class="card-admin">
        <div class="card-admin-header">
            <h3><i class="bi bi-pie-chart"></i> Status dos Pedidos Hoje</h3>
        </div>
        <div class="card-admin-body">
            <div class="status-hoje-grid">
                @foreach(config('lanchonete.pedido.status') as $chave => $label)
                @php $qtd = $pedidosPorStatus[$chave] ?? 0; @endphp
                <div class="status-hoje-item">
                    <div class="status-hoje-cor" style="background:{{ config('lanchonete.pedido.cores_status')[$chave] }}"></div>
                    <div class="status-hoje-info">
                        <span class="status-hoje-label">{{ $label }}</span>
                        <span class="status-hoje-qtd">{{ $qtd }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const graficoData = @json($graficoSemana);
const ctx = document.getElementById('graficoSemana')?.getContext('2d');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: graficoData.map(d => {
                const dt = new Date(d.dia + 'T00:00:00');
                return dt.toLocaleDateString('pt-BR', { weekday: 'short', day: '2-digit' });
            }),
            datasets: [{
                label: 'Pedidos',
                data: graficoData.map(d => d.pedidos),
                backgroundColor: '#FF6B3580',
                borderColor: '#FF6B35',
                borderWidth: 2,
                borderRadius: 6,
                yAxisID: 'y',
            }, {
                label: 'Faturamento (R$)',
                data: graficoData.map(d => parseFloat(d.faturamento)),
                type: 'line',
                borderColor: '#2C3E50',
                backgroundColor: '#2C3E5020',
                borderWidth: 2,
                tension: 0.4,
                yAxisID: 'y1',
            }]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y:  { type: 'linear', display: true, position: 'left',  beginAtZero: true },
                y1: { type: 'linear', display: true, position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } }
            }
        }
    });
}

// Atualização automática a cada 30s
setInterval(() => {
    fetch('{{ route("admin.dashboard") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(() => {})
        .catch(() => {});
}, 30000);
</script>
@endpush
