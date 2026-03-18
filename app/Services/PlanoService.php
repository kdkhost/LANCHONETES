<?php

namespace App\Services;

use App\Models\Loja;
use App\Models\Plano;
use App\Models\Assinatura;
use Illuminate\Support\Facades\Log;

class PlanoService
{
    public function iniciarTrial(Loja $loja): Assinatura
    {
        if ($loja->trial_utilizado) {
            throw new \Exception('Esta loja já utilizou o período de teste.');
        }

        $planoGratuito = Plano::where('slug', 'gratuita')->firstOrFail();
        
        // Criar assinatura de trial
        $assinatura = Assinatura::create([
            'loja_id' => $loja->id,
            'plano_id' => $planoGratuito->id,
            'status' => 'trial',
            'data_inicio' => now(),
            'trial_expira_em' => now()->addDays(14),
            'periodo' => 'mensal',
        ]);

        // Atualizar loja
        $loja->update([
            'plano_id' => $planoGratuito->id,
            'trial_expira_em' => now()->addDays(14),
            'trial_utilizado' => false, // Ainda não utilizado
        ]);

        Log::info("Trial iniciado para loja {$loja->id}", [
            'loja' => $loja->nome,
            'trial_expira_em' => $loja->trial_expira_em,
        ]);

        return $assinatura;
    }

    public function expirarTrial(Loja $loja): void
    {
        if (!$loja->estaEmTrial()) {
            return;
        }

        // Marcar trial como utilizado
        $loja->update(['trial_utilizado' => true]);

        // Atualizar assinatura para expirada
        $assinatura = $loja->assinatura;
        if ($assinatura) {
            $assinatura->update(['status' => 'expirada']);
        }

        // Atualizar limitações da loja
        $this->atualizarLimitacoesLoja($loja);

        // Enviar email de trial expirado
        try {
            \Mail::to($loja->email)->send(new \App\Mail\TrialExpirado($loja));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de trial expirado', [
                'loja_id' => $loja->id,
                'error' => $e->getMessage(),
            ]);
        }

        Log::info("Trial expirado para loja {$loja->id}", [
            'loja' => $loja->nome,
        ]);
    }

    public function criarAssinatura(Loja $loja, Plano $plano, array $dados): Assinatura
    {
        // Cancelar assinatura anterior se existir
        $assinaturaAnterior = $loja->assinatura;
        if ($assinaturaAnterior) {
            $assinaturaAnterior->update(['status' => 'cancelada']);
        }

        // Criar nova assinatura
        $assinatura = Assinatura::create([
            'loja_id' => $loja->id,
            'plano_id' => $plano->id,
            'status' => 'ativa',
            'data_inicio' => now(),
            'data_fim' => $dados['periodo'] === 'anual' ? now()->addYear() : now()->addMonth(),
            'periodo' => $dados['periodo'],
            'valor_pago' => $dados['valor_pago'],
            'metodo_pagamento' => $dados['metodo_pagamento'] ?? null,
            'gateway_id' => $dados['gateway_id'] ?? null,
            'notas' => $dados['notas'] ?? null,
        ]);

        // Atualizar loja
        $loja->update([
            'plano_id' => $plano->id,
            'trial_utilizado' => true, // Marca trial como utilizado
        ]);

        // Atualizar limitações da loja
        $this->atualizarLimitacoesLoja($loja);

        Log::info("Nova assinatura criada para loja {$loja->id}", [
            'loja' => $loja->nome,
            'plano' => $plano->nome,
            'periodo' => $dados['periodo'],
        ]);

        return $assinatura;
    }

    public function cancelarAssinatura(Loja $loja): void
    {
        $assinatura = $loja->assinatura;
        if (!$assinatura) {
            throw new \Exception('Nenhuma assinatura ativa encontrada.');
        }

        $assinatura->update(['status' => 'cancelada']);
        $this->atualizarLimitacoesLoja($loja);

        Log::info("Assinatura cancelada para loja {$loja->id}", [
            'loja' => $loja->nome,
        ]);
    }

    public function atualizarLimitacoesLoja(Loja $loja): void
    {
        $limitacoes = [
            'pode_criar_produtos' => $loja->podeCriarProdutos(),
            'pode_configurar_pagamento' => $loja->podeConfigurarPagamento(),
            'pode_vender' => $loja->podeVender(),
            'trial_ativo' => $loja->estaEmTrial(),
            'assinatura_ativa' => $loja->assinatura?->estaAtiva() ?? false,
            'status_plano' => $loja->status_plano,
            'dias_restantes_trial' => $loja->dias_restantes_trial,
        ];

        $loja->update(['limitacoes_plano' => $limitacoes]);
    }

    public function verificarLojasComTrialExpirando(): array
    {
        $lojas = Loja::where('trial_expira_em', '<=', now()->addDays(3))
            ->where('trial_expira_em', '>', now())
            ->where('trial_utilizado', false)
            ->with(['assinatura', 'plano'])
            ->get();

        return $lojas->map(function ($loja) {
            return [
                'loja' => $loja,
                'dias_restantes' => $loja->dias_restantes_trial,
                'data_expiracao' => $loja->trial_expira_em,
            ];
        })->toArray();
    }

    public function expirarTrialsVencidos(): int
    {
        $lojas = Loja::where('trial_expira_em', '<', now())
            ->where('trial_utilizado', false)
            ->get();

        $count = 0;
        foreach ($lojas as $loja) {
            $this->expirarTrial($loja);
            $count++;
        }

        return $count;
    }

    public function verificarAssinaturasExpirando(): array
    {
        $assinaturas = Assinatura::where('data_fim', '<=', now()->addDays(7))
            ->where('data_fim', '>', now())
            ->where('status', 'ativa')
            ->with(['loja', 'plano'])
            ->get();

        return $assinaturas->map(function ($assinatura) {
            return [
                'assinatura' => $assinatura,
                'dias_restantes' => $assinatura->dias_restantes_assinatura,
                'data_expiracao' => $assinatura->data_fim,
            ];
        })->toArray();
    }

    public function expirarAssinaturasVencidas(): int
    {
        $assinaturas = Assinatura::where('data_fim', '<', now())
            ->where('status', 'ativa')
            ->get();

        $count = 0;
        foreach ($assinaturas as $assinatura) {
            $assinatura->update(['status' => 'expirada']);
            $this->atualizarLimitacoesLoja($assinatura->loja);
            $count++;
        }

        return $count;
    }
}
