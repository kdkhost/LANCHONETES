<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Funcionario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'funcionarios';

    protected $fillable = [
        'usuario_id', 'loja_id', 'tipo', 'cargo', 'salario',
        'data_admissao', 'data_demissao', 'e_entregador', 'veiculo',
        'placa_veiculo', 'cnh', 'latitude_atual', 'longitude_atual',
        'localizacao_atualizada_em', 'disponivel_entregas', 'ativo', 'configuracoes',
    ];

    protected $casts = [
        'e_entregador'              => 'boolean',
        'disponivel_entregas'       => 'boolean',
        'ativo'                     => 'boolean',
        'data_admissao'             => 'date',
        'data_demissao'             => 'date',
        'localizacao_atualizada_em' => 'datetime',
        'salario'                   => 'decimal:2',
        'latitude_atual'            => 'decimal:8',
        'longitude_atual'           => 'decimal:8',
        'configuracoes'             => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function pedidosEntrega(): HasMany
    {
        return $this->hasMany(Pedido::class, 'entregador_id');
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(Entrega::class, 'entregador_id');
    }

    public function atualizarLocalizacao(float $lat, float $lng): void
    {
        $this->update([
            'latitude_atual'            => $lat,
            'longitude_atual'           => $lng,
            'localizacao_atualizada_em' => now(),
        ]);
    }

    public function getTipoLabelAttribute(): string
    {
        return config('lanchonete.funcionario.tipos')[$this->tipo] ?? $this->tipo;
    }

    public function scopeEntregadores($query)
    {
        return $query->where('e_entregador', true)->where('ativo', true);
    }

    public function scopeDisponiveis($query)
    {
        return $query->entregadores()->where('disponivel_entregas', true);
    }
}
