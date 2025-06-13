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
        Schema::create('historico_etapas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('execucao_etapa_id')->constrained('execucao_etapas')->onDelete('cascade')->comment('Execução relacionada');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict')->comment('Usuário que realizou a ação');
            $table->foreignId('status_anterior_id')->nullable()->constrained('status')->onDelete('set null')->comment('Status anterior');
            $table->foreignId('status_novo_id')->nullable()->constrained('status')->onDelete('set null')->comment('Novo status');
            $table->string('acao', 100)->comment('Tipo de ação realizada');
            $table->string('descricao_acao', 500)->nullable()->comment('Descrição da ação');
            $table->text('observacao')->nullable()->comment('Observações do usuário');
            $table->json('dados_alterados')->nullable()->comment('Dados que foram alterados');
            $table->string('ip_usuario', 45)->nullable()->comment('IP do usuário');
            $table->string('user_agent', 500)->nullable()->comment('Browser/sistema do usuário');
            $table->timestamp('data_acao')->useCurrent()->comment('Data/hora da ação');

            $table->index(['execucao_etapa_id']);
            $table->index(['usuario_id']);
            $table->index(['data_acao']);
            $table->index(['acao']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historico_etapas');
    }
};
