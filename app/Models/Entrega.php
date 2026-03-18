<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Entrega extends Model
{
    use HasFactory;

    protected $table = 'entregas';

    protected $fillable = [
        'pedido_id', 'entregador_id', 'status', 'distancia_km',
        'tempo_estimado_min', 'taxa_entrega', 'latitude_coleta', 'longitude_coleta',
        'latitude_destino', 'longitude_destino', 'latitude_atual', 'longitude_atual',
        'token_rastreamento', 'link_rastreamento_whatsapp',
        'aceito_em', 'coletado_em', 'entregue_em', 'localizacao_atualizada_em',
        'observacoes', 'rota_historico',
    ];

    protected $casts = [
        'distancia_km'              => 'decimal:3',
        'taxa_entrega'              => 'decimal:2',
        'latitude_coleta'           => 'decimal:8',
        'longitude_coleta'          => 'decimal:8',
        'latitude_destino'          => 'decimal:8',
        'longitude_destino'         => 'decimal:8',
        'latitude_atual'            => 'decimal:8',
        'longitude_atual'           => 'decimal:8',
        'aceito_em'                 => 'datetime',
        'coletado_em'               => 'datetime',
        'entregue_em'               => 'datetime',
        'localizacao_atualizada_em' => 'datetime',
        'rota_historico'            => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($entrega) {
            if (empty($entrega->token_rastreamento)) {
                $entrega->token_rastreamento = Str::random(64);
            }
        });
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function entregador(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'entregador_id');
    }

    public function getLinkRastreamentoAttribute(): string
    {
        return route('rastreamento.publico', $this->token_rastreamento);
    }

    public function atualizarLocalizacao(float $lat, float $lng): void
    {
        $historico = $this->rota_historico ?? [];
        $historico[] = [
            'lat'  => $lat,
            'lng'  => $lng,
            'hora' => now()->toISOString(),
        ];

        $this->update([
            'latitude_atual'            => $lat,
            'longitude_atual'           => $lng,
            'localizacao_atualizada_em' => now(),
            'rota_historico'            => $historico,
        ]);
    }
}
