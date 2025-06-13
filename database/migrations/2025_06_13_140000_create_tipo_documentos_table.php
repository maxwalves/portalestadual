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
        Schema::create('tipo_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique()->comment('Código único do tipo');
            $table->string('nome')->comment('Nome do tipo de documento');
            $table->text('descricao')->nullable()->comment('Descrição do tipo');
            $table->string('extensoes_permitidas')->nullable()->comment('Extensões permitidas (ex: pdf,doc,docx)');
            $table->integer('tamanho_maximo_mb')->default(10)->comment('Tamanho máximo em MB');
            $table->boolean('requer_assinatura')->default(false)->comment('1=Requer assinatura digital');
            $table->string('categoria')->nullable()->comment('Categoria do documento (PROJETO, FINANCEIRO, LICENCA, etc.)');
            $table->boolean('is_ativo')->default(true)->comment('1=Ativo, 0=Inativo');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->index(['categoria']);
            $table->index(['is_ativo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_documentos');
    }
}; 