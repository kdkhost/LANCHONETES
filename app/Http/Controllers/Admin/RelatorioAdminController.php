<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Pagamento;
use App\Models\ItemPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RelatorioAdminController extends Controller
{
    public function vendas(Request $request)
    {
        $lojaId   = Auth::user()->loja_id;
        $de       = $request->input('de', now()->startOfMonth()->toDateString());
        $ate      = $request->input('ate', now()->toDateString());
        $agrupado = $request->input('agrupado', 'dia');

        $query = Pedido::with(['itens', 'pagamento'])
            ->whereIn('status', ['entregue', 'pronto', 'saiu_para_entrega'])
            ->whereBetween('created_at', [$de . ' 00:00:00', $ate . ' 23:59:59'])
            ->when($lojaId, fn($q) => $q->where('loja_id', $lojaId));

        $pedidos = $query->get();

        // Totais gerais
        $totalVendas      = $pedidos->count();
        $faturamentoTotal = $pedidos->sum('total');
        $ticketMedio      = $totalVendas > 0 ? $faturamentoTotal / $totalVendas : 0;
        $totalDesconto    = $pedidos->sum('desconto');

        // Agrupamento por período
        $formato = match ($agrupado) {
            'semana' => '%Y-%u',
            'mes'    => '%Y-%m',
            default  => '%Y-%m-%d',
        };
        $grafico = $pedidos->groupBy(fn($p) => $p->created_at->format(
            match ($agrupado) { 'semana' => 'W/Y', 'mes' => 'm/Y', default => 'd/m/Y' }
        ))->map(fn($grupo) => [
            'periodo'      => $grupo->first()->created_at->format(match ($agrupado) { 'semana' => 'W/Y', 'mes' => 'm/Y', default => 'd/m/Y' }),
            'pedidos'      => $grupo->count(),
            'faturamento'  => $grupo->sum('total'),
        ])->values();

        // Produtos mais vendidos
        $topProdutos = ItemPedido::select('produto_nome', DB::raw('SUM(quantidade) as total_vendido'), DB::raw('SUM(subtotal) as receita'))
            ->whereHas('pedido', function ($q) use ($de, $ate, $lojaId) {
                $q->whereIn('status', ['entregue', 'pronto', 'saiu_para_entrega'])
                  ->whereBetween('created_at', [$de . ' 00:00:00', $ate . ' 23:59:59'])
                  ->when($lojaId, fn($q) => $q->where('loja_id', $lojaId));
            })
            ->groupBy('produto_nome')
            ->orderByDesc('total_vendido')
            ->limit(15)
            ->get();

        // Por método de pagamento
        $porMetodo = $pedidos->groupBy(fn($p) => $p->pagamento?->metodo ?? 'desconhecido')
            ->map(fn($grupo, $metodo) => [
                'metodo'       => config("lanchonete.pagamento.metodos.$metodo", ucfirst($metodo)),
                'pedidos'      => $grupo->count(),
                'faturamento'  => $grupo->sum('total'),
            ])->values();

        // Por tipo de entrega
        $porEntrega = $pedidos->groupBy('tipo_entrega')
            ->map(fn($grupo, $tipo) => [
                'tipo'    => $tipo === 'entrega' ? 'Entrega' : 'Retirada',
                'pedidos' => $grupo->count(),
            ])->values();

        if ($request->expectsJson()) {
            return response()->json(compact('totalVendas', 'faturamentoTotal', 'ticketMedio', 'totalDesconto', 'grafico', 'topProdutos', 'porMetodo', 'porEntrega'));
        }

        return view('admin.relatorios.vendas', compact('totalVendas', 'faturamentoTotal', 'ticketMedio', 'totalDesconto', 'grafico', 'topProdutos', 'porMetodo', 'porEntrega', 'de', 'ate', 'agrupado'));
    }

    public function exportarCsv(Request $request)
    {
        $lojaId = Auth::user()->loja_id;
        $de     = $request->input('de', now()->startOfMonth()->toDateString());
        $ate    = $request->input('ate', now()->toDateString());

        $pedidos = Pedido::with(['usuario', 'itens', 'pagamento'])
            ->whereIn('status', ['entregue', 'pronto', 'saiu_para_entrega', 'cancelado'])
            ->whereBetween('created_at', [$de . ' 00:00:00', $ate . ' 23:59:59'])
            ->when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
            ->orderBy('created_at')
            ->get();

        $filename = 'relatorio_' . $de . '_' . $ate . '.csv';
        $headers  = ['Content-Type' => 'text/csv; charset=utf-8', 'Content-Disposition' => "attachment; filename=$filename"];

        $callback = function () use ($pedidos) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // BOM UTF-8
            fputcsv($out, ['Número', 'Data', 'Cliente', 'Itens', 'Subtotal', 'Taxa Entrega', 'Desconto', 'Total', 'Pagamento', 'Status'], ';');
            foreach ($pedidos as $p) {
                fputcsv($out, [
                    $p->numero,
                    $p->created_at->format('d/m/Y H:i'),
                    $p->usuario->nome,
                    $p->itens->count(),
                    number_format($p->subtotal, 2, ',', '.'),
                    number_format($p->taxa_entrega, 2, ',', '.'),
                    number_format($p->desconto, 2, ',', '.'),
                    number_format($p->total, 2, ',', '.'),
                    $p->pagamento?->metodo ?? '-',
                    $p->status,
                ], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
