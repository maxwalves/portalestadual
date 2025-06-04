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
        Schema::create('cadastro_demanda_gms', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('descricao', 255)->nullable();
            $table->string('codigoGMS', 100)->nullable();
            $table->string('protocolo', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cadastro_demanda_gms');
    }
};
