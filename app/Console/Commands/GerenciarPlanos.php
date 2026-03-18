<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PlanoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GerenciarPlanos extends Command
{
    protected $signature = 'planos:gerenciar';
    protected $description = 'Expirar trials, assinaturas e enviar notificações';

    public function handle(PlanoService $planoService): int
    {
        $this->info('🔍 Verificando planos e assinaturas...');
        
        // Expirar trials vencidos
        $trialsExpirados = $planoService->expirarTrialsVencidos();
        if ($trialsExpirados > 0) {
            $this->warn("⚠️  {$trialsExpirados} trials expirados e bloqueados");
        }

        // Expirar assinaturas vencidas
        $assinaturasExpiradas = $planoService->expirarAssinaturasVencidas();
        if ($assinaturasExpiradas > 0) {
            $this->warn("⚠️  {$assinaturasExpiradas} assinaturas expiradas");
        }

        // Notificar trials que expiram em breve
        $trialsExpirando = $planoService->verificarLojasComTrialExpirando();
        foreach ($trialsExpirando as $info) {
            $this->notificarTrialExpirando($info);
            $this->line("📧 Notificado: {$info['loja']->nome} ({$info['dias_restantes']} dias restantes)");
        }

        // Notificar assinaturas que expiram em breve
        $assinaturasExpirando = $planoService->verificarAssinaturasExpirando();
        foreach ($assinaturasExpirando as $info) {
            $this->notificarAssinaturaExpirando($info);
            $this->line("📧 Notificado: {$info['assinatura']->loja->nome} ({$info['dias_restantes']} dias restantes)");
        }

        $this->info('✅ Gerenciamento de planos concluído!');
        return Command::SUCCESS;
    }

    private function notificarTrialExpirando(array $info): void
    {
        $loja = $info['loja'];
        $diasRestantes = $info['dias_restantes'];

        // Aqui você pode implementar envio de email, WhatsApp, etc.
        // Por enquanto, apenas log
        Log::info("Trial expirando em {$diasRestantes} dias", [
            'loja_id' => $loja->id,
            'loja_nome' => $loja->nome,
            'email' => $loja->email,
            'dias_restantes' => $diasRestantes,
            'data_expiracao' => $info['data_expiracao'],
        ]);

        // Enviar email de trial expirando
        try {
            \Mail::to($loja->email)->send(new \App\Mail\TrialExpirando($loja, $diasRestantes));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de trial expirando', [
                'loja_id' => $loja->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function notificarAssinaturaExpirando(array $info): void
    {
        $assinatura = $info['assinatura'];
        $diasRestantes = $info['dias_restantes'];

        Log::info("Assinatura expirando em {$diasRestantes} dias", [
            'assinatura_id' => $assinatura->id,
            'loja_id' => $assinatura->loja_id,
            'loja_nome' => $assinatura->loja->nome,
            'plano' => $assinatura->plano->nome,
            'email' => $assinatura->loja->email,
            'dias_restantes' => $diasRestantes,
            'data_expiracao' => $info['data_expiracao'],
        ]);

        // Enviar email de assinatura expirando
        try {
            \Mail::to($assinatura->loja->email)->send(new \App\Mail\AssinaturaExpirando($assinatura, $diasRestantes));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de assinatura expirando', [
                'assinatura_id' => $assinatura->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
