<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos', 'gorjeta_entregador')) {
                $table->decimal('gorjeta_entregador', 10, 2)->default(0)->after('desconto');
            }
        });

        Schema::table('lojas', function (Blueprint $table) {
            if (!Schema::hasColumn('lojas', 'avaliacao_media_comida')) {
                $table->decimal('avaliacao_media_comida', 3, 2)->default(0)->after('avaliacao_media');
            }
            if (!Schema::hasColumn('lojas', 'avaliacao_media_entrega')) {
                $table->decimal('avaliacao_media_entrega', 3, 2)->default(0)->after('avaliacao_media_comida');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'gorjeta_entregador')) {
                $table->dropColumn('gorjeta_entregador');
            }
        });

        Schema::table('lojas', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('lojas', 'avaliacao_media_comida') ? 'avaliacao_media_comida' : null,
                Schema::hasColumn('lojas', 'avaliacao_media_entrega') ? 'avaliacao_media_entrega' : null,
            ]);
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
