@extends('layouts.admin')
@section('titulo', 'Estatísticas de Visitas')

@section('conteudo')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="mb-1">📊 Estatísticas de Visitas</h2>
        <p class="text-muted mb-0">Análise de tráfego e comportamento dos visitantes</p>
    </div>
    <form method="GET" class="d-flex gap-2">
        <select name="periodo" class="campo-input" style="width:auto" onchange="this.form.submit()">
            <option value="7" {{ $periodo == 7 ? 'selected' : '' }}>Últimos 7 dias</option>
            <option value="30" {{ $periodo == 30 ? 'selected' : '' }}>Últimos 30 dias</option>
            <option value="90" {{ $periodo == 90 ? 'selected' : '' }}>Últimos 90 dias</option>
        </select>
    </form>
</div>

{{-- Cards de resumo --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px">
    <div class="card-admin">
        <div class="card-admin-body">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:48px;height:48px;background:#e3f2fd;border-radius:12px;display:flex;align-items:center;justify-content:center">
                    <i class="bi bi-eye" style="font-size:24px;color:#1976d2"></i>
                </div>
                <div>
                    <div style="font-size:1.75rem;font-weight:800;color:#1976d2">{{ number_format($visitasLoja['total_visitas']) }}</div>
                    <div style="font-size:.85rem;color:#6c757d">Total de Visitas</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-admin">
        <div class="card-admin-body">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:48px;height:48px;background:#f3e5f5;border-radius:12px;display:flex;align-items:center;justify-content:center">
                    <i class="bi bi-people" style="font-size:24px;color:#7b1fa2"></i>
                </div>
                <div>
                    <div style="font-size:1.75rem;font-weight:800;color:#7b1fa2">{{ number_format($visitasLoja['visitas_unicas']) }}</div>
                    <div style="font-size:.85rem;color:#6c757d">Visitantes Únicos</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-admin">
        <div class="card-admin-body">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:48px;height:48px;background:#e8f5e9;border-radius:12px;display:flex;align-items:center;justify-content:center">
                    <i class="bi bi-graph-up" style="font-size:24px;color:#388e3c"></i>
                </div>
                <div>
                    <div style="font-size:1.75rem;font-weight:800;color:#388e3c">{{ number_format($visitasLoja['media_diaria'], 1) }}</div>
                    <div style="font-size:.85rem;color:#6c757d">Média Diária</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Gráfico de visitas diárias --}}
<div class="card-admin mb-4">
    <div class="card-admin-header">
        <h3><i class="bi bi-bar-chart"></i> Visitas Diárias</h3>
    </div>
    <div class="card-admin-body">
        <canvas id="graficoVisitas" height="80"></canvas>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">
    {{-- Top Produtos --}}
    <div class="card-admin">
        <div class="card-admin-header">
            <h3><i class="bi bi-box-seam"></i> Produtos Mais Visitados</h3>
        </div>
        <div class="card-admin-body">
            @forelse($topProdutos as $produto)
            <div style="display:flex;align-items:center;gap:12px;padding:12px;border-bottom:1px solid #f0f0f0">
                <img src="{{ asset('storage/' . $produto->imagem) }}" alt="{{ $produto->nome }}" style="width:48px;height:48px;object-fit:cover;border-radius:8px">
                <div style="flex:1">
                    <div style="font-weight:600">{{ $produto->nome }}</div>
                    <div style="font-size:.85rem;color:#6c757d">{{ number_format($produto->total) }} visitas</div>
                </div>
            </div>
            @empty
            <p class="text-muted text-center py-4">Nenhum dado disponível</p>
            @endforelse
        </div>
    </div>

    {{-- Top Categorias --}}
    <div class="card-admin">
        <div class="card-admin-header">
            <h3><i class="bi bi-tags"></i> Categorias Mais Visitadas</h3>
        </div>
        <div class="card-admin-body">
            @forelse($topCategorias as $categoria)
            <div style="display:flex;align-items:center;gap:12px;padding:12px;border-bottom:1px solid #f0f0f0">
                <div style="width:48px;height:48px;background:#f5f5f5;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:24px">
                    {{ $categoria->icone ?? '📦' }}
                </div>
                <div style="flex:1">
                    <div style="font-weight:600">{{ $categoria->nome }}</div>
                    <div style="font-size:.85rem;color:#6c757d">{{ number_format($categoria->total) }} visitas</div>
                </div>
            </div>
            @empty
            <p class="text-muted text-center py-4">Nenhum dado disponível</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Dispositivos --}}
<div class="card-admin">
    <div class="card-admin-header">
        <h3><i class="bi bi-phone"></i> Visitas por Dispositivo</h3>
    </div>
    <div class="card-admin-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
            @php
                $totalDispositivos = $visitasPorDispositivo->sum();
                $dispositivos = [
                    'mobile' => ['📱', 'Mobile', '#4caf50'],
                    'desktop' => ['💻', 'Desktop', '#2196f3'],
                    'tablet' => ['📲', 'Tablet', '#ff9800'],
                    'unknown' => ['❓', 'Desconhecido', '#9e9e9e'],
                ];
            @endphp
            @foreach($dispositivos as $tipo => $info)
                @php
                    $total = $visitasPorDispositivo[$tipo] ?? 0;
                    $percentual = $totalDispositivos > 0 ? ($total / $totalDispositivos) * 100 : 0;
                @endphp
                <div style="text-align:center;padding:16px;border:1px solid #e9ecef;border-radius:12px">
                    <div style="font-size:2rem;margin-bottom:8px">{{ $info[0] }}</div>
                    <div style="font-weight:700;font-size:1.5rem;color:{{ $info[2] }}">{{ number_format($total) }}</div>
                    <div style="font-size:.9rem;color:#6c757d;margin-bottom:4px">{{ $info[1] }}</div>
                    <div style="font-size:.85rem;color:#999">{{ number_format($percentual, 1) }}%</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('graficoVisitas');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($visitasDiarias->pluck('data')),
        datasets: [{
            label: 'Total de Visitas',
            data: @json($visitasDiarias->pluck('total')),
            borderColor: '#FF6B35',
            backgroundColor: 'rgba(255, 107, 53, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Visitantes Únicos',
            data: @json($visitasDiarias->pluck('unicas')),
            borderColor: '#2C3E50',
            backgroundColor: 'rgba(44, 62, 80, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: true, position: 'top' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
@endpush
