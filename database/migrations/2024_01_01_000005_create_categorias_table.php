<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id');
            $table->unsignedBigInteger('categoria_pai_id')->nullable();
            $table->string('nome', 100);
            $table->string('slug', 100);
            $table->text('descricao')->nullable();
            $table->string('imagem')->nullable();
            $table->string('icone', 50)->nullable();
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->boolean('destaque')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
            $table->foreign('categoria_pai_id')->references('id')->on('categorias')->nullOnDelete();
            $table->unique(['loja_id', 'slug']);
            $table->index(['loja_id', 'ativo', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
