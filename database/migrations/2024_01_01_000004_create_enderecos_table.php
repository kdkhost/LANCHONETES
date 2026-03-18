<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enderecos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('apelido', 50)->default('Casa');
            $table->string('destinatario', 150)->nullable();
            $table->string('cep', 9);
            $table->string('logradouro', 200);
            $table->string('numero', 20);
            $table->string('complemento', 100)->nullable();
            $table->string('bairro', 100);
            $table->string('cidade', 100);
            $table->string('estado', 2);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('principal')->default(false);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enderecos');
    }
};
