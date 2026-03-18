<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitaProduto extends Model
{
    protected $table = 'visitas_produtos';

    public $timestamps = false;

    protected $fillable = [
        'produto_id',
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

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public static function registrar(int $produtoId, int $lojaId, ?int $usuarioId, array $dados): void
    {
        self::create([
            'produto_id'  => $produtoId,
            'loja_id'     => $lojaId,
            'usuario_id'  => $usuarioId,
            'ip'          => $dados['ip'] ?? null,
            'user_agent'  => $dados['user_agent'] ?? null,
            'referer'     => $dados['referer'] ?? null,
            'device_type' => $dados['device_type'] ?? null,
            'visitado_em' => now(),
        ]);

        self::atualizarContador($produtoId, $dados['ip'] ?? null);
    }

    private static function atualizarContador(int $produtoId, ?string $ip): void
    {
        $hoje = today();
        $contador = ContadorVisita::firstOrCreate(
            ['tipo' => 'produto', 'entidade_id' => $produtoId, 'data' => $hoje],
            ['total_visitas' => 0, 'visitas_unicas' => 0]
        );

        $contador->increment('total_visitas');

        if ($ip && !self::where('produto_id', $produtoId)
            ->whereDate('visitado_em', $hoje)
            ->where('ip', $ip)
            ->where('id', '<', $contador->id)
            ->exists()) {
            $contador->increment('visitas_unicas');
        }
    }
}
