<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContadorVisita extends Model
{
    protected $table = 'contadores_visitas';

    protected $fillable = [
        'tipo',
        'entidade_id',
        'data',
        'total_visitas',
        'visitas_unicas',
    ];

    protected $casts = [
        'data' => 'date',
        'total_visitas' => 'integer',
        'visitas_unicas' => 'integer',
    ];

    public static function obterEstatisticas(string $tipo, int $entidadeId, int $dias = 30): array
    {
        $dataInicio = today()->subDays($dias);
        
        $dados = self::where('tipo', $tipo)
            ->where('entidade_id', $entidadeId)
            ->where('data', '>=', $dataInicio)
            ->orderBy('data')
            ->get();

        return [
            'total_visitas' => $dados->sum('total_visitas'),
            'visitas_unicas' => $dados->sum('visitas_unicas'),
            'media_diaria' => $dados->avg('total_visitas'),
            'por_dia' => $dados->map(fn($d) => [
                'data' => $d->data->format('Y-m-d'),
                'total' => $d->total_visitas,
                'unicas' => $d->visitas_unicas,
            ])->toArray(),
        ];
    }

    public static function rankingMaisVisitados(string $tipo, int $lojaId, int $limite = 10): array
    {
        $dataInicio = today()->subDays(30);
        
        return self::where('tipo', $tipo)
            ->where('data', '>=', $dataInicio)
            ->selectRaw('entidade_id, SUM(total_visitas) as total, SUM(visitas_unicas) as unicas')
            ->groupBy('entidade_id')
            ->orderByDesc('total')
            ->limit($limite)
            ->get()
            ->toArray();
    }
}
