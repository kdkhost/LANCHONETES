<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id');
            $table->string('codigo', 50)->unique();
            $table->text('descricao')->nullable();
            $table->enum('tipo', ['percentual', 'fixo', 'frete_gratis'])->default('fixo');
            $table->decimal('valor', 10, 2)->default(0);
            $table->decimal('valor_minimo_pedido', 10, 2)->default(0);
            $table->decimal('desconto_maximo', 10, 2)->nullable();
            $table->integer('usos_maximos')->nullable();
            $table->integer('usos_por_usuario')->default(1);
            $table->integer('usos_realizados')->default(0);
            $table->timestamp('valido_de')->nullable();
            $table->timestamp('valido_ate')->nullable();
            $table->boolean('primeiro_pedido')->default(false);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
            $table->index(['loja_id', 'ativo']);
            $table->index('codigo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupons');
    }
};
