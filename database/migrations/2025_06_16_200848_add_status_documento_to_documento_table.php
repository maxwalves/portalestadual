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
        Schema::table('documentos', function (Blueprint $table) {
            // Verificar se as colunas já existem antes de adicionar
            if (!Schema::hasColumn('documentos', 'status_documento')) {
                $table->enum('status_documento', ['PENDENTE', 'EM_ANALISE', 'APROVADO', 'REPROVADO'])
                      ->default('PENDENTE')
                      ->after('observacoes')
                      ->comment('Status do documento');
            }
            
            if (!Schema::hasColumn('documentos', 'motivo_reprovacao')) {
                $table->text('motivo_reprovacao')
                      ->nullable()
                      ->after('status_documento')
                      ->comment('Motivo da reprovação do documento');
            }
            
            if (!Schema::hasColumn('documentos', 'data_aprovacao')) {
                $table->timestamp('data_aprovacao')
                      ->nullable()
                      ->after('motivo_reprovacao')
                      ->comment('Data de aprovação do documento');
            }
            
            if (!Schema::hasColumn('documentos', 'usuario_aprovacao_id')) {
                $table->foreignId('usuario_aprovacao_id')
                      ->nullable()
                      ->after('data_aprovacao')
                      ->constrained('users')
                      ->onDelete('set null')
                      ->comment('Usuário que aprovou/reprovou o documento');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropColumn([
                'status_documento',
                'motivo_reprovacao', 
                'data_aprovacao',
                'usuario_aprovacao_id'
            ]);
        });
    }
};
