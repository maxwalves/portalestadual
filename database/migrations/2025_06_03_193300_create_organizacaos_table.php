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
        Schema::create('organizacao', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255);
            $table->enum('tipo', ['PARANACIDADE', 'SECID', 'SEED', 'SESA', 'SESP', 'EMPRESA', 'OUTRO']);
            $table->string('cnpj', 18)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->text('endereco')->nullable();
            $table->string('responsavel_nome', 255)->nullable();
            $table->string('responsavel_cargo', 100)->nullable();
            $table->boolean('is_ativo')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->unique('cnpj');
            $table->index('tipo');
            $table->index('is_ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizacao');
    }
};
