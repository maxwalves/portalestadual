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
        Schema::create('etapa_status_opcoes', function (Blueprint $table) {
            $table->foreignId('etapa_fluxo_id')->constrained('etapa_fluxo')->onDelete('cascade')->comment('Etapa do fluxo');
            $table->foreignId('status_id')->constrained('status')->onDelete('cascade')->comment('Status disponível');
            $table->integer('ordem')->default(0)->comment('Ordem de exibição');
            $table->boolean('is_padrao')->default(false)->comment('1=Status padrão da etapa');
            $table->boolean('mostra_para_responsavel')->default(true)->comment('1=Visível ao responsável');
            $table->boolean('requer_justificativa')->default(false)->comment('1=Requer justificativa');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['etapa_fluxo_id', 'status_id']);
            $table->index(['etapa_fluxo_id', 'ordem'], 'idx_etapa_ordem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etapa_status_opcoes');
    }
};
