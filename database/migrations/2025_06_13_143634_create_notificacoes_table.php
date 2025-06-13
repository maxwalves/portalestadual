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
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('execucao_etapa_id')->constrained('execucao_etapas')->onDelete('cascade')->comment('Execução relacionada');
            $table->foreignId('usuario_destinatario_id')->constrained('users')->onDelete('cascade')->comment('Usuário destinatário');
            $table->foreignId('tipo_notificacao_id')->constrained('tipo_notificacoes')->onDelete('restrict')->comment('Tipo de notificação');
            $table->enum('canal', ['EMAIL', 'SISTEMA', 'SMS', 'WHATSAPP'])->default('SISTEMA')->comment('Canal de envio');
            $table->string('assunto', 500)->nullable()->comment('Assunto (para email)');
            $table->text('mensagem')->comment('Conteúdo da mensagem');
            $table->enum('prioridade', ['BAIXA', 'MEDIA', 'ALTA', 'URGENTE'])->default('MEDIA')->comment('Prioridade');
            $table->timestamp('data_envio')->useCurrent()->comment('Data/hora de envio');
            $table->timestamp('data_leitura')->nullable()->comment('Data/hora de leitura');
            $table->timestamp('data_expiracao')->nullable()->comment('Data de expiração da notificação');
            $table->enum('status_envio', ['PENDENTE', 'ENVIADO', 'ERRO', 'LIDO', 'EXPIRADO'])->default('PENDENTE')->comment('Status do envio');
            $table->integer('tentativas')->default(0)->comment('Número de tentativas de envio');
            $table->text('erro_mensagem')->nullable()->comment('Mensagem de erro (se houver)');
            $table->json('metadata')->nullable()->comment('Dados adicionais');
            $table->timestamps();

            $table->index(['execucao_etapa_id']);
            $table->index(['usuario_destinatario_id']);
            $table->index(['status_envio']);
            $table->index(['data_envio']);
            $table->index(['prioridade']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
    }
};
