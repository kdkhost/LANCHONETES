<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plano extends Model
{
    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'preco_mensal',
        'preco_anual',
        'recursos',
        'ativo',
        'destaque',
        'ordem',
    ];

    protected $casts = [
        'preco_mensal' => 'decimal:2',
        'preco_anual' => 'decimal:2',
        'recursos' => 'array',
        'ativo' => 'boolean',
        'destaque' => 'boolean',
    ];

    public function assinaturas(): HasMany
    {
        return $this->hasMany(Assinatura::class);
    }

    public function lojas(): HasMany
    {
        return $this->hasMany(Loja::class);
    }

    public function temRecurso(string $recurso): bool
    {
        return $this->recursos[$recurso] ?? false;
    }

    public function getPrecoFormatado(string $periodo = 'mensal'): string
    {
        $preco = $periodo === 'anual' ? $this->preco_anual : $this->preco_mensal;
        
        if ($preco == 0) {
            return 'Grátis';
        }
        
        return 'R$ ' . number_format($preco, 2, ',', '.');
    }

    public function getEconomiaAnualAttribute(): ?float
    {
        if (!$this->preco_mensal || !$this->preco_anual) {
            return null;
        }
        
        $valorAnualMensal = $this->preco_mensal * 12;
        return $valorAnualMensal - $this->preco_anual;
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeDestaque($query)
    {
        return $query->where('destaque', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }
}
