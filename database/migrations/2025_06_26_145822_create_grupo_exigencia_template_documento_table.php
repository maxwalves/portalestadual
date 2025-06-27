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
        Schema::create('grupo_exigencia_template_documento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_exigencia_id')->constrained('grupo_exigencia')->onDelete('cascade');
            $table->foreignId('template_documento_id')->constrained('template_documentos')->onDelete('cascade');
            $table->boolean('is_obrigatorio')->default(true)->comment('Se o template é obrigatório neste grupo');
            $table->integer('ordem')->default(0)->comment('Ordem de apresentação dentro do grupo');
            $table->text('observacoes')->nullable()->comment('Observações específicas para este grupo');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Índices
            $table->unique(['grupo_exigencia_id', 'template_documento_id'], 'uk_grupo_template');
            $table->index(['grupo_exigencia_id', 'ordem'], 'idx_grupo_ordem');
            $table->index('template_documento_id', 'idx_template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_exigencia_template_documento');
    }
};
