<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    protected $fillable = [
        'nome',
        'titulo',
        'descricao',
        'passos',
        'ativo',
        'ordem',
        'target_role',
    ];

    protected $casts = [
        'passos' => 'array',
        'ativo' => 'boolean',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(TourUsuario::class);
    }

    public function usuariosCompletados(): HasMany
    {
        return $this->usuarios()->where('status', 'concluido');
    }

    public function usuariosPendentes(): HasMany
    {
        return $this->usuarios()->where('status', 'pendente');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeParaRole($query, string $role)
    {
        return $query->where(function($q) use ($role) {
            $q->where('target_role', $role)->orWhereNull('target_role');
        });
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    public function getPrimeiroPassoAttribute(): ?array
    {
        return $this->passos[0] ?? null;
    }

    public function getTotalPassosAttribute(): int
    {
        return count($this->passos);
    }

    public function getProgressoUsuario($userId): array
    {
        $tourUsuario = $this->usuarios()->where('user_id', $userId)->first();
        
        if (!$tourUsuario) {
            return [
                'status' => 'pendente',
                'passo_atual' => 0,
                'percentual' => 0,
                'iniciado_em' => null,
                'concluido_em' => null,
            ];
        }

        return [
            'status' => $tourUsuario->status,
            'passo_atual' => $tourUsuario->passo_atual,
            'percentual' => $this->total_passos > 0 
                ? round(($tourUsuario->passo_atual / $this->total_passos) * 100, 2)
                : 0,
            'iniciado_em' => $tourUsuario->iniciado_em,
            'concluido_em' => $tourUsuario->concluido_em,
        ];
    }

    public function podeIniciar($userId): bool
    {
        $progresso = $this->getProgressoUsuario($userId);
        return $progresso['status'] === 'pendente';
    }

    public function podeContinuar($userId): bool
    {
        $progresso = $this->getProgressoUsuario($userId);
        return $progresso['status'] === 'em_andamento';
    }

    public function estaConcluido($userId): bool
    {
        $progresso = $this->getProgressoUsuario($userId);
        return $progresso['status'] === 'concluido';
    }

    public function iniciarParaUsuario($userId): TourUsuario
    {
        $tourUsuario = $this->usuarios()->updateOrCreate(
            ['user_id' => $userId],
            [
                'status' => 'em_andamento',
                'passo_atual' => 0,
                'iniciado_em' => now(),
                'dados_adicionais' => [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            ]
        );

        return $tourUsuario;
    }

    public function avancarPasso($userId): bool
    {
        $tourUsuario = $this->usuarios()->where('user_id', $userId)->first();
        
        if (!$tourUsuario || $tourUsuario->status !== 'em_andamento') {
            return false;
        }

        $novoPasso = $tourUsuario->passo_atual + 1;
        
        if ($novoPasso >= $this->total_passos) {
            // Concluir tour
            $tourUsuario->update([
                'status' => 'concluido',
                'passo_atual' => $this->total_passos,
                'concluido_em' => now(),
                'dados_adicionais' => array_merge($tourUsuario->dados_adicionais ?? [], [
                    'concluido_em' => now()->toISOString(),
                ])
            ]);
        } else {
            // Avançar para próximo passo
            $tourUsuario->update([
                'passo_atual' => $novoPasso,
                'dados_adicionais' => array_merge($tourUsuario->dados_adicionais ?? [], [
                    'passo_' . $novoPasso . '_acessado_em' => now()->toISOString(),
                ])
            ]);
        }

        return true;
    }

    public function pular($userId): bool
    {
        $tourUsuario = $this->usuarios()->where('user_id', $userId)->first();
        
        if (!$tourUsuario) {
            return false;
        }

        $tourUsuario->update([
            'status' => 'pulado',
            'dados_adicionais' => array_merge($tourUsuario->dados_adicionais ?? [], [
                'pulado_em' => now()->toISOString(),
                'motivo_pulo' => 'manual',
            ])
        ]);

        return true;
    }

    public function reiniciarParaUsuario($userId): TourUsuario
    {
        $tourUsuario = $this->usuarios()->updateOrCreate(
            ['user_id' => $userId],
            [
                'status' => 'pendente',
                'passo_atual' => 0,
                'iniciado_em' => null,
                'concluido_em' => null,
                'dados_adicionais' => [
                    'reiniciado_em' => now()->toISOString(),
                    'reiniciado_vezes' => ($this->usuarios()->where('user_id', $userId)->count() + 1),
                ]
            ]
        );

        return $tourUsuario;
    }
}
