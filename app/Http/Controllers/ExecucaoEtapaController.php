<?php

namespace App\Http\Controllers;

use App\Models\Acao;
use App\Models\ExecucaoEtapa;
use App\Models\EtapaFluxo;
use App\Models\Documento;
use App\Models\Status;
use App\Models\HistoricoEtapa;
use App\Traits\HasOrganizacaoAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExecucaoEtapaController extends Controller
{
    use HasOrganizacaoAccess;

    /**
     * Exibe o workflow completo de uma ação
     */
    public function workflow(Acao $acao)
    {
        // Verificar se o usuário pode acessar esta ação
        if (!$this->canAccessOrganizacao($acao->demanda->termoAdesao->organizacao_id)) {
            abort(403, 'Acesso negado a esta ação.');
        }

        // Buscar todas as etapas do fluxo
        $etapasFluxo = EtapaFluxo::where('tipo_fluxo_id', $acao->tipo_fluxo_id)
            ->with([
                'modulo',
                'grupoExigencia.templatesDocumento.tipoDocumento',
                'organizacaoSolicitante',
                'organizacaoExecutora'
            ])
            ->orderBy('ordem_execucao')
            ->get();

        // Buscar execuções existentes
        $execucoes = ExecucaoEtapa::where('acao_id', $acao->id)
            ->with([
                'etapaFluxo',
                'status',
                'usuarioResponsavel',
                'documentos.tipoDocumento',
                'documentos.usuarioUpload'
            ])
            ->get()
            ->keyBy('etapa_fluxo_id');

        // Determinar etapa atual
        $etapaAtual = $this->determinarEtapaAtual($acao, $execucoes);

        // Verificar permissões do usuário atual
        $user = Auth::user();
        $permissoes = $this->calcularPermissoes($user, $acao, $etapaAtual);

        return view('workflow.acao', compact(
            'acao',
            'etapasFluxo',
            'execucoes',
            'etapaAtual',
            'permissoes'
        ));
    }

    /**
     * Inicia uma nova etapa
     */
    public function iniciarEtapa(Request $request, Acao $acao, EtapaFluxo $etapaFluxo)
    {
        if (!$this->canEdit($acao->demanda->termoAdesao->organizacao_id)) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }

        DB::beginTransaction();
        try {
            // Verificar se pode iniciar esta etapa
            if (!$this->podeIniciarEtapa($acao, $etapaFluxo)) {
                return response()->json(['error' => 'Não é possível iniciar esta etapa agora'], 400);
            }

            // Criar execução da etapa
            $execucao = ExecucaoEtapa::create([
                'acao_id' => $acao->id,
                'etapa_fluxo_id' => $etapaFluxo->id,
                'usuario_responsavel_id' => Auth::id(),
                'status_id' => Status::where('codigo', 'PENDENTE')->first()->id,
                'data_inicio' => now(),
                'data_prazo' => now()->addDays($etapaFluxo->prazo_dias),
                'created_by' => Auth::id()
            ]);

            // Registrar no histórico
            HistoricoEtapa::create([
                'execucao_etapa_id' => $execucao->id,
                'usuario_id' => Auth::id(),
                'status_novo_id' => $execucao->status_id,
                'acao' => 'ETAPA_INICIADA',
                'descricao_acao' => 'Etapa iniciada',
                'ip_usuario' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Etapa iniciada com sucesso',
                'execucao_id' => $execucao->id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Erro ao iniciar etapa: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Upload de documento para uma etapa
     */
    public function uploadDocumento(Request $request, ExecucaoEtapa $execucao)
    {
        $request->validate([
            'arquivo' => 'required|file|max:51200', // 50MB
            'tipo_documento_id' => 'required|exists:tipo_documento,id',
            'observacoes' => 'nullable|string|max:1000'
        ]);

        // Verificar permissões
        if (!$this->podeEnviarDocumento($execucao)) {
            return response()->json(['error' => 'Sem permissão para enviar documento'], 403);
        }

        DB::beginTransaction();
        try {
            $arquivo = $request->file('arquivo');
            $nomeOriginal = $arquivo->getClientOriginalName();
            $nomeArquivo = time() . '_' . $nomeOriginal;
            $caminho = $arquivo->storeAs('documentos/execucoes/' . $execucao->id, $nomeArquivo, 'public');

            // Criar registro do documento
            $documento = Documento::create([
                'execucao_etapa_id' => $execucao->id,
                'tipo_documento_id' => $request->tipo_documento_id,
                'usuario_upload_id' => Auth::id(),
                'nome_arquivo' => $nomeOriginal,
                'nome_arquivo_sistema' => $nomeArquivo,
                'tamanho_bytes' => $arquivo->getSize(),
                'mime_type' => $arquivo->getMimeType(),
                'hash_arquivo' => hash_file('sha256', $arquivo->getRealPath()),
                'caminho_storage' => $caminho,
                'observacoes' => $request->observacoes
            ]);

            // Atualizar status da execução se necessário
            if ($execucao->status->codigo === 'PENDENTE') {
                $statusEmAnalise = Status::where('codigo', 'EM_ANALISE')->first();
                $execucao->update(['status_id' => $statusEmAnalise->id]);

                // Registrar no histórico
                HistoricoEtapa::create([
                    'execucao_etapa_id' => $execucao->id,
                    'usuario_id' => Auth::id(),
                    'status_anterior_id' => Status::where('codigo', 'PENDENTE')->first()->id,
                    'status_novo_id' => $statusEmAnalise->id,
                    'acao' => 'DOCUMENTO_ENVIADO',
                    'descricao_acao' => 'Documento enviado: ' . $nomeOriginal,
                    'ip_usuario' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Documento enviado com sucesso',
                'documento' => $documento->load('tipoDocumento', 'usuarioUpload')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Erro ao enviar documento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Aprovar documento
     */
    public function aprovarDocumento(Request $request, Documento $documento)
    {
        $request->validate([
            'observacoes' => 'nullable|string|max:1000'
        ]);

        if (!$this->podeAprovarDocumento($documento)) {
            return response()->json(['error' => 'Sem permissão para aprovar documento'], 403);
        }

        DB::beginTransaction();
        try {
            // Marcar documento como aprovado
            $documento->update([
                'is_aprovado' => true,
                'data_aprovacao' => now(),
                'usuario_aprovacao_id' => Auth::id(),
                'observacoes_aprovacao' => $request->observacoes
            ]);

            // Registrar no histórico
            HistoricoEtapa::create([
                'execucao_etapa_id' => $documento->execucao_etapa_id,
                'usuario_id' => Auth::id(),
                'acao' => 'DOCUMENTO_APROVADO',
                'descricao_acao' => 'Documento aprovado: ' . $documento->nome_arquivo,
                'observacao' => $request->observacoes,
                'ip_usuario' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Verificar se todos os documentos estão aprovados
            $this->verificarConclusaoEtapa($documento->execucaoEtapa);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Documento aprovado com sucesso'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Erro ao aprovar documento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reprovar documento
     */
    public function reprovarDocumento(Request $request, Documento $documento)
    {
        $request->validate([
            'motivo' => 'required|string|max:1000'
        ]);

        if (!$this->podeAprovarDocumento($documento)) {
            return response()->json(['error' => 'Sem permissão para reprovar documento'], 403);
        }

        DB::beginTransaction();
        try {
            // Marcar documento como reprovado
            $documento->update([
                'is_aprovado' => false,
                'data_reprovacao' => now(),
                'usuario_reprovacao_id' => Auth::id(),
                'motivo_reprovacao' => $request->motivo
            ]);

            // Voltar status da execução para devolvido
            $statusDevolvido = Status::where('codigo', 'DEVOLVIDO')->first();
            $execucao = $documento->execucaoEtapa;
            $statusAnterior = $execucao->status_id;
            
            $execucao->update([
                'status_id' => $statusDevolvido->id,
                'justificativa' => $request->motivo
            ]);

            // Registrar no histórico
            HistoricoEtapa::create([
                'execucao_etapa_id' => $execucao->id,
                'usuario_id' => Auth::id(),
                'status_anterior_id' => $statusAnterior,
                'status_novo_id' => $statusDevolvido->id,
                'acao' => 'DOCUMENTO_REPROVADO',
                'descricao_acao' => 'Documento reprovado: ' . $documento->nome_arquivo,
                'observacao' => $request->motivo,
                'ip_usuario' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Documento reprovado. Etapa devolvida para correção.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Erro ao reprovar documento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Concluir etapa
     */
    public function concluirEtapa(Request $request, ExecucaoEtapa $execucao)
    {
        $request->validate([
            'observacoes' => 'nullable|string|max:1000'
        ]);

        if (!$this->podeConcluirEtapa($execucao)) {
            return response()->json(['error' => 'Sem permissão ou etapa não pode ser concluída'], 403);
        }

        DB::beginTransaction();
        try {
            // Marcar etapa como concluída
            $statusAprovado = Status::where('codigo', 'APROVADO')->first();
            $statusAnterior = $execucao->status_id;
            
            $execucao->update([
                'status_id' => $statusAprovado->id,
                'data_conclusao' => now(),
                'observacoes' => $request->observacoes,
                'percentual_conclusao' => 100.00
            ]);

            // Registrar no histórico
            HistoricoEtapa::create([
                'execucao_etapa_id' => $execucao->id,
                'usuario_id' => Auth::id(),
                'status_anterior_id' => $statusAnterior,
                'status_novo_id' => $statusAprovado->id,
                'acao' => 'ETAPA_CONCLUIDA',
                'descricao_acao' => 'Etapa concluída',
                'observacao' => $request->observacoes,
                'ip_usuario' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Verificar se deve iniciar próxima etapa
            $this->verificarProximaEtapa($execucao->acao);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Etapa concluída com sucesso'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Erro ao concluir etapa: ' . $e->getMessage()], 500);
        }
    }

    // ===== MÉTODOS AUXILIARES =====

    private function determinarEtapaAtual(Acao $acao, $execucoes)
    {
        // Buscar primeira etapa não concluída
        $etapasFluxo = EtapaFluxo::where('tipo_fluxo_id', $acao->tipo_fluxo_id)
            ->orderBy('ordem_execucao')
            ->get();

        foreach ($etapasFluxo as $etapa) {
            $execucao = $execucoes->get($etapa->id);
            if (!$execucao || $execucao->status->codigo !== 'APROVADO') {
                return $etapa;
            }
        }

        return null; // Todas as etapas concluídas
    }

    private function calcularPermissoes($user, $acao, $etapaAtual)
    {
        $permissoes = [
            'pode_iniciar_etapa' => false,
            'pode_enviar_documento' => false,
            'pode_aprovar_documento' => false,
            'pode_concluir_etapa' => false
        ];

        if (!$etapaAtual) {
            return $permissoes;
        }

        $userOrgId = $user->organizacao_id;
        
        // Pode iniciar se for da organização solicitante
        if ($userOrgId === $etapaAtual->organizacao_solicitante_id) {
            $permissoes['pode_iniciar_etapa'] = true;
            $permissoes['pode_enviar_documento'] = true;
        }

        // Pode aprovar se for da organização executora
        if ($userOrgId === $etapaAtual->organizacao_executora_id) {
            $permissoes['pode_aprovar_documento'] = true;
            $permissoes['pode_concluir_etapa'] = true;
        }

        return $permissoes;
    }

    private function podeIniciarEtapa(Acao $acao, EtapaFluxo $etapaFluxo)
    {
        $user = Auth::user();
        
        // Verificar se é da organização solicitante
        if ($user->organizacao_id !== $etapaFluxo->organizacao_solicitante_id) {
            return false;
        }

        // Verificar se etapa anterior foi concluída (se não for a primeira)
        if ($etapaFluxo->ordem_execucao > 1) {
            $etapaAnterior = EtapaFluxo::where('tipo_fluxo_id', $etapaFluxo->tipo_fluxo_id)
                ->where('ordem_execucao', $etapaFluxo->ordem_execucao - 1)
                ->first();

            if ($etapaAnterior) {
                $execucaoAnterior = ExecucaoEtapa::where('acao_id', $acao->id)
                    ->where('etapa_fluxo_id', $etapaAnterior->id)
                    ->first();

                if (!$execucaoAnterior || $execucaoAnterior->status->codigo !== 'APROVADO') {
                    return false;
                }
            }
        }

        return true;
    }

    private function podeEnviarDocumento(ExecucaoEtapa $execucao)
    {
        $user = Auth::user();
        
        // Verificar se é da organização solicitante
        if ($user->organizacao_id !== $execucao->etapaFluxo->organizacao_solicitante_id) {
            return false;
        }

        // Verificar se etapa está em status que permite envio
        return in_array($execucao->status->codigo, ['PENDENTE', 'DEVOLVIDO']);
    }

    private function podeAprovarDocumento(Documento $documento)
    {
        $user = Auth::user();
        $execucao = $documento->execucaoEtapa;
        
        // Verificar se é da organização executora
        return $user->organizacao_id === $execucao->etapaFluxo->organizacao_executora_id;
    }

    private function podeConcluirEtapa(ExecucaoEtapa $execucao)
    {
        $user = Auth::user();
        
        // Verificar se é da organização executora
        if ($user->organizacao_id !== $execucao->etapaFluxo->organizacao_executora_id) {
            return false;
        }

        // Verificar se todos os documentos estão aprovados
        $documentosPendentes = $execucao->documentos()
            ->where(function($q) {
                $q->whereNull('is_aprovado')
                  ->orWhere('is_aprovado', false);
            })
            ->count();

        return $documentosPendentes === 0 && $execucao->documentos()->count() > 0;
    }

    private function verificarConclusaoEtapa(ExecucaoEtapa $execucao)
    {
        // Se todos os documentos estão aprovados, pode marcar como pronto para conclusão
        $documentosPendentes = $execucao->documentos()
            ->where(function($q) {
                $q->whereNull('is_aprovado')
                  ->orWhere('is_aprovado', false);
            })
            ->count();

        if ($documentosPendentes === 0 && $execucao->documentos()->count() > 0) {
            $statusProntoParaConclusao = Status::where('codigo', 'APROVADO')->first();
            if ($execucao->status->codigo !== 'APROVADO') {
                $execucao->update(['status_id' => $statusProntoParaConclusao->id]);
            }
        }
    }

    private function verificarProximaEtapa(Acao $acao)
    {
        // Buscar próxima etapa na sequência
        $proximaEtapa = EtapaFluxo::where('tipo_fluxo_id', $acao->tipo_fluxo_id)
            ->whereNotIn('id', function($query) use ($acao) {
                $query->select('etapa_fluxo_id')
                      ->from('execucao_etapa')
                      ->where('acao_id', $acao->id);
            })
            ->orderBy('ordem_execucao')
            ->first();

        // Aqui poderia implementar lógica para auto-iniciar próxima etapa
        // ou enviar notificações
    }
} 