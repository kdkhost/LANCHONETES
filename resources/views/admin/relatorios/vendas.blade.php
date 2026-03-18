@extends('layouts.admin')
@section('titulo', 'Relatório de Vendas')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('conteudo')
<div class="d-flex align-items-center justify-content-between gap-2 mb-4">
    <form method="GET" action="{{ route('admin.relatorios.vendas') }}" class="d-flex gap-2 flex-wrap align-items-center">
        <input type="date" name="de"  value="{{ $de }}"  class="campo-input" style="width:auto">
        <input type="date" name="ate" value="{{ $ate }}" class="campo-input" style="width:auto">
        <select name="agrupado" class="campo-input" style="width:auto">
            <option value="dia"    {{ $agrupado === 'dia'    ? 'selected' : '' }}>Por dia</option>
            <option value="semana" {{ $agrupado === 'semana' ? 'selected' : '' }}>Por semana</option>
            <option value="mes"    {{ $agrupado === 'mes'    ? 'selected' : '' }}>Por mês</option>
        </select>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>
    <a href="{{ route('admin.relatorios.exportar-csv') }}?de={{ $de }}&ate={{ $ate }}" class="btn btn-success btn-sm">
        <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV
    </a>
</div>

{{-- Métricas --}}
<div class="metrics-row mb-4">
    <div class="metric-card">
        <div class="metric-icon bg-primary-soft"><i class="bi bi-bag-check text-primary"></i></div>
        <div class="metric-info">
            <div class="metric-valor">{{ $totalVendas }}</div>
            <div class="metric-label">Pedidos no Período</div>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon bg-success-soft"><i class="bi bi-currency-dollar text-success"></i></div>
        <div class="metric-info">
            <div class="metric-valor">R$ {{ number_format($faturamentoTotal, 2, ',', '.') }}</div>
            <div class="metric-label">Faturamento Total</div>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon bg-info-soft"><i class="bi bi-receipt text-info"></i></div>
        <div class="metric-info">
            <div class="metric-valor">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</div>
            <div class="metric-label">Ticket Médio</div>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon bg-warning-soft"><i class="bi bi-tag text-warning"></i></div>
        <div class="metric-info">
            <div class="metric-valor">R$ {{ number_format($totalDesconto, 2, ',', '.') }}</div>
            <div class="metric-label">Descontos Concedidos</div>
        </div>
    </div>
</div>

<div class="dashboard-row-2">
    {{-- Gráfico de Faturamento --}}
    <div class="card-admin">
        <div class="card-admin-header">
            <h3><i class="bi bi-graph-up"></i> Faturamento por {{ ucfirst($agrupado) }}</h3>
        </div>
        <div class="card-admin-body">
            <canvas id="graficoVendas" height="250"></canvas>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:12px">
        {{-- Por Método de Pagamento --}}
        <div class="card-admin">
            <div class="card-admin-header"><h3><i class="bi bi-credit-card"></i> Por Pagamento</h3></div>
            <div class="card-admin-body p-0">
                @foreach($porMetodo as $m)
                <div class="top-produto-linha">
                    <span class="top-nome">{{ $m['metodo'] }}</span>
                    <span class="top-qtd">{{ $m['pedidos'] }} (R$ {{ number_format($m['faturamento'],2,',','.') }})</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Por Tipo de Entrega --}}
        <div class="card-admin">
            <div class="card-admin-header"><h3><i class="bi bi-bicycle"></i> Por Entrega</h3></div>
            <div class="card-admin-body p-0">
                @foreach($porEntrega as $e)
                <div class="top-produto-linha">
                    <span class="top-nome">{{ $e['tipo'] }}</span>
                    <span class="top-qtd">{{ $e['pedidos'] }} pedidos</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Top Produtos --}}
<div class="card-admin mt-3">
    <div class="card-admin-header"><h3><i class="bi bi-trophy"></i> Produtos Mais Vendidos</h3></div>
    <div class="card-admin-body p-0">
        <table class="tabela-admin">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produto</th>
                    <th>Qtd. Vendida</th>
                    <th>Receita Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topProdutos as $i => $prod)
                <tr>
                    <td><strong>{{ $i + 1 }}</strong></td>
                    <td>{{ $prod->produto_nome }}</td>
                    <td><strong>{{ $prod->total_vendido }}x</strong></td>
                    <td>R$ {{ number_format($prod->receita, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const graficoData = @json($grafico);
const ctx = document.getElementById('graficoVendas')?.getContext('2d');
if (ctx && graficoData.length) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: graficoData.map(d => d.periodo),
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
                y:  { type: 'linear', display: true, position: 'left',  beginAtZero: true, title: { display: true, text: 'Pedidos' } },
                y1: { type: 'linear', display: true, position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, title: { display: true, text: 'R$' } }
            }
        }
    });
}
</script>
@endpush
