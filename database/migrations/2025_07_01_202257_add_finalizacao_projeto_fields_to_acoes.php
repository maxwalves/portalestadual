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
        Schema::table('acoes', function (Blueprint $table) {
            // Campos para controle de finalização do projeto
            $table->boolean('is_finalizado')->default(false)->comment('1=Projeto finalizado, 0=Em andamento');
            $table->timestamp('data_finalizacao')->nullable()->comment('Data em que o projeto foi finalizado');
            $table->foreignId('usuario_finalizacao_id')->nullable()->constrained('users')->onDelete('set null')->comment('Usuário que finalizou o projeto');
            $table->text('observacao_finalizacao')->nullable()->comment('Observações sobre a finalização');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acoes', function (Blueprint $table) {
            $table->dropForeign(['usuario_finalizacao_id']);
            $table->dropColumn(['is_finalizado', 'data_finalizacao', 'usuario_finalizacao_id', 'observacao_finalizacao']);
        });
    }
};
