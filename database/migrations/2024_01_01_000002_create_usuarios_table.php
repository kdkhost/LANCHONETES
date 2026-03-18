<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('email', 150)->unique();
            $table->timestamp('email_verificado_em')->nullable();
            $table->string('senha');
            $table->string('telefone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('cpf', 14)->nullable()->unique();
            $table->date('data_nascimento')->nullable();
            $table->enum('genero', ['masculino', 'feminino', 'outro', 'prefiro_nao_informar'])->nullable();
            $table->string('foto_perfil')->nullable();
            $table->enum('role', ['super_admin', 'admin', 'gerente', 'atendente', 'cozinheiro', 'entregador', 'cliente'])->default('cliente');
            $table->unsignedBigInteger('loja_id')->nullable();
            $table->boolean('ativo')->default(true);
            $table->string('token_redefinicao')->nullable();
            $table->timestamp('token_redefinicao_expira_em')->nullable();
            $table->timestamp('ultimo_acesso_em')->nullable();
            $table->string('dispositivo_push_token')->nullable();
            $table->json('preferencias')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('loja_id')->references('id')->on('lojas')->nullOnDelete();
            $table->index(['role', 'ativo']);
            $table->index('loja_id');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('usuarios');
    }
};
