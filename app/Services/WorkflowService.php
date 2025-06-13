<?php

namespace App\Services;

use App\Models\ExecucaoEtapa;
use App\Models\Status;
use App\Models\HistoricoEtapa;
use App\Models\Notificacao;
use App\Models\TipoNotificacao;
use App\Models\TransicaoEtapa;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class WorkflowService
{
    /**
     * Alterar status de uma execução de etapa
     */
    public function alterarStatus(
        ExecucaoEtapa $execucaoEtapa,
        Status $novoStatus,
        ?string $observacao = null,
        ?User $usuario = null
    ): bool {
        $usuario = $usuario ?? Auth::user();
        $statusAnterior = $execucaoEtapa->status;

        DB::beginTransaction();
        try {
            // Atualizar status da execução
            $execucaoEtapa->update([
                'status_id' => $novoStatus->id,
                'updated_by' => $usuario->id,
            ]);

            // Registrar no histórico
            HistoricoEtapa::registrarMudancaStatus(
                $execucaoEtapa,
                $statusAnterior,
                $novoStatus,
                $usuario,
                $observacao
            );

            // Enviar notificação se necessário
            $this->enviarNotificacaoMudancaStatus($execucaoEtapa, $statusAnterior, $novoStatus);

            // Verificar transições automáticas
            $this->processarTransicoes($execucaoEtapa);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Processar transições automáticas baseadas no novo status
     */
    public function processarTransicoes(ExecucaoEtapa $execucaoEtapa): void
    {
        $transicoes = TransicaoEtapa::ativas()
            ->porEtapaOrigem($execucaoEtapa->etapa_fluxo_id)
            ->ordenado()
            ->get();

        foreach ($transicoes as $transicao) {
            if ($transicao->avaliarCondicao($execucaoEtapa)) {
                // Executar transição
                $this->executarTransicao($execucaoEtapa, $transicao);
                break; // Executar apenas a primeira transição que atender a condição
            }
        }
    }

    /**
     * Executar uma transição específica
     */
    public function executarTransicao(ExecucaoEtapa $execucaoEtapa, TransicaoEtapa $transicao): void
    {
        // Criar nova execução da etapa de destino
        $novaExecucao = ExecucaoEtapa::create([
            'acao_id' => $execucaoEtapa->acao_id,
            'etapa_fluxo_id' => $transicao->etapa_fluxo_destino_id,
            'status_id' => Status::where('codigo', 'PENDENTE')->first()->id,
            'etapa_anterior_id' => $execucaoEtapa->id,
            'data_inicio' => now(),
            'motivo_transicao' => $transicao->mensagem_transicao,
            'created_by' => Auth::id(),
        ]);

        // Registrar no histórico
        HistoricoEtapa::create([
            'execucao_etapa_id' => $novaExecucao->id,
            'usuario_id' => Auth::id() ?? 1, // Sistema
            'acao' => 'TRANSICAO_AUTOMATICA',
            'descricao_acao' => "Transição automática da etapa {$execucaoEtapa->etapaFluxo->nome_etapa}",
            'observacao' => $transicao->descricao,
            'data_acao' => now(),
        ]);

        // Enviar notificação da nova etapa
        $this->enviarNotificacaoNovaEtapa($novaExecucao);
    }

    /**
     * Enviar notificação de mudança de status
     */
    public function enviarNotificacaoMudancaStatus(
        ExecucaoEtapa $execucaoEtapa,
        ?Status $statusAnterior,
        Status $statusNovo
    ): void {
        $tipoNotificacao = TipoNotificacao::buscarPorCodigo('STATUS_ALTERADO');
        
        if (!$tipoNotificacao) {
            return;
        }

        $variaveis = [
            'etapa_nome' => $execucaoEtapa->etapaFluxo->nome_etapa,
            'acao_nome' => $execucaoEtapa->acao->nome,
            'status_anterior' => $statusAnterior?->nome ?? 'Nenhum',
            'status_novo' => $statusNovo->nome,
            'usuario_nome' => Auth::user()?->name ?? 'Sistema',
        ];

        // Enviar para o responsável da etapa
        if ($execucaoEtapa->usuario_responsavel_id) {
            Notificacao::criarNotificacaoSistema(
                $execucaoEtapa,
                $execucaoEtapa->usuarioResponsavel,
                $tipoNotificacao,
                $variaveis,
                'MEDIA'
            );
        }
    }

    /**
     * Enviar notificação de nova etapa
     */
    public function enviarNotificacaoNovaEtapa(ExecucaoEtapa $execucaoEtapa): void
    {
        $tipoNotificacao = TipoNotificacao::buscarPorCodigo('NOVA_ETAPA');
        
        if (!$tipoNotificacao) {
            return;
        }

        $variaveis = [
            'etapa_nome' => $execucaoEtapa->etapaFluxo->nome_etapa,
            'acao_nome' => $execucaoEtapa->acao->nome,
            'usuario_nome' => Auth::user()?->name ?? 'Sistema',
            'data_inicio' => $execucaoEtapa->data_inicio->format('d/m/Y H:i'),
        ];

        // Determinar quem deve receber a notificação
        $destinatarios = $this->obterDestinatariosEtapa($execucaoEtapa);

        foreach ($destinatarios as $destinatario) {
            Notificacao::criarNotificacaoSistema(
                $execucaoEtapa,
                $destinatario,
                $tipoNotificacao,
                $variaveis,
                'ALTA'
            );
        }
    }

    /**
     * Obter destinatários para notificações de uma etapa
     */
    public function obterDestinatariosEtapa(ExecucaoEtapa $execucaoEtapa): array
    {
        $destinatarios = [];

        // Responsável direto
        if ($execucaoEtapa->usuario_responsavel_id) {
            $destinatarios[] = $execucaoEtapa->usuarioResponsavel;
        }

        // Usuários da organização executora (exemplo)
        $organizacaoExecutora = $execucaoEtapa->etapaFluxo->organizacaoExecutora;
        if ($organizacaoExecutora) {
            $usuariosOrganizacao = User::where('organizacao_id', $organizacaoExecutora->id)
                ->where('is_ativo', true)
                ->get();
            
            $destinatarios = array_merge($destinatarios, $usuariosOrganizacao->toArray());
        }

        return array_unique($destinatarios, SORT_REGULAR);
    }

    /**
     * Verificar e enviar notificações de prazo próximo
     */
    public function verificarPrazosProximos(): int
    {
        $execucoesPrazoProximo = ExecucaoEtapa::whereNotNull('data_prazo')
            ->whereNull('data_conclusao')
            ->whereBetween('data_prazo', [now(), now()->addDays(2)])
            ->get();

        $notificacoesEnviadas = 0;

        foreach ($execucoesPrazoProximo as $execucao) {
            if ($this->enviarNotificacaoPrazoProximo($execucao)) {
                $notificacoesEnviadas++;
            }
        }

        return $notificacoesEnviadas;
    }

    /**
     * Enviar notificação de prazo próximo
     */
    public function enviarNotificacaoPrazoProximo(ExecucaoEtapa $execucaoEtapa): bool
    {
        $tipoNotificacao = TipoNotificacao::buscarPorCodigo('PRAZO_PROXIMO');
        
        if (!$tipoNotificacao || !$execucaoEtapa->data_prazo) {
            return false;
        }

        $diasRestantes = now()->diffInDays($execucaoEtapa->data_prazo, false);

        $variaveis = [
            'etapa_nome' => $execucaoEtapa->etapaFluxo->nome_etapa,
            'acao_nome' => $execucaoEtapa->acao->nome,
            'data_prazo' => $execucaoEtapa->data_prazo->format('d/m/Y H:i'),
            'dias_restantes' => max(0, $diasRestantes),
        ];

        $destinatarios = $this->obterDestinatariosEtapa($execucaoEtapa);

        foreach ($destinatarios as $destinatario) {
            Notificacao::criarNotificacaoSistema(
                $execucaoEtapa,
                $destinatario,
                $tipoNotificacao,
                $variaveis,
                'ALTA'
            );
        }

        return true;
    }

    /**
     * Verificar e enviar notificações de prazo expirado
     */
    public function verificarPrazosExpirados(): int
    {
        $execucoesPrazoExpirado = ExecucaoEtapa::whereNotNull('data_prazo')
            ->whereNull('data_conclusao')
            ->where('data_prazo', '<', now())
            ->get();

        $notificacoesEnviadas = 0;

        foreach ($execucoesPrazoExpirado as $execucao) {
            if ($this->enviarNotificacaoPrazoExpirado($execucao)) {
                $notificacoesEnviadas++;
            }
        }

        return $notificacoesEnviadas;
    }

    /**
     * Enviar notificação de prazo expirado
     */
    public function enviarNotificacaoPrazoExpirado(ExecucaoEtapa $execucaoEtapa): bool
    {
        $tipoNotificacao = TipoNotificacao::buscarPorCodigo('PRAZO_EXPIRADO');
        
        if (!$tipoNotificacao || !$execucaoEtapa->data_prazo) {
            return false;
        }

        $diasAtraso = now()->diffInDays($execucaoEtapa->data_prazo);

        $variaveis = [
            'etapa_nome' => $execucaoEtapa->etapaFluxo->nome_etapa,
            'acao_nome' => $execucaoEtapa->acao->nome,
            'data_prazo' => $execucaoEtapa->data_prazo->format('d/m/Y H:i'),
            'dias_atraso' => $diasAtraso,
        ];

        $destinatarios = $this->obterDestinatariosEtapa($execucaoEtapa);

        foreach ($destinatarios as $destinatario) {
            Notificacao::criarNotificacaoSistema(
                $execucaoEtapa,
                $destinatario,
                $tipoNotificacao,
                $variaveis,
                'URGENTE'
            );
        }

        // Atualizar dias em atraso
        $execucaoEtapa->update(['dias_em_atraso' => $diasAtraso]);

        return true;
    }

    /**
     * Obter estatísticas do workflow
     */
    public function obterEstatisticas(): array
    {
        return [
            'execucoes_ativas' => ExecucaoEtapa::whereNull('data_conclusao')->count(),
            'execucoes_atrasadas' => ExecucaoEtapa::whereNotNull('data_prazo')
                ->whereNull('data_conclusao')
                ->where('data_prazo', '<', now())
                ->count(),
            'notificacoes_pendentes' => Notificacao::pendentesEnvio()->count(),
            'historicos_hoje' => HistoricoEtapa::whereDate('data_acao', today())->count(),
        ];
    }
} 