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
        Schema::create('modulo', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255);
            $table->enum('tipo', ['ENVIO', 'ANALISE', 'ASSINATURA']);
            $table->text('descricao')->nullable();
            $table->string('icone', 50)->nullable();
            $table->string('cor', 7)->nullable();
            $table->json('campos_customizaveis')->nullable();
            $table->json('configuracao_padrao')->nullable();
            $table->boolean('is_ativo')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modulo');
    }
};
