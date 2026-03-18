<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitaCategoria extends Model
{
    protected $table = 'visitas_categorias';

    public $timestamps = false;

    protected $fillable = [
        'categoria_id',
        'loja_id',
        'usuario_id',
        'ip',
        'user_agent',
        'referer',
        'device_type',
        'visitado_em',
    ];

    protected $casts = [
        'visitado_em' => 'datetime',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public static function registrar(int $categoriaId, int $lojaId, ?int $usuarioId, array $dados): void
    {
        self::create([
            'categoria_id' => $categoriaId,
            'loja_id'      => $lojaId,
            'usuario_id'   => $usuarioId,
            'ip'           => $dados['ip'] ?? null,
            'user_agent'   => $dados['user_agent'] ?? null,
            'referer'      => $dados['referer'] ?? null,
            'device_type'  => $dados['device_type'] ?? null,
            'visitado_em'  => now(),
        ]);

        self::atualizarContador($categoriaId, $dados['ip'] ?? null);
    }

    private static function atualizarContador(int $categoriaId, ?string $ip): void
    {
        $hoje = today();
        $contador = ContadorVisita::firstOrCreate(
            ['tipo' => 'categoria', 'entidade_id' => $categoriaId, 'data' => $hoje],
            ['total_visitas' => 0, 'visitas_unicas' => 0]
        );

        $contador->increment('total_visitas');

        if ($ip && !self::where('categoria_id', $categoriaId)
            ->whereDate('visitado_em', $hoje)
            ->where('ip', $ip)
            ->where('id', '<', $contador->id)
            ->exists()) {
            $contador->increment('visitas_unicas');
        }
    }
}
