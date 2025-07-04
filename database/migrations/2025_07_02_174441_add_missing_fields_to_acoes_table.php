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
            // Status da ação
            $table->enum('status', [
                'PLANEJAMENTO', 
                'EM_EXECUCAO', 
                'PARALISADA', 
                'CONCLUIDA', 
                'CANCELADA',
                'FINALIZADO'
            ])->default('PLANEJAMENTO')->comment('Status da ação');
            
            // Código de referência único
            $table->string('codigo_referencia', 100)->nullable()->unique()->comment('Código único de referência da obra');
            
            // Campos financeiros
            $table->decimal('valor_executado', 15, 2)->nullable()->default(0.00)->comment('Valor já executado');
            $table->decimal('percentual_execucao', 5, 2)->nullable()->default(0.00)->comment('Percentual de execução física');
            
            // Coordenadas geográficas
            $table->decimal('coordenadas_lat', 10, 8)->nullable()->comment('Latitude');
            $table->decimal('coordenadas_lng', 11, 8)->nullable()->comment('Longitude');
            
            // Datas de planejamento e execução
            $table->date('data_inicio_previsto')->nullable()->comment('Data prevista de início');
            $table->date('data_fim_previsto')->nullable()->comment('Data prevista de término');
            $table->date('data_inicio_real')->nullable()->comment('Data real de início');
            $table->date('data_fim_real')->nullable()->comment('Data real de término');
            
            // Índices para performance
            $table->index('status');
            $table->index(['data_inicio_previsto', 'data_fim_previsto']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acoes', function (Blueprint $table) {
            $table->dropIndex(['acoes_status_index']);
            $table->dropIndex(['acoes_data_inicio_previsto_data_fim_previsto_index']);
            $table->dropColumn([
                'status',
                'codigo_referencia', 
                'valor_executado',
                'percentual_execucao',
                'coordenadas_lat',
                'coordenadas_lng',
                'data_inicio_previsto',
                'data_fim_previsto',
                'data_inicio_real',
                'data_fim_real'
            ]);
        });
    }
};
