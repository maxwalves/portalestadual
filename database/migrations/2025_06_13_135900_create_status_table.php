<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('status', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique()->comment('Código único do status');
            $table->string('nome', 100)->comment('Nome do status');
            $table->text('descricao')->nullable()->comment('Descrição do status');
            $table->enum('categoria', ['EXECUCAO', 'DOCUMENTO', 'GERAL'])->default('EXECUCAO')->comment('Categoria do status');
            $table->string('cor', 7)->nullable()->comment('Cor para exibição (hexadecimal)');
            $table->string('icone', 50)->nullable()->comment('Ícone (FontAwesome)');
            $table->integer('ordem')->default(0)->comment('Ordem de exibição');
            $table->boolean('is_ativo')->default(true)->comment('1=Ativo, 0=Inativo');
            $table->timestamps();
            
            $table->index(['categoria']);
            $table->index(['ordem']);
            $table->index(['is_ativo']);
        });

        // Inserir status padrão
        DB::table('status')->insert([
            [
                'codigo' => 'PENDENTE',
                'nome' => 'Pendente',
                'descricao' => 'Aguardando início',
                'categoria' => 'EXECUCAO',
                'cor' => '#6c757d',
                'ordem' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'EM_ANALISE',
                'nome' => 'Em Análise',
                'descricao' => 'Em processo de análise',
                'categoria' => 'EXECUCAO',
                'cor' => '#ffc107',
                'ordem' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'APROVADO',
                'nome' => 'Aprovado',
                'descricao' => 'Aprovado com sucesso',
                'categoria' => 'EXECUCAO',
                'cor' => '#28a745',
                'ordem' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'REPROVADO',
                'nome' => 'Reprovado',
                'descricao' => 'Reprovado - necessita correções',
                'categoria' => 'EXECUCAO',
                'cor' => '#dc3545',
                'ordem' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'DEVOLVIDO',
                'nome' => 'Devolvido para Correção',
                'descricao' => 'Retornado para ajustes',
                'categoria' => 'EXECUCAO',
                'cor' => '#fd7e14',
                'ordem' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'CANCELADO',
                'nome' => 'Cancelado',
                'descricao' => 'Processo cancelado',
                'categoria' => 'EXECUCAO',
                'cor' => '#6c757d',
                'ordem' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'FINALIZADO',
                'nome' => 'Finalizado',
                'descricao' => 'Projeto finalizado com sucesso',
                'categoria' => 'EXECUCAO',
                'cor' => '#17a2b8',
                'ordem' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status');
    }
}; 