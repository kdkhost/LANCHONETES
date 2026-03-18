<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assinatura extends Model
{
    protected $fillable = [
        'loja_id',
        'plano_id',
        'status',
        'data_inicio',
        'data_fim',
        'trial_expira_em',
        'periodo',
        'valor_pago',
        'metodo_pagamento',
        'gateway_id',
        'notas',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'trial_expira_em' => 'date',
        'valor_pago' => 'decimal:2',
    ];

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class);
    }

    public function estaEmTrial(): bool
    {
        return $this->status === 'trial' && 
               $this->trial_expira_em && 
               $this->trial_expira_em->isFuture();
    }

    public function trialExpirado(): bool
    {
        return $this->status === 'trial' && 
               $this->trial_expira_em && 
               $this->trial_expira_em->isPast();
    }

    public function estaAtiva(): bool
    {
        return $this->status === 'ativa' && 
               (!$this->data_fim || $this->data_fem->isFuture());
    }

    public function expirou(): bool
    {
        return $this->data_fim && $this->data_fem->isPast();
    }

    public function podeCriarProdutos(): bool
    {
        if ($this->estaEmTrial()) {
            return true; // Trial tem acesso total
        }
        
        return $this->estaAtiva() && $this->plano?->temRecurso('produtos_ilimitados');
    }

    public function podeConfigurarPagamento(): bool
    {
        if ($this->estaEmTrial()) {
            return true; // Trial tem acesso total
        }
        
        return $this->estaAtiva() && $this->plano?->temRecurso('pagamento_online');
    }

    public function podeVender(): bool
    {
        if ($this->estaEmTrial()) {
            return true; // Trial tem acesso total
        }
        
        return $this->estaAtiva() && $this->plano?->temRecurso('pagamento_online');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'trial' => 'Período de Teste',
            'ativa' => 'Ativa',
            'cancelada' => 'Cancelada',
            'suspensa' => 'Suspensa',
            'expirada' => 'Expirada',
            default => 'Desconhecido',
        };
    }

    public function getStatusCorAttribute(): string
    {
        return match($this->status) {
            'trial' => '#28a745',
            'ativa' => '#007bff',
            'cancelada' => '#dc3545',
            'suspensa' => '#ffc107',
            'expirada' => '#6c757d',
            default => '#6c757d',
        };
    }

    public function getDiasRestantesTrialAttribute(): int
    {
        if (!$this->trial_expira_em) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->trial_expira_em, false));
    }

    public function getDiasRestantesAssinaturaAttribute(): int
    {
        if (!$this->data_fim) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->data_fem, false));
    }
}
