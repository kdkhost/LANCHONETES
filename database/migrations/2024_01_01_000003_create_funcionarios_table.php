<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('loja_id');
            $table->enum('tipo', ['funcionario', 'freelancer', 'autonomo', 'terceirizado'])->default('funcionario');
            $table->string('cargo', 100)->nullable();
            $table->decimal('salario', 10, 2)->nullable();
            $table->date('data_admissao')->nullable();
            $table->date('data_demissao')->nullable();
            $table->boolean('e_entregador')->default(false);
            $table->string('veiculo', 100)->nullable();
            $table->string('placa_veiculo', 10)->nullable();
            $table->string('cnh', 11)->nullable();
            $table->decimal('latitude_atual', 10, 8)->nullable();
            $table->decimal('longitude_atual', 11, 8)->nullable();
            $table->timestamp('localizacao_atualizada_em')->nullable();
            $table->boolean('disponivel_entregas')->default(false);
            $table->boolean('ativo')->default(true);
            $table->json('configuracoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
            $table->index(['loja_id', 'e_entregador', 'disponivel_entregas']);
            $table->index(['loja_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funcionarios');
    }
};
