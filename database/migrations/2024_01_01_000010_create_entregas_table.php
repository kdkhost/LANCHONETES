<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entregas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id')->unique();
            $table->unsignedBigInteger('entregador_id')->nullable();
            $table->enum('status', ['aguardando', 'aceito', 'coletado', 'em_rota', 'entregue', 'cancelado'])->default('aguardando');
            $table->decimal('distancia_km', 8, 3)->nullable();
            $table->integer('tempo_estimado_min')->nullable();
            $table->decimal('taxa_entrega', 10, 2)->default(0);
            $table->decimal('latitude_coleta', 10, 8)->nullable();
            $table->decimal('longitude_coleta', 11, 8)->nullable();
            $table->decimal('latitude_destino', 10, 8)->nullable();
            $table->decimal('longitude_destino', 11, 8)->nullable();
            $table->decimal('latitude_atual', 10, 8)->nullable();
            $table->decimal('longitude_atual', 11, 8)->nullable();
            $table->string('token_rastreamento', 64)->unique();
            $table->string('link_rastreamento_whatsapp')->nullable();
            $table->timestamp('aceito_em')->nullable();
            $table->timestamp('coletado_em')->nullable();
            $table->timestamp('entregue_em')->nullable();
            $table->timestamp('localizacao_atualizada_em')->nullable();
            $table->text('observacoes')->nullable();
            $table->json('rota_historico')->nullable();
            $table->timestamps();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->cascadeOnDelete();
            $table->foreign('entregador_id')->references('id')->on('funcionarios')->nullOnDelete();
            $table->index(['entregador_id', 'status']);
            $table->index('token_rastreamento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entregas');
    }
};
