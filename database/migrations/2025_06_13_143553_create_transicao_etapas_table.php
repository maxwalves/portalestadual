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
        Schema::create('transicao_etapas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etapa_fluxo_origem_id')->constrained('etapa_fluxo')->onDelete('cascade')->comment('Etapa de origem');
            $table->foreignId('etapa_fluxo_destino_id')->constrained('etapa_fluxo')->onDelete('cascade')->comment('Etapa de destino');
            $table->foreignId('status_condicao_id')->nullable()->constrained('status')->onDelete('set null')->comment('Status que dispara a transição');
            $table->enum('condicao_tipo', ['STATUS', 'VALOR', 'CAMPO_CUSTOMIZADO', 'PRAZO_EXPIRADO', 'MULTIPLA', 'SEMPRE'])->default('STATUS')->comment('Tipo de condição');
            $table->enum('condicao_operador', ['=', '!=', '>', '<', '>=', '<=', 'IN', 'NOT IN', 'BETWEEN', 'CONTAINS'])->nullable()->comment('Operador da condição');
            $table->text('condicao_valor')->nullable()->comment('Valor para comparação (pode ser JSON)');
            $table->string('condicao_campo', 100)->nullable()->comment('Nome do campo para condições customizadas');
            $table->enum('logica_adicional', ['AND', 'OR'])->nullable()->comment('Lógica para múltiplas condições');
            $table->integer('prioridade')->default(0)->comment('Prioridade (maior = mais prioritário)');
            $table->string('descricao', 500)->nullable()->comment('Descrição da transição');
            $table->text('mensagem_transicao')->nullable()->comment('Mensagem exibida na transição');
            $table->boolean('is_ativo')->default(true)->comment('1=Ativo, 0=Inativo');
            $table->timestamps();

            $table->index(['etapa_fluxo_origem_id', 'prioridade'], 'idx_origem_prioridade');
            $table->index(['is_ativo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transicao_etapas');
    }
};
