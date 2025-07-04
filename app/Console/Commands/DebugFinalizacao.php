<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Status;
use App\Models\TransicaoEtapa;
use App\Models\EtapaFluxo;
use App\Models\Acao;
use App\Models\ExecucaoEtapa;

class DebugFinalizacao extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:finalizacao {acao_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug do problema de finalização de projetos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== DEBUG: PROBLEMA DE FINALIZAÇÃO ===');
        $this->line('');

        // 1. Verificar status FINALIZADO
        $statusFinalizado = Status::where('codigo', 'FINALIZADO')->first();
        if (!$statusFinalizado) {
            $this->error('Status FINALIZADO não encontrado!');
            return;
        }

        $this->info("Status FINALIZADO encontrado:");
        $this->line("ID: {$statusFinalizado->id}");
        $this->line("Nome: {$statusFinalizado->nome}");
        $this->line('');

        // 2. Verificar transições configuradas para FINALIZADO
        $transicoes = TransicaoEtapa::where('status_condicao_id', $statusFinalizado->id)
            ->with(['etapaOrigem', 'etapaDestino', 'statusCondicao'])
            ->get();

        if ($transicoes->count() > 0) {
            $this->error("PROBLEMA ENCONTRADO! Existem {$transicoes->count()} transições configuradas para status FINALIZADO:");
            $this->line('');

            foreach ($transicoes as $transicao) {
                $this->line("Transição ID: {$transicao->id}");
                $this->line("Etapa Origem: {$transicao->etapaOrigem->nome_etapa} (ID: {$transicao->etapa_fluxo_origem_id})");
                $this->line("Etapa Destino: {$transicao->etapaDestino->nome_etapa} (ID: {$transicao->etapa_fluxo_destino_id})");
                $this->line("Ativa: " . ($transicao->ativa ? 'Sim' : 'Não'));
                $this->line("Prioridade: {$transicao->prioridade}");
                $this->line("Descrição: {$transicao->descricao}");
                $this->line('---');
            }
            
            $this->line('');
            $this->warn('SOLUÇÃO: Essas transições devem ser DESATIVADAS ou REMOVIDAS!');
            $this->warn('Quando status é FINALIZADO, o projeto deve PARAR o fluxo.');
            
            if ($this->confirm('Deseja desativar automaticamente essas transições?')) {
                foreach ($transicoes as $transicao) {
                    $transicao->update(['ativa' => false]);
                    $this->info("Transição ID {$transicao->id} desativada!");
                }
            }
        } else {
            $this->info('✅ Nenhuma transição configurada para status FINALIZADO. Está correto!');
        }

        $this->line('');

        // 3. Se foi passado ID da ação, analisar caso específico
        $acaoId = $this->argument('acao_id');
        if ($acaoId) {
            $this->info("=== ANÁLISE DA AÇÃO {$acaoId} ===");
            
            $acao = Acao::find($acaoId);
            if (!$acao) {
                $this->error("Ação {$acaoId} não encontrada!");
                return;
            }

            $this->line("Nome: {$acao->nome}");
            $this->line("Status: {$acao->status}");
            $this->line("Finalizado: " . ($acao->is_finalizado ? 'Sim' : 'Não'));
            
            if ($acao->is_finalizado) {
                $this->line("Data Finalização: {$acao->data_finalizacao}");
            }

            // Verificar execuções das etapas
            $execucoes = ExecucaoEtapa::where('acao_id', $acaoId)
                ->with(['etapaFluxo', 'status'])
                ->orderBy('created_at')
                ->get();

            $this->line('');
            $this->info('Execuções de etapas:');
            foreach ($execucoes as $exec) {
                $this->line("Etapa: {$exec->etapaFluxo->nome_etapa} (Ordem: {$exec->etapaFluxo->ordem_execucao})");
                $this->line("Status: {$exec->status->nome}");
                $this->line("Criada em: {$exec->created_at}");
                if ($exec->data_conclusao) {
                    $this->line("Concluída em: {$exec->data_conclusao}");
                }
                $this->line('---');
            }

            // Verificar qual deveria ser a etapa atual
            $etapaAtual = $this->determinarEtapaAtual($acao);
            if ($etapaAtual) {
                $this->warn("Etapa atual determinada pelo sistema: {$etapaAtual->nome_etapa} (Ordem: {$etapaAtual->ordem_execucao})");
            } else {
                $this->info("✅ Nenhuma etapa atual - fluxo concluído corretamente");
            }
        }

        $this->line('');
        $this->info('Debug concluído!');
    }

    private function determinarEtapaAtual($acao)
    {
        $execucoes = ExecucaoEtapa::where('acao_id', $acao->id)
            ->with(['status'])
            ->get()
            ->keyBy('etapa_fluxo_id');

        $etapasFluxo = EtapaFluxo::where('tipo_fluxo_id', $acao->tipo_fluxo_id)
            ->orderBy('ordem_execucao')
            ->get();

        // 1. Verificar se há alguma etapa em execução
        foreach ($etapasFluxo as $etapa) {
            $execucao = $execucoes->get($etapa->id);
            if ($execucao && in_array($execucao->status->codigo, ['PENDENTE', 'EM_ANALISE', 'DEVOLVIDO'])) {
                return $etapa;
            }
        }

        // 2. Se nenhuma em execução, buscar primeira não concluída
        foreach ($etapasFluxo as $etapa) {
            $execucao = $execucoes->get($etapa->id);
            if (!$execucao || $execucao->status->codigo !== 'APROVADO') {
                return $etapa;
            }
        }

        return null; // Todas concluídas
    }
}
