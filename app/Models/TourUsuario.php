<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourUsuario extends Model
{
    protected $fillable = [
        'user_id',
        'tour_id',
        'status',
        'passo_atual',
        'dados_adicionais',
        'iniciado_em',
        'concluido_em',
    ];

    protected $casts = [
        'dados_adicionais' => 'array',
        'iniciado_em' => 'datetime',
        'concluido_em' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pendente' => 'Pendente',
            'em_andamento' => 'Em Andamento',
            'concluido' => 'Concluído',
            'pulado' => 'Pulado',
            default => 'Desconhecido',
        };
    }

    public function getStatusCorAttribute(): string
    {
        return match($this->status) {
            'pendente' => '#6c757d',
            'em_andamento' => '#007bff',
            'concluido' => '#28a745',
            'pulado' => '#ffc107',
            default => '#6c757d',
        };
    }

    public function getDuracaoAttribute(): ?string
    {
        if (!$this->iniciado_em) {
            return null;
        }

        $fim = $this->concluido_em ?? now();
        $duracao = $this->iniciado_em->diff($fim);

        if ($duracao->days > 0) {
            return $duracao->days . 'd ' . $duracao->h . 'h ' . $duracao->i . 'min';
        } elseif ($duracao->h > 0) {
            return $duracao->h . 'h ' . $duracao->i . 'min';
        } else {
            return $duracao->i . ' min ' . $duracao->s . 's';
        }
    }

    public function getPercentualConclusaoAttribute(): float
    {
        if (!$this->tour) {
            return 0;
        }

        $totalPassos = $this->tour->total_passos;
        if ($totalPassos === 0) {
            return 0;
        }

        return round(($this->passo_atual / $totalPassos) * 100, 2);
    }

    public function getPassoAtualAttribute(): array
    {
        if (!$this->tour) {
            return [];
        }

        $passos = $this->tour->passos;
        $indice = min($this->passo_atual, count($passos) - 1);
        
        return $passos[$indice] ?? [];
    }

    public function getProximoPassoAttribute(): ?array
    {
        if (!$this->tour) {
            return null;
        }

        $passos = $this->tour->passos;
        $proximoIndice = $this->passo_atual + 1;
        
        return $passos[$proximoIndice] ?? null;
    }

    public function getPassoAnteriorAttribute(): ?array
    {
        if (!$this->tour || $this->passo_atual <= 0) {
            return null;
        }

        $passos = $this->tour->passos;
        $indiceAnterior = $this->passo_atual - 1;
        
        return $passos[$indiceAnterior] ?? null;
    }

    public function podeAvancar(): bool
    {
        return $this->status === 'em_andamento' && 
               $this->passo_atual < ($this->tour->total_passos - 1);
    }

    public function podeVoltar(): bool
    {
        return $this->status === 'em_andamento' && $this->passo_atual > 0;
    }

    public function estaNoUltimoPasso(): bool
    {
        return $this->status === 'em_andamento' && 
               $this->passo_atual >= ($this->tour->total_passos - 1);
    }

    public function iniciar(): bool
    {
        if ($this->status !== 'pendente') {
            return false;
        }

        return $this->update([
            'status' => 'em_andamento',
            'iniciado_em' => now(),
            'dados_adicionais' => array_merge($this->dados_adicionais ?? [], [
                'ip_inicio' => request()->ip(),
                'user_agent_inicio' => request()->userAgent(),
            ])
        ]);
    }

    public function avancar(): bool
    {
        if (!$this->podeAvancar()) {
            return false;
        }

        $novoPasso = $this->passo_atual + 1;
        
        if ($novoPasso >= $this->tour->total_passos) {
            // Concluir tour
            return $this->concluir();
        }

        return $this->update([
            'passo_atual' => $novoPasso,
            'dados_adicionais' => array_merge($this->dados_adicionais ?? [], [
                'passo_' . $novoPasso . '_acessado_em' => now()->toISOString(),
            ])
        ]);
    }

    public function voltar(): bool
    {
        if (!$this->podeVoltar()) {
            return false;
        }

        return $this->update([
            'passo_atual' => $this->passo_atual - 1,
            'dados_adicionais' => array_merge($this->dados_adicionais ?? [], [
                'voltou_para_passo_' . ($this->passo_atual - 1) => now()->toISOString(),
            ])
        ]);
    }

    public function concluir(): bool
    {
        if ($this->status === 'concluido') {
            return true;
        }

        return $this->update([
            'status' => 'concluido',
            'passo_atual' => $this->tour->total_passos,
            'concluido_em' => now(),
            'dados_adicionais' => array_merge($this->dados_adicionais ?? [], [
                'concluido_em' => now()->toISOString(),
                'tempo_total' => $this->duracao,
            ])
        ]);
    }

    public function pular(string $motivo = 'manual'): bool
    {
        return $this->update([
            'status' => 'pulado',
            'dados_adicionais' => array_merge($this->dados_adicionais ?? [], [
                'pulado_em' => now()->toISOString(),
                'motivo_pulo' => $motivo,
                'passo_ao_pular' => $this->passo_atual,
            ])
        ]);
    }

    public function reiniciar(): bool
    {
        return $this->update([
            'status' => 'pendente',
            'passo_atual' => 0,
            'iniciado_em' => null,
            'concluido_em' => null,
            'dados_adicionais' => array_merge($this->dados_adicionais ?? [], [
                'reiniciado_em' => now()->toISOString(),
                'reiniciado_vezes' => (($this->dados_adicionais['reiniciado_vezes'] ?? 0) + 1),
            ])
        ]);
    }

    public function registrarAcao(string $acao, array $dados = []): void
    {
        $this->update([
            'dados_adicionais' => array_merge($this->dados_adicionais ?? [], [
                'acao_' . $acao => [
                    'timestamp' => now()->toISOString(),
                    'dados' => $dados,
                ]
            ])
        ]);
    }

    // Scopes
    public function scopeDoUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeEmAndamento($query)
    {
        return $query->where('status', 'em_andamento');
    }

    public function scopeConcluidos($query)
    {
        return $query->where('status', 'concluido');
    }

    public function scopePulados($query)
    {
        return $query->where('status', 'pulado');
    }
}
