<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lojas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('slug', 150)->unique();
            $table->string('cnpj', 18)->nullable()->unique();
            $table->string('telefone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->text('descricao')->nullable();
            $table->string('cep', 9)->nullable();
            $table->string('logradouro', 200)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento', 100)->nullable();
            $table->string('bairro', 100)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('estado', 2)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('raio_entrega_km', 5, 2)->default(10.00);
            $table->string('cor_primaria', 7)->default('#FF6B35');
            $table->string('cor_secundaria', 7)->default('#2C3E50');
            $table->decimal('pedido_minimo', 10, 2)->default(0);
            $table->decimal('tempo_entrega_min', 5, 0)->default(30);
            $table->decimal('tempo_entrega_max', 5, 0)->default(60);
            $table->boolean('ativo')->default(true);
            $table->boolean('aceita_retirada')->default(true);
            $table->boolean('aceita_entrega')->default(true);
            $table->boolean('aceita_pagamento_entrega')->default(false);
            $table->enum('tipo_taxa_entrega', ['fixo', 'por_km', 'bairro', 'gratis'])->default('fixo');
            $table->decimal('taxa_entrega_fixa', 10, 2)->default(0);
            $table->decimal('taxa_por_km', 10, 2)->default(0);
            $table->decimal('km_gratis', 5, 2)->default(0);
            $table->string('chave_pix', 200)->nullable();
            $table->string('mercadopago_public_key', 200)->nullable();
            $table->text('mercadopago_access_token')->nullable();
            $table->string('evolution_instance', 100)->nullable();
            $table->boolean('notificacoes_whatsapp')->default(false);
            $table->json('horarios_funcionamento')->nullable();
            $table->json('configuracoes')->nullable();
            $table->decimal('avaliacao_media', 3, 2)->default(0);
            $table->integer('avaliacoes_total')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lojas');
    }
};
