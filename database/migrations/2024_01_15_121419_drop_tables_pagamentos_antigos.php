<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inscricaos', function (Blueprint $table) {
            $table->dropForeign(['cupom_desconto_id']);
            $table->dropForeign(['promocao_id']);
            $table->dropColumn(['cupom_desconto_id', 'promocao_id']);
        });
        Schema::dropIfExists('cupom_de_descontos');
        Schema::dropIfExists('lotes');
        Schema::dropIfExists('exibir_promocaos');
        Schema::dropIfExists('atividades_promocaos');
        Schema::dropIfExists('promocaos');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
