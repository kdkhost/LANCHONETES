<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loja;
use Illuminate\Support\Facades\Log;

class GerenciarHorariosLojas extends Command
{
    protected $signature = 'lojas:gerenciar-horarios';
    protected $description = 'Abre e fecha lojas automaticamente com base nos horários configurados';

    public function handle(): int
    {
        $lojas = Loja::where('horario_automatico', true)
            ->whereNotNull('horario_abertura')
            ->whereNotNull('horario_fechamento')
            ->get();

        if ($lojas->isEmpty()) {
            $this->info('Nenhuma loja com horário automático configurado.');
            return Command::SUCCESS;
        }

        $agora = now();
        $horaAtual = $agora->format('H:i:s');
        $diaAtual = (int) $agora->dayOfWeek;

        $abertas = 0;
        $fechadas = 0;

        foreach ($lojas as $loja) {
            // Verificar se hoje é dia de funcionamento
            $diasFuncionamento = $loja->dias_funcionamento ?? [];
            $funcionaHoje = empty($diasFuncionamento) || in_array($diaAtual, $diasFuncionamento);

            if (!$funcionaHoje) {
                if ($loja->ativo) {
                    $loja->update(['ativo' => false]);
                    $fechadas++;
                    $this->warn("🔴 Loja '{$loja->nome}' fechada (não funciona hoje)");
                    Log::info("Loja {$loja->id} fechada automaticamente - não funciona hoje");
                }
                continue;
            }

            // Verificar horário de abertura
            if ($horaAtual >= $loja->horario_abertura && $horaAtual <= $loja->horario_fechamento) {
                if (!$loja->ativo) {
                    $loja->update(['ativo' => true]);
                    $abertas++;
                    $this->info("🟢 Loja '{$loja->nome}' aberta automaticamente");
                    Log::info("Loja {$loja->id} aberta automaticamente às {$horaAtual}");
                }
            } else {
                if ($loja->ativo) {
                    $loja->update(['ativo' => false]);
                    $fechadas++;
                    $this->warn("🔴 Loja '{$loja->nome}' fechada automaticamente");
                    Log::info("Loja {$loja->id} fechada automaticamente às {$horaAtual}");
                }
            }
        }

        $this->newLine();
        $this->info("✅ Processamento concluído:");
        $this->line("   • Lojas abertas: {$abertas}");
        $this->line("   • Lojas fechadas: {$fechadas}");
        $this->line("   • Total processadas: {$lojas->count()}");

        return Command::SUCCESS;
    }
}
