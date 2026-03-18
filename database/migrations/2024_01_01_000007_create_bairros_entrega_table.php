<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bairros_entrega', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id');
            $table->string('nome', 100);
            $table->string('cidade', 100);
            $table->string('estado', 2)->default('SP');
            $table->decimal('taxa', 10, 2)->default(0);
            $table->integer('tempo_estimado_min')->default(30);
            $table->integer('tempo_estimado_max')->default(60);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
            $table->index(['loja_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bairros_entrega');
    }
};
