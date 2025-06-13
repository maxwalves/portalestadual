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
        Schema::create('tipo_notificacoes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique()->comment('Código único');
            $table->string('nome', 255)->comment('Nome do tipo');
            $table->text('descricao')->nullable()->comment('Descrição');
            $table->text('template_email')->nullable()->comment('Template HTML do email');
            $table->text('template_sms')->nullable()->comment('Template do SMS');
            $table->text('template_sistema')->nullable()->comment('Template para notificação no sistema');
            $table->json('variaveis_disponiveis')->nullable()->comment('Variáveis disponíveis para o template');
            $table->boolean('is_ativo')->default(true)->comment('1=Ativo, 0=Inativo');
            $table->timestamps();

            $table->index(['is_ativo']);
        });

        // Inserir tipos de notificação padrão
        DB::table('tipo_notificacoes')->insert([
            [
                'codigo' => 'NOVA_ETAPA',
                'nome' => 'Nova Etapa Iniciada',
                'descricao' => 'Notificação quando uma nova etapa é iniciada',
                'template_sistema' => 'Uma nova etapa foi iniciada: {etapa_nome}',
                'template_email' => '<p>Uma nova etapa foi iniciada: <strong>{etapa_nome}</strong></p><p>Ação: {acao_nome}</p>',
                'variaveis_disponiveis' => json_encode(['etapa_nome', 'acao_nome', 'usuario_nome', 'data_inicio']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'PRAZO_PROXIMO',
                'nome' => 'Prazo Próximo',
                'descricao' => 'Notificação de proximidade do prazo',
                'template_sistema' => 'Atenção: O prazo da etapa {etapa_nome} está próximo do vencimento',
                'template_email' => '<p>Atenção: O prazo da etapa <strong>{etapa_nome}</strong> está próximo do vencimento.</p><p>Prazo: {data_prazo}</p>',
                'variaveis_disponiveis' => json_encode(['etapa_nome', 'acao_nome', 'data_prazo', 'dias_restantes']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'PRAZO_EXPIRADO',
                'nome' => 'Prazo Expirado',
                'descricao' => 'Notificação de prazo expirado',
                'template_sistema' => 'URGENTE: O prazo da etapa {etapa_nome} foi expirado',
                'template_email' => '<p style="color: red;"><strong>URGENTE:</strong> O prazo da etapa <strong>{etapa_nome}</strong> foi expirado.</p><p>Prazo era: {data_prazo}</p>',
                'variaveis_disponiveis' => json_encode(['etapa_nome', 'acao_nome', 'data_prazo', 'dias_atraso']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'STATUS_ALTERADO',
                'nome' => 'Status Alterado',
                'descricao' => 'Notificação de mudança de status',
                'template_sistema' => 'Status da etapa {etapa_nome} alterado para {status_novo}',
                'template_email' => '<p>O status da etapa <strong>{etapa_nome}</strong> foi alterado.</p><p>De: {status_anterior} → Para: {status_novo}</p>',
                'variaveis_disponiveis' => json_encode(['etapa_nome', 'status_anterior', 'status_novo', 'usuario_nome']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'DOCUMENTO_PENDENTE',
                'nome' => 'Documento Pendente',
                'descricao' => 'Notificação de documento aguardando análise',
                'template_sistema' => 'Novo documento enviado aguardando análise: {documento_nome}',
                'template_email' => '<p>Um novo documento foi enviado e aguarda análise.</p><p>Documento: <strong>{documento_nome}</strong></p><p>Etapa: {etapa_nome}</p>',
                'variaveis_disponiveis' => json_encode(['documento_nome', 'etapa_nome', 'acao_nome', 'usuario_envio']),
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
        Schema::dropIfExists('tipo_notificacoes');
    }
};
