<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->string('metodo', 50);
            $table->enum('status', ['pendente', 'aprovado', 'recusado', 'cancelado', 'reembolsado', 'em_analise'])->default('pendente');
            $table->decimal('valor', 10, 2);
            $table->string('mp_payment_id')->nullable();
            $table->string('mp_preference_id')->nullable();
            $table->string('mp_status')->nullable();
            $table->string('mp_status_detail')->nullable();
            $table->string('mp_transaction_id')->nullable();
            $table->string('pix_qr_code')->nullable();
            $table->text('pix_qr_code_base64')->nullable();
            $table->timestamp('pix_expiracao')->nullable();
            $table->integer('parcelas')->default(1);
            $table->string('bandeira_cartao', 30)->nullable();
            $table->string('ultimos_digitos_cartao', 4)->nullable();
            $table->string('titular_cartao', 150)->nullable();
            $table->string('comprovante')->nullable();
            $table->json('resposta_gateway')->nullable();
            $table->timestamp('pago_em')->nullable();
            $table->timestamps();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->cascadeOnDelete();
            $table->index(['pedido_id', 'status']);
            $table->index('mp_payment_id');
            $table->index('mp_preference_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
