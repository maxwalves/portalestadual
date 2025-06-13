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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('execucao_etapa_id')->constrained('execucao_etapas')->onDelete('cascade');
            $table->foreignId('template_documento_id')->nullable()->constrained('template_documentos')->onDelete('set null');
            $table->foreignId('tipo_documento_id')->constrained('tipo_documentos')->onDelete('restrict');
            $table->foreignId('usuario_upload_id')->constrained('users')->onDelete('restrict');
            
            // Informações do arquivo
            $table->string('nome_arquivo', 500)->comment('Nome original do arquivo');
            $table->string('nome_arquivo_sistema', 500)->comment('Nome do arquivo no sistema');
            $table->bigInteger('tamanho_bytes')->comment('Tamanho em bytes');
            $table->string('mime_type', 100)->nullable()->comment('Tipo MIME do arquivo');
            $table->string('hash_arquivo', 64)->comment('Hash SHA256 para validação');
            $table->string('caminho_storage', 1000)->comment('Caminho no storage');
            
            // Controle de versão
            $table->integer('versao')->default(1)->comment('Versão do documento');
            $table->foreignId('documento_pai_id')->nullable()->constrained('documentos')->onDelete('set null');
            
            // Status e validação
            $table->enum('status_documento', ['PENDENTE', 'EM_ANALISE', 'APROVADO', 'REPROVADO', 'EXPIRADO'])->default('PENDENTE');
            $table->boolean('is_assinado')->default(false)->comment('1=Assinado digitalmente');
            $table->timestamp('data_upload')->useCurrent()->comment('Data/hora do upload');
            $table->date('data_validade')->nullable()->comment('Data de validade do documento');
            
            // Observações e metadados
            $table->text('observacoes')->nullable()->comment('Observações sobre o documento');
            $table->text('motivo_reprovacao')->nullable()->comment('Motivo da reprovação (se aplicável)');
            $table->json('metadata')->nullable()->comment('Metadados adicionais');
            
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            // Índices
            $table->index(['execucao_etapa_id']);
            $table->index(['tipo_documento_id']);
            $table->index(['usuario_upload_id']);
            $table->index(['hash_arquivo']);
            $table->index(['status_documento']);
            $table->index(['data_upload']);
            $table->index(['execucao_etapa_id', 'tipo_documento_id', 'versao']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
}; 