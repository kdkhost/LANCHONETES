<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->string('nome', 150);
            $table->string('slug', 150);
            $table->text('descricao')->nullable();
            $table->text('ingredientes')->nullable();
            $table->decimal('preco', 10, 2);
            $table->decimal('preco_promocional', 10, 2)->nullable();
            $table->decimal('peso_gramas', 10, 2)->nullable();
            $table->string('imagem_principal')->nullable();
            $table->json('imagens')->nullable();
            $table->integer('estoque')->nullable();
            $table->boolean('controla_estoque')->default(false);
            $table->boolean('ativo')->default(true);
            $table->boolean('disponivel')->default(true);
            $table->boolean('destaque')->default(false);
            $table->boolean('novo')->default(false);
            $table->integer('tempo_preparo_min')->default(15);
            $table->integer('ordem')->default(0);
            $table->integer('vendas_total')->default(0);
            $table->decimal('avaliacao_media', 3, 2)->default(0);
            $table->integer('avaliacoes_total')->default(0);
            $table->json('informacoes_nutricionais')->nullable();
            $table->json('alergicos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
            $table->foreign('categoria_id')->references('id')->on('categorias')->nullOnDelete();
            $table->unique(['loja_id', 'slug']);
            $table->index(['loja_id', 'ativo', 'disponivel']);
            $table->index(['loja_id', 'categoria_id']);
            $table->index('destaque');
        });

        Schema::create('grupos_adicionais', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('produto_id');
            $table->string('nome', 100);
            $table->text('descricao')->nullable();
            $table->integer('min_selecao')->default(0);
            $table->integer('max_selecao')->default(1);
            $table->boolean('obrigatorio')->default(false);
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->foreign('produto_id')->references('id')->on('produtos')->cascadeOnDelete();
        });

        Schema::create('adicionais', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grupo_id');
            $table->string('nome', 100);
            $table->text('descricao')->nullable();
            $table->decimal('preco', 10, 2)->default(0);
            $table->integer('estoque')->nullable();
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();

            $table->foreign('grupo_id')->references('id')->on('grupos_adicionais')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adicionais');
        Schema::dropIfExists('grupos_adicionais');
        Schema::dropIfExists('produtos');
    }
};
