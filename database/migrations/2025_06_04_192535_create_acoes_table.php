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
        Schema::create('acoes', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->foreignId('demanda_id')->constrained('demandas')->onDelete('cascade');
            $table->string('projeto_sam')->nullable();
            $table->foreignId('tipo_fluxo_id')->constrained('tipo_fluxo')->onDelete('cascade');
            $table->decimal('valor_estimado', 15, 2)->nullable();
            $table->decimal('valor_contratado', 15, 2)->nullable();
            $table->string('localizacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acoes');
    }
};
