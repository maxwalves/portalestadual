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
        Schema::create('etapa_fluxo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_fluxo_id')->nullable();
            $table->unsignedBigInteger('modulo_id');
            $table->unsignedBigInteger('grupo_exigencia_id')->nullable();
            $table->unsignedBigInteger('organizacao_solicitante_id');
            $table->unsignedBigInteger('organizacao_executora_id');
            $table->string('nome_etapa', 255);
            $table->text('descricao_customizada')->nullable();
            $table->integer('ordem_execucao')->nullable();
            $table->integer('prazo_dias')->default(5);
            $table->enum('tipo_prazo', ['UTEIS', 'CORRIDOS'])->default('UTEIS');
            $table->boolean('is_obrigatoria')->default(1);
            $table->boolean('permite_pular')->default(0);
            $table->boolean('permite_retorno')->default(1);
            $table->enum('tipo_etapa', ['SEQUENCIAL', 'CONDICIONAL'])->default('SEQUENCIAL');
            $table->json('configuracoes')->nullable();
            $table->timestamps();

            $table->foreign('tipo_fluxo_id')->references('id')->on('tipo_fluxo')->onDelete('set null');
            $table->foreign('modulo_id')->references('id')->on('modulo')->onDelete('restrict');
            $table->foreign('grupo_exigencia_id')->references('id')->on('grupo_exigencia')->onDelete('set null');
            $table->foreign('organizacao_solicitante_id')->references('id')->on('organizacao')->onDelete('restrict');
            $table->foreign('organizacao_executora_id')->references('id')->on('organizacao')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etapa_fluxo');
    }
};
