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
        Schema::create('execucao_etapas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acao_id')->constrained('acoes')->onDelete('cascade');
            $table->foreignId('etapa_fluxo_id')->constrained('etapa_fluxo')->onDelete('restrict');
            $table->foreignId('usuario_responsavel_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('status_id')->constrained('status')->onDelete('restrict');
            $table->foreignId('etapa_anterior_id')->nullable()->constrained('execucao_etapas')->onDelete('set null');
            
            $table->timestamp('data_inicio')->useCurrent()->comment('Início da execução');
            $table->timestamp('data_prazo')->nullable()->comment('Prazo para conclusão');
            $table->timestamp('data_conclusao')->nullable()->comment('Data de conclusão real');
            $table->integer('dias_em_atraso')->nullable()->default(0)->comment('Dias de atraso (calculado)');
            
            $table->text('observacoes')->nullable()->comment('Observações gerais');
            $table->text('justificativa')->nullable()->comment('Justificativa (para reprovação/devolução)');
            $table->string('motivo_transicao', 500)->nullable()->comment('Motivo da transição entre etapas');
            $table->json('dados_especificos')->nullable()->comment('Dados específicos da execução');
            $table->decimal('percentual_conclusao', 5, 2)->nullable()->default(0)->comment('Percentual concluído');
            $table->boolean('notificacao_enviada')->default(false)->comment('Se notificação foi enviada');
            
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->index(['acao_id']);
            $table->index(['etapa_fluxo_id']);
            $table->index(['status_id']);
            $table->index(['data_prazo']);
            $table->index(['data_conclusao']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('execucao_etapas');
    }
}; 