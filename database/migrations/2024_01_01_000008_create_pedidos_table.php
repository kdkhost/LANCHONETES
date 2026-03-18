<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('numero', 20)->unique();
            $table->enum('status', [
                'aguardando_pagamento',
                'pagamento_aprovado',
                'confirmado',
                'em_preparo',
                'pronto',
                'saiu_para_entrega',
                'entregue',
                'cancelado',
                'recusado',
            ])->default('aguardando_pagamento');
            $table->enum('tipo_entrega', ['entrega', 'retirada'])->default('entrega');
            $table->unsignedBigInteger('endereco_id')->nullable();
            $table->string('endereco_cep', 9)->nullable();
            $table->string('endereco_logradouro', 200)->nullable();
            $table->string('endereco_numero', 20)->nullable();
            $table->string('endereco_complemento', 100)->nullable();
            $table->string('endereco_bairro', 100)->nullable();
            $table->string('endereco_cidade', 100)->nullable();
            $table->string('endereco_estado', 2)->nullable();
            $table->decimal('endereco_latitude', 10, 8)->nullable();
            $table->decimal('endereco_longitude', 11, 8)->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('taxa_entrega', 10, 2)->default(0);
            $table->decimal('desconto', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('observacoes')->nullable();
            $table->string('cupom_codigo', 50)->nullable();
            $table->integer('tempo_estimado_min')->nullable();
            $table->timestamp('confirmado_em')->nullable();
            $table->timestamp('entregue_em')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->text('motivo_cancelamento')->nullable();
            $table->unsignedBigInteger('entregador_id')->nullable();
            $table->string('link_rastreamento')->nullable();
            $table->json('historico_status')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->foreign('endereco_id')->references('id')->on('enderecos')->nullOnDelete();
            $table->foreign('entregador_id')->references('id')->on('funcionarios')->nullOnDelete();
            $table->index(['loja_id', 'status']);
            $table->index(['usuario_id', 'status']);
            $table->index('numero');
            $table->index('created_at');
        });

        Schema::create('itens_pedido', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('produto_id');
            $table->string('produto_nome', 150);
            $table->text('produto_descricao')->nullable();
            $table->decimal('produto_preco', 10, 2);
            $table->integer('quantidade');
            $table->decimal('subtotal', 10, 2);
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->cascadeOnDelete();
            $table->foreign('produto_id')->references('id')->on('produtos')->restrictOnDelete();
            $table->index('pedido_id');
        });

        Schema::create('itens_adicionais_pedido', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_pedido_id');
            $table->unsignedBigInteger('adicional_id');
            $table->string('adicional_nome', 100);
            $table->decimal('adicional_preco', 10, 2);
            $table->integer('quantidade')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            $table->foreign('item_pedido_id')->references('id')->on('itens_pedido')->cascadeOnDelete();
            $table->foreign('adicional_id')->references('id')->on('adicionais')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens_adicionais_pedido');
        Schema::dropIfExists('itens_pedido');
        Schema::dropIfExists('pedidos');
    }
};
