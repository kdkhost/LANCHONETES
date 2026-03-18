<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContadorVisita;
use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EstatisticasController extends Controller
{
    public function visitas(Request $request)
    {
        $loja = Auth::user()->loja;
        if (!$loja) abort(403);

        $periodo = $request->get('periodo', 30);
        $dataInicio = today()->subDays($periodo);

        // Estatísticas da loja
        $visitasLoja = ContadorVisita::obterEstatisticas('loja', $loja->id, $periodo);

        // Top 10 produtos mais visitados
        $topProdutos = DB::table('contadores_visitas as cv')
            ->join('produtos as p', 'cv.entidade_id', '=', 'p.id')
            ->where('cv.tipo', 'produto')
            ->where('p.loja_id', $loja->id)
            ->where('cv.data', '>=', $dataInicio)
            ->select('p.id', 'p.nome', 'p.imagem', DB::raw('SUM(cv.total_visitas) as total'))
            ->groupBy('p.id', 'p.nome', 'p.imagem')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Top 10 categorias mais visitadas
        $topCategorias = DB::table('contadores_visitas as cv')
            ->join('categorias as c', 'cv.entidade_id', '=', 'c.id')
            ->where('cv.tipo', 'categoria')
            ->where('c.loja_id', $loja->id)
            ->where('cv.data', '>=', $dataInicio)
            ->select('c.id', 'c.nome', 'c.icone', DB::raw('SUM(cv.total_visitas) as total'))
            ->groupBy('c.id', 'c.nome', 'c.icone')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Visitas por dispositivo (últimos 30 dias)
        $visitasPorDispositivo = DB::table('visitas_lojas')
            ->where('loja_id', $loja->id)
            ->where('visitado_em', '>=', $dataInicio)
            ->select('device_type', DB::raw('COUNT(*) as total'))
            ->groupBy('device_type')
            ->get()
            ->mapWithKeys(fn($v) => [$v->device_type ?? 'unknown' => $v->total]);

        // Gráfico de visitas diárias
        $visitasDiarias = ContadorVisita::where('tipo', 'loja')
            ->where('entidade_id', $loja->id)
            ->where('data', '>=', $dataInicio)
            ->orderBy('data')
            ->get()
            ->map(fn($v) => [
                'data' => $v->data->format('d/m'),
                'total' => $v->total_visitas,
                'unicas' => $v->visitas_unicas,
            ]);

        return view('admin.estatisticas.visitas', compact(
            'visitasLoja',
            'topProdutos',
            'topCategorias',
            'visitasPorDispositivo',
            'visitasDiarias',
            'periodo'
        ));
    }
}
