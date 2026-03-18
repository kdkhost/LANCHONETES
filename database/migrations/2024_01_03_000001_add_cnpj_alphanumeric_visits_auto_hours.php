<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Atualizar lojas para CNPJ alfanumérico e horários automáticos ────
        Schema::table('lojas', function (Blueprint $table) {
            // CNPJ alfanumérico (novo formato governo 06/2026)
            $table->string('cnpj', 18)->nullable()->change();
            
            // Horários automáticos de abertura/fechamento
            $table->boolean('horario_automatico')->default(false)->after('ativo');
            $table->time('horario_abertura')->nullable()->after('horario_automatico');
            $table->time('horario_fechamento')->nullable()->after('horario_abertura');
            $table->json('dias_funcionamento')->nullable()->after('horario_fechamento')
                ->comment('Array com dias da semana: [0=dom, 1=seg, ..., 6=sab]');
        });

        // ── Tabela de visitas de lojas ──────────────────────────────────────
        Schema::create('visitas_lojas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referer', 500)->nullable();
            $table->string('device_type', 20)->nullable(); // mobile, desktop, tablet
            $table->timestamp('visitado_em');
            
            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->nullOnDelete();
            $table->index(['loja_id', 'visitado_em']);
            $table->index('ip');
        });

        // ── Tabela de visitas de produtos ───────────────────────────────────
        Schema::create('visitas_produtos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('produto_id');
            $table->unsignedBigInteger('loja_id');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referer', 500)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->timestamp('visitado_em');
            
            $table->foreign('produto_id')->references('id')->on('produtos')->cascadeOnDelete();
            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->nullOnDelete();
            $table->index(['produto_id', 'visitado_em']);
            $table->index(['loja_id', 'visitado_em']);
        });

        // ── Tabela de visitas de categorias ─────────────────────────────────
        Schema::create('visitas_categorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categoria_id');
            $table->unsignedBigInteger('loja_id');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referer', 500)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->timestamp('visitado_em');
            
            $table->foreign('categoria_id')->references('id')->on('categorias')->cascadeOnDelete();
            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->nullOnDelete();
            $table->index(['categoria_id', 'visitado_em']);
            $table->index(['loja_id', 'visitado_em']);
        });

        // ── Contador agregado de visitas (cache/performance) ────────────────
        Schema::create('contadores_visitas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // loja, produto, categoria
            $table->unsignedBigInteger('entidade_id');
            $table->date('data');
            $table->unsignedInteger('total_visitas')->default(0);
            $table->unsignedInteger('visitas_unicas')->default(0); // IPs únicos
            $table->timestamps();
            
            $table->unique(['tipo', 'entidade_id', 'data']);
            $table->index(['tipo', 'entidade_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contadores_visitas');
        Schema::dropIfExists('visitas_categorias');
        Schema::dropIfExists('visitas_produtos');
        Schema::dropIfExists('visitas_lojas');
        
        Schema::table('lojas', function (Blueprint $table) {
            $table->dropColumn([
                'horario_automatico',
                'horario_abertura',
                'horario_fechamento',
                'dias_funcionamento',
            ]);
        });
    }
};
