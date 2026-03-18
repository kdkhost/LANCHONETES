<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lojas', function (Blueprint $table) {
            // ── Pop-up de saída ──────────────────────────────────────────────
            $table->boolean('popup_saida_ativo')->default(false)->after('configuracoes');
            $table->string('popup_saida_titulo', 150)->nullable()->after('popup_saida_ativo');
            $table->text('popup_saida_texto')->nullable()->after('popup_saida_titulo');
            $table->enum('popup_saida_desconto_tipo', ['percentual', 'fixo', 'frete_gratis'])->default('percentual')->after('popup_saida_texto');
            $table->decimal('popup_saida_desconto_valor', 8, 2)->default(10)->after('popup_saida_desconto_tipo');
            $table->string('popup_saida_cupom', 50)->nullable()->after('popup_saida_desconto_valor');
            $table->string('popup_saida_imagem')->nullable()->after('popup_saida_cupom');
            $table->integer('popup_saida_validade_min')->default(30)->after('popup_saida_imagem');

            // ── Pop-up de promoção / relâmpago ───────────────────────────────
            $table->boolean('popup_promo_ativo')->default(false)->after('popup_saida_validade_min');
            $table->string('popup_promo_titulo', 150)->nullable()->after('popup_promo_ativo');
            $table->text('popup_promo_texto')->nullable()->after('popup_promo_titulo');
            $table->string('popup_promo_imagem')->nullable()->after('popup_promo_texto');
            $table->integer('popup_promo_delay_seg')->default(5)->after('popup_promo_imagem');
            $table->timestamp('popup_promo_expira_em')->nullable()->after('popup_promo_delay_seg');
            $table->string('popup_promo_url', 500)->nullable()->after('popup_promo_expira_em');

            // ── NFe / Nota Fiscal ────────────────────────────────────────────
            $table->boolean('nfe_ativo')->default(false)->after('popup_promo_url');
            $table->enum('nfe_ambiente', ['homologacao', 'producao'])->default('homologacao')->after('nfe_ativo');
            $table->string('nfe_token', 200)->nullable()->after('nfe_ambiente');
            $table->string('nfe_cnpj_emitente', 20)->nullable()->after('nfe_token');
            $table->string('nfe_razao_social', 200)->nullable()->after('nfe_cnpj_emitente');
            $table->string('nfe_serie', 10)->default('1')->after('nfe_razao_social');
            $table->integer('nfe_numero_atual')->default(1)->after('nfe_serie');
            $table->string('nfe_provedor', 50)->default('focusnfe')->after('nfe_numero_atual');

            // ── LGPD ─────────────────────────────────────────────────────────
            $table->text('lgpd_texto_cookies')->nullable()->after('nfe_provedor');
            $table->string('lgpd_url_politica', 500)->nullable()->after('lgpd_texto_cookies');
            $table->string('lgpd_url_termos', 500)->nullable()->after('lgpd_url_politica');

            // ── Cozinha / Notificações ───────────────────────────────────────
            $table->boolean('cozinha_ativo')->default(false)->after('lgpd_url_termos');
            $table->string('cozinha_pin', 10)->nullable()->after('cozinha_ativo');

            // ── WhatsApp templates ───────────────────────────────────────────
            $table->json('wpp_templates')->nullable()->after('cozinha_pin');
        });

        // ── Tabela de notas fiscais ──────────────────────────────────────────
        Schema::create('notas_fiscais', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('loja_id');
            $table->string('numero', 20)->nullable();
            $table->string('serie', 10)->default('1');
            $table->enum('tipo', ['nfce', 'nfe'])->default('nfce');
            $table->enum('ambiente', ['homologacao', 'producao'])->default('homologacao');
            $table->enum('status', ['pendente', 'processando', 'autorizada', 'cancelada', 'rejeitada'])->default('pendente');
            $table->string('chave_acesso', 50)->nullable();
            $table->string('protocolo', 30)->nullable();
            $table->string('url_danfe', 500)->nullable();
            $table->string('xml_path')->nullable();
            $table->text('motivo_rejeicao')->nullable();
            $table->json('dados_emissao')->nullable();
            $table->json('resposta_sefaz')->nullable();
            $table->decimal('valor_total', 10, 2)->nullable();
            $table->timestamp('emitida_em')->nullable();
            $table->timestamp('cancelada_em')->nullable();
            $table->timestamps();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->cascadeOnDelete();
            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
            $table->index(['loja_id', 'status']);
            $table->index('chave_acesso');
        });

        // ── Tabela de aceitação LGPD ─────────────────────────────────────────
        Schema::create('lgpd_aceites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->unsignedBigInteger('loja_id');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('versao', 20)->default('1.0');
            $table->enum('tipo', ['cookies', 'termos', 'ambos'])->default('ambos');
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->nullOnDelete();
            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lgpd_aceites');
        Schema::dropIfExists('notas_fiscais');
        Schema::table('lojas', function (Blueprint $table) {
            $table->dropColumn([
                'popup_saida_ativo', 'popup_saida_titulo', 'popup_saida_texto',
                'popup_saida_desconto_tipo', 'popup_saida_desconto_valor',
                'popup_saida_cupom', 'popup_saida_imagem', 'popup_saida_validade_min',
                'popup_promo_ativo', 'popup_promo_titulo', 'popup_promo_texto',
                'popup_promo_imagem', 'popup_promo_delay_seg', 'popup_promo_expira_em',
                'popup_promo_url', 'nfe_ativo', 'nfe_ambiente', 'nfe_token',
                'nfe_cnpj_emitente', 'nfe_razao_social', 'nfe_serie', 'nfe_numero_atual',
                'nfe_provedor', 'lgpd_texto_cookies', 'lgpd_url_politica', 'lgpd_url_termos',
                'cozinha_ativo', 'cozinha_pin', 'wpp_templates',
            ]);
        });
    }
};
