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
        Schema::create('cidades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ibge_id')->unique();
            $table->string('nome');
            $table->string('estado', 2)->default('PR');
            $table->string('nome_normalizado'); // Para busca otimizada sem acentos
            $table->timestamps();
            
            // Índices para busca rápida
            $table->index('nome');
            $table->index('nome_normalizado');
            $table->index(['estado', 'nome']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cidades');
    }
};
