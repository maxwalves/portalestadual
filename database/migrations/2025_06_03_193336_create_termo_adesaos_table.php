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
        Schema::create('termos_adesao', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->date('data_criacao');
            $table->string('path_arquivo')->nullable();
            $table->foreignId('organizacao_id')->constrained('organizacao');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('termos_adesao');
    }
};
