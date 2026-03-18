<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avaliacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('loja_id');
            $table->integer('nota_loja')->nullable();
            $table->integer('nota_entrega')->nullable();
            $table->integer('nota_comida')->nullable();
            $table->text('comentario')->nullable();
            $table->json('tags')->nullable();
            $table->json('fotos')->nullable();
            $table->boolean('aprovado')->default(true);
            $table->boolean('anonimo')->default(false);
            $table->timestamps();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
            $table->index(['loja_id', 'aprovado']);
        });

        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('titulo', 150);
            $table->text('mensagem');
            $table->string('tipo', 50)->default('info');
            $table->string('icone', 50)->nullable();
            $table->string('url', 500)->nullable();
            $table->json('dados')->nullable();
            $table->boolean('lida')->default(false);
            $table->timestamp('lida_em')->nullable();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->index(['usuario_id', 'lida']);
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id');
            $table->string('titulo', 150)->nullable();
            $table->string('imagem');
            $table->string('url', 500)->nullable();
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamp('valido_de')->nullable();
            $table->timestamp('valido_ate')->nullable();
            $table->timestamps();

            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
            $table->index(['loja_id', 'ativo']);
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('notificacoes');
        Schema::dropIfExists('avaliacoes');
    }
};
