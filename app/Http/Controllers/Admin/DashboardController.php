<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Usuario;
use App\Models\Loja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario  = Auth::user();
        $lojaId   = $usuario->loja_id;

        $hoje         = now()->toDateString();
        $mesAtual     = now()->format('Y-m');
        $semanaInicio = now()->startOfWeek()->toDateString();

        $pedidosHoje = Pedido::when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
            ->whereDate('created_at', $hoje)
            ->count();

        $faturamentoHoje = Pedido::when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
            ->whereDate('created_at', $hoje)
            ->whereNotIn('status', ['cancelado', 'recusado'])
            ->sum('total');

        $pedidosMes = Pedido::when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $faturamentoMes = Pedido::when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereNotIn('status', ['cancelado', 'recusado'])
            ->sum('total');

        $pedidosAtivos = Pedido::when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
            ->ativos()
            ->with(['usuario', 'itens', 'pagamento'])
            ->latest()
            ->take(20)
            ->get();

        $pedidosPorStatus = Pedido::when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
            ->whereDate('created_at', $hoje)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $graficoSemana = Pedido::when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNotIn('status', ['cancelado', 'recusado'])
            ->select(
                DB::raw('DATE(created_at) as dia'),
                DB::raw('COUNT(*) as pedidos'),
                DB::raw('SUM(total) as faturamento')
            )
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $topProdutos = DB::table('itens_pedido')
            ->join('pedidos', 'pedidos.id', '=', 'itens_pedido.pedido_id')
            ->when($lojaId, fn($q) => $q->where('pedidos.loja_id', $lojaId))
            ->whereDate('pedidos.created_at', '>=', now()->subDays(30))
            ->whereNotIn('pedidos.status', ['cancelado', 'recusado'])
            ->select('itens_pedido.produto_nome', DB::raw('SUM(itens_pedido.quantidade) as total_vendido'))
            ->groupBy('itens_pedido.produto_nome')
            ->orderByDesc('total_vendido')
            ->take(5)
            ->get();

        $novosClientes = Usuario::where('role', 'cliente')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->count();

        return view('admin.dashboard', compact(
            'pedidosHoje', 'faturamentoHoje', 'pedidosMes', 'faturamentoMes',
            'pedidosAtivos', 'pedidosPorStatus', 'graficoSemana',
            'topProdutos', 'novosClientes'
        ));
    }
}
