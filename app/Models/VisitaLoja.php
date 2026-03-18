<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitaLoja extends Model
{
    protected $table = 'visitas_lojas';

    public $timestamps = false;

    protected $fillable = [
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

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public static function registrar(int $lojaId, ?int $usuarioId, array $dados): void
    {
        self::create([
            'loja_id'     => $lojaId,
            'usuario_id'  => $usuarioId,
            'ip'          => $dados['ip'] ?? null,
            'user_agent'  => $dados['user_agent'] ?? null,
            'referer'     => $dados['referer'] ?? null,
            'device_type' => $dados['device_type'] ?? null,
            'visitado_em' => now(),
        ]);

        self::atualizarContador('loja', $lojaId, $dados['ip'] ?? null);
    }

    private static function atualizarContador(string $tipo, int $entidadeId, ?string $ip): void
    {
        $hoje = today();
        $contador = ContadorVisita::firstOrCreate(
            ['tipo' => $tipo, 'entidade_id' => $entidadeId, 'data' => $hoje],
            ['total_visitas' => 0, 'visitas_unicas' => 0]
        );

        $contador->increment('total_visitas');

        if ($ip) {
            $jaVisitouHoje = match($tipo) {
                'loja' => self::where('loja_id', $entidadeId)
                    ->whereDate('visitado_em', $hoje)
                    ->where('ip', $ip)
                    ->where('id', '<', $contador->id)
                    ->exists(),
                'produto' => VisitaProduto::where('produto_id', $entidadeId)
                    ->whereDate('visitado_em', $hoje)
                    ->where('ip', $ip)
                    ->exists(),
                'categoria' => VisitaCategoria::where('categoria_id', $entidadeId)
                    ->whereDate('visitado_em', $hoje)
                    ->where('ip', $ip)
                    ->exists(),
                default => false,
            };

            if (!$jaVisitouHoje) {
                $contador->increment('visitas_unicas');
            }
        }
    }
}
