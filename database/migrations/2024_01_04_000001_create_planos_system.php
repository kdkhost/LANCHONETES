<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Tabela de Planos ─────────────────────────────────────────────────────
        Schema::create('planos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('slug', 100)->unique();
            $table->text('descricao')->nullable();
            $table->decimal('preco_mensal', 10, 2)->default(0);
            $table->decimal('preco_anual', 10, 2)->nullable();
            $table->json('recursos')->nullable(); // Array com recursos do plano
            $table->boolean('ativo')->default(true);
            $table->boolean('destaque')->default(false);
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        // ── Tabela de Assinaturas ───────────────────────────────────────────────
        Schema::create('assinaturas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id');
            $table->unsignedBigInteger('plano_id');
            $table->enum('status', ['trial', 'ativa', 'cancelada', 'suspensa', 'expirada'])->default('trial');
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->date('trial_expira_em')->nullable();
            $table->enum('periodo', ['mensal', 'anual'])->default('mensal');
            $table->decimal('valor_pago', 10, 2)->nullable();
            $table->string('metodo_pagamento', 50)->nullable(); // mercado_pago, stripe, etc
            $table->string('gateway_id', 100)->nullable(); // ID externo do pagamento
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
            $table->foreign('plano_id')->references('id')->on('planos')->cascadeOnDelete();
            $table->index(['loja_id', 'status']);
            $table->index('trial_expira_em');
        });

        // ── Adicionar campo plano_id na tabela lojas ───────────────────────────────
        Schema::table('lojas', function (Blueprint $table) {
            $table->unsignedBigInteger('plano_id')->nullable()->after('id');
            $table->date('trial_expira_em')->nullable()->after('plano_id');
            $table->boolean('trial_utilizado')->default(false)->after('trial_expira_em');
            $table->json('limitacoes_plano')->nullable()->after('configuracoes'); // Cache de limitações atuais
            
            $table->foreign('plano_id')->references('id')->on('planos')->nullOnDelete();
            $table->index('trial_expira_em');
        });

        // ── Inserir plano gratuito padrão ───────────────────────────────────────────
        \DB::table('planos')->insert([
            [
                'nome' => 'Plano Gratuita',
                'slug' => 'gratuita',
                'descricao' => 'Período de teste de 14 dias com todos os recursos disponíveis',
                'preco_mensal' => 0,
                'preco_anual' => 0,
                'recursos' => json_encode([
                    'produtos_ilimitados' => true,
                    'pedidos_ilimitados' => true,
                    'pagamento_online' => true,
                    'relatorios_completos' => true,
                    'estatisticas_visitas' => true,
                    'notificacoes_whatsapp' => true,
                    'cozinha_app' => true,
                    'nfe_integracao' => true,
                    'lgpd_compliance' => true,
                    'popups_marketing' => true,
                    'suporte_prioritario' => false,
                    'dominio_personalizado' => false,
                    'api_acesso' => false,
                ]),
                'ativo' => true,
                'destaque' => false,
                'ordem' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Plano Profissional',
                'slug' => 'profissional',
                'descricao' => 'Acesso completo a todos os recursos com suporte prioritário',
                'preco_mensal' => 97.00,
                'preco_anual' => 970.00,
                'recursos' => json_encode([
                    'produtos_ilimitados' => true,
                    'pedidos_ilimitados' => true,
                    'pagamento_online' => true,
                    'relatorios_completos' => true,
                    'estatisticas_visitas' => true,
                    'notificacoes_whatsapp' => true,
                    'cozinha_app' => true,
                    'nfe_integracao' => true,
                    'lgpd_compliance' => true,
                    'popups_marketing' => true,
                    'suporte_prioritario' => true,
                    'dominio_personalizado' => true,
                    'api_acesso' => true,
                ]),
                'ativo' => true,
                'destaque' => true,
                'ordem' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // ── Associar lojas existentes ao plano gratuito ───────────────────────────
        $planoGratuitoId = \DB::table('planos')->where('slug', 'gratuita')->value('id');
        if ($planoGratuitoId) {
            \DB::table('lojas')->update([
                'plano_id' => $planoGratuitoId,
                'trial_expira_em' => now()->addDays(14),
                'trial_utilizado' => false,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('lojas', function (Blueprint $table) {
            $table->dropForeign(['plano_id']);
            $table->dropColumn(['plano_id', 'trial_expira_em', 'trial_utilizado', 'limitacoes_plano']);
        });

        Schema::dropIfExists('assinaturas');
        Schema::dropIfExists('planos');
    }
};
