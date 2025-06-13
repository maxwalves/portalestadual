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
        Schema::create('template_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_exigencia_id')->constrained('grupo_exigencia')->onDelete('cascade');
            $table->foreignId('tipo_documento_id')->constrained('tipo_documentos')->onDelete('restrict');
            $table->string('nome')->comment('Nome do template');
            $table->text('descricao')->nullable()->comment('Descrição/instruções');
            $table->string('caminho_modelo_storage', 500)->nullable()->comment('Caminho do arquivo modelo');
            $table->string('exemplo_preenchido', 500)->nullable()->comment('Caminho de exemplo preenchido');
            $table->boolean('is_obrigatorio')->default(true)->comment('1=Obrigatório, 0=Opcional');
            $table->integer('ordem')->default(0)->comment('Ordem de apresentação');
            $table->text('instrucoes_preenchimento')->nullable()->comment('Instruções detalhadas');
            $table->json('validacoes_customizadas')->nullable()->comment('Validações específicas do template');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->index(['grupo_exigencia_id', 'ordem']);
            $table->index(['is_obrigatorio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_documentos');
    }
}; 