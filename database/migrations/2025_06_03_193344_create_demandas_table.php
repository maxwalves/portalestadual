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
        Schema::create('demandas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('descricao')->nullable();
            $table->string('prioridade_sam')->nullable();
            $table->unsignedBigInteger('termo_adesao_id');
            $table->unsignedBigInteger('cadastro_demanda_gms_id');
            $table->foreign('termo_adesao_id')->references('id')->on('termos_adesao');
            $table->foreign('cadastro_demanda_gms_id')->references('id')->on('cadastro_demanda_gms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandas');
    }
};
