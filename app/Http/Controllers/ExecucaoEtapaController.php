<?php

namespace App\Http\Controllers;

use App\Models\Acao;
use App\Models\ExecucaoEtapa;
use App\Models\EtapaFluxo;
use App\Models\Documento;
use App\Models\Status;
use App\Models\HistoricoEtapa;
use App\Models\TipoDocumento;
use App\Models\EtapaStatusOpcao;
use App\Models\TransicaoEtapa;
use App\Traits\HasOrganizacaoAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExecucaoEtapaController extends Controller
{
    use HasOrganizacaoAccess;

    /**
     * Verificar se o usuário pode acessar uma ação específica
     * Considera tanto organizações solicitantes quanto executoras
     */
    protected function canAccessAcao(Acao $acao): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Admin de sistema e Paranacidade sempre podem
        if ($user->hasRole(['admin', 'admin_paranacidade', 'tecnico_paranacidade'])) {
            return true;
        }

        // Para usuários de secretaria, verificar se:
        if ($user->hasRole(['admin_secretaria', 'tecnico_secretaria'])) {
            $userOrgId = $user->organizacao_id;
            
            // 1. A organização é solicitante (dona da demanda)
            $acao->load(['demanda.termoAdesao']);
            if ($acao->demanda && $acao->demanda->termoAdesao && 
                $acao->demanda->termoAdesao->organizacao_id == $userOrgId) {
                return true;
            }
            
            // 2. A organização é executora de alguma etapa
            $acao->load(['tipoFluxo.etapasFluxo']);
            if ($acao->tipoFluxo && $acao->tipoFluxo->etapasFluxo) {
                $isExecutora = $acao->tipoFluxo->etapasFluxo
                    ->where('organizacao_executora_id', $userOrgId)
                    ->isNotEmpty();
                
                if ($isExecutora) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Exibe o workflow completo de uma ação
     */
    public function workflow(Acao $acao)
    {
        // Verificar se o usuário pode acessar esta ação
        if (!$this->canAccessAcao($acao)) {
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
     * Exibe uma etapa específica com todas as exigências
     */
    public function etapaDetalhada(Acao $acao, EtapaFluxo $etapaFluxo)
    {
        // Verificar se o usuário pode acessar esta ação
        if (!$this->canAccessAcao($acao)) {
            abort(403, 'Acesso negado a esta ação.');
        }

        // Carregar dados da etapa
        $etapaFluxo->load([
            'modulo',
            'grupoExigencia.templatesDocumento.tipoDocumento',
            'organizacaoSolicitante',
            'organizacaoExecutora'
        ]);

        // Buscar execução desta etapa
        $execucao = ExecucaoEtapa::where('acao_id', $acao->id)
            ->where('etapa_fluxo_id', $etapaFluxo->id)
            ->with([
                'status',
                'usuarioResponsavel',
                'documentos.tipoDocumento',
                'documentos.usuarioUpload'
            ])
            ->first();

        // Verificar permissões do usuário atual
        $user = Auth::user();
        $permissoes = $this->calcularPermissoes($user, $acao, $etapaFluxo);

        // Buscar templates de documentos se houver grupo de exigência
        $templatesDocumento = collect();
        if ($etapaFluxo->grupoExigencia) {
            $templatesDocumento = $etapaFluxo->grupoExigencia->templatesDocumento()
                ->with('tipoDocumento')
                ->orderBy('ordem')
                ->get();
        }

        // Agrupar documentos enviados por tipo
        $documentosEnviados = collect();
        if ($execucao) {
            $documentosEnviados = $execucao->documentos()
                ->with(['tipoDocumento', 'usuarioUpload'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('tipo_documento_id')
                ->map(function ($documentos) {
                    $pendentes = $documentos->whereIn('status_documento', ['PENDENTE', 'EM_ANALISE']);
                    if ($pendentes->isNotEmpty()) {
                        return $pendentes->first();
                    }
                    return $documentos->first();
                });
        }

        return view('workflow.etapa-detalhada', compact(
            'acao',
            'etapaFluxo',
            'execucao',
            'permissoes',
            'templatesDocumento',
            'documentosEnviados'
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
     * Upload de documento
     */
    public function uploadDocumento(Request $request, ExecucaoEtapa $execucao)
    {
        \Log::info('Iniciando upload de documento', [
            'execucao_id' => $execucao->id,
            'request_data' => $request->all()
        ]);

        $request->validate([
            'arquivo' => 'required|file|max:51200', // 50MB
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'template_documento_id' => 'nullable|exists:template_documentos,id',
            'observacoes' => 'nullable|string|max:1000'
        ]);

        if (!$this->podeEnviarDocumento($execucao)) {
            \Log::warning('Usuário sem permissão para enviar documento', [
                'user_id' => Auth::id(),
                'execucao_id' => $execucao->id
            ]);
            return response()->json(['error' => 'Sem permissão para enviar documento'], 403);
        }

        DB::beginTransaction();
        try {
            $arquivo = $request->file('arquivo');
            $tipoDocumento = TipoDocumento::findOrFail($request->tipo_documento_id);
            
            \Log::info('Validando arquivo', [
                'nome_arquivo' => $arquivo->getClientOriginalName(),
                'extensao' => $arquivo->getClientOriginalExtension(),
                'tamanho' => $arquivo->getSize(),
                'tipo_documento' => $tipoDocumento->nome,
                'extensoes_permitidas' => $tipoDocumento->extensoes_permitidas
            ]);
            
            // Validar arquivo
            if (!$tipoDocumento->isExtensaoPermitida($arquivo->getClientOriginalExtension())) {
                \Log::error('Extensão não permitida', [
                    'extensao' => $arquivo->getClientOriginalExtension(),
                    'extensoes_permitidas' => $tipoDocumento->extensoes_permitidas
                ]);
                return response()->json(['error' => 'Tipo de arquivo não permitido'], 400);
            }

            if (!$tipoDocumento->isTamanhoPermitido($arquivo->getSize())) {
                \Log::error('Arquivo muito grande', [
                    'tamanho' => $arquivo->getSize(),
                    'tamanho_maximo' => $tipoDocumento->tamanho_maximo_mb * 1024 * 1024
                ]);
                return response()->json(['error' => 'Arquivo muito grande'], 400);
            }

            // Gerar hash do arquivo
            $hashArquivo = hash_file('sha256', $arquivo->getRealPath());
            
            // Verificar se já existe um arquivo idêntico
            $documentoExistente = Documento::where('execucao_etapa_id', $execucao->id)
                ->where('tipo_documento_id', $request->tipo_documento_id)
                ->where('hash_arquivo', $hashArquivo)
                ->first();

            if ($documentoExistente) {
                \Log::warning('Arquivo duplicado detectado', [
                    'hash' => $hashArquivo,
                    'documento_existente_id' => $documentoExistente->id
                ]);
                return response()->json(['error' => 'Este arquivo já foi enviado'], 400);
            }

            // Gerar nome único do arquivo
            $nomeArquivoSistema = uniqid() . '_' . time() . '.' . $arquivo->getClientOriginalExtension();
            $caminhoStorage = "documentos/execucao_{$execucao->id}/{$nomeArquivoSistema}";

            \Log::info('Fazendo upload do arquivo', [
                'caminho_storage' => $caminhoStorage,
                'nome_sistema' => $nomeArquivoSistema
            ]);

            // Fazer upload
            $arquivo->storeAs(dirname($caminhoStorage), basename($caminhoStorage), 'public');

            // Criar registro do documento
            $documento = Documento::create([
                'execucao_etapa_id' => $execucao->id,
                'template_documento_id' => $request->template_documento_id,
                'tipo_documento_id' => $request->tipo_documento_id,
                'usuario_upload_id' => Auth::id(),
                'nome_arquivo' => $arquivo->getClientOriginalName(),
                'nome_arquivo_sistema' => $nomeArquivoSistema,
                'tamanho_bytes' => $arquivo->getSize(),
                'mime_type' => $arquivo->getMimeType(),
                'hash_arquivo' => $hashArquivo,
                'caminho_storage' => $caminhoStorage,
                'status_documento' => Documento::STATUS_PENDENTE,
                'observacoes' => $request->observacoes,
                'created_by' => Auth::id()
            ]);

            \Log::info('Documento criado com sucesso', [
                'documento_id' => $documento->id,
                'nome_arquivo' => $documento->nome_arquivo
            ]);

            // Registrar no histórico
            HistoricoEtapa::create([
                'execucao_etapa_id' => $execucao->id,
                'usuario_id' => Auth::id(),
                'acao' => 'DOCUMENTO_ENVIADO',
                'descricao_acao' => "Documento enviado: {$tipoDocumento->nome}",
                'observacao' => $request->observacoes,
                'dados_alterados' => json_encode([
                    'documento_id' => $documento->id,
                    'tipo_documento' => $tipoDocumento->nome,
                    'nome_arquivo' => $arquivo->getClientOriginalName()
                ]),
                'ip_usuario' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Atualizar status da execução se necessário
            $statusAtual = $execucao->status->codigo;
            if (in_array($statusAtual, ['PENDENTE', 'DEVOLVIDO'])) {
                $statusEmAnalise = Status::where('codigo', 'EM_ANALISE')->first();
                if ($statusEmAnalise) {
                    $execucao->update([
                        'status_id' => $statusEmAnalise->id,
                        'justificativa' => null // Limpar justificativa anterior
                    ]);
                }
            }

            DB::commit();

            \Log::info('Upload concluído com sucesso', [
                'documento_id' => $documento->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documento enviado com sucesso',
                'documento_id' => $documento->id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Erro no upload de documento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
            return response()->json(['error' => 'Sem permissão para aprovar este documento'], 403);
        }

        DB::beginTransaction();
        try {
            // Atualizar documento
            $documento->update([
                'status_documento' => Documento::STATUS_APROVADO,
                'data_aprovacao' => now(),
                'usuario_aprovacao_id' => Auth::id(),
                'observacoes' => $request->observacoes,
                'motivo_reprovacao' => null, // Limpar motivo de reprovação anterior
                'updated_by' => Auth::id()
            ]);

            // Registrar no histórico
            HistoricoEtapa::create([
                'execucao_etapa_id' => $documento->execucao_etapa_id,
                'usuario_id' => Auth::id(),
                'acao' => 'DOCUMENTO_APROVADO',
                'descricao_acao' => "Documento aprovado: {$documento->tipoDocumento->nome}",
                'observacao' => $request->observacoes,
                'dados_alterados' => json_encode([
                    'documento_id' => $documento->id,
                    'status_anterior' => 'PENDENTE',
                    'status_novo' => 'APROVADO'
                ]),
                'ip_usuario' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Verificar se todos os documentos obrigatórios estão aprovados
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
            'motivo_reprovacao' => 'required|string|max:1000'
        ]);

        if (!$this->podeAprovarDocumento($documento)) {
            return response()->json(['error' => 'Sem permissão para reprovar este documento'], 403);
        }

        DB::beginTransaction();
        try {
            // Atualizar documento
            $documento->update([
                'status_documento' => Documento::STATUS_REPROVADO,
                'motivo_reprovacao' => $request->motivo_reprovacao,
                'data_aprovacao' => now(),
                'usuario_aprovacao_id' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            // Registrar no histórico
            HistoricoEtapa::create([
                'execucao_etapa_id' => $documento->execucao_etapa_id,
                'usuario_id' => Auth::id(),
                'acao' => 'DOCUMENTO_REPROVADO',
                'descricao_acao' => "Documento reprovado: {$documento->tipoDocumento->nome}",
                'observacao' => $request->motivo_reprovacao,
                'dados_alterados' => json_encode([
                    'documento_id' => $documento->id,
                    'status_anterior' => 'PENDENTE',
                    'status_novo' => 'REPROVADO',
                    'motivo' => $request->motivo_reprovacao
                ]),
                'ip_usuario' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Documento reprovado com sucesso'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Erro ao reprovar documento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Exibir histórico da etapa
     */
    public function historicoEtapa(ExecucaoEtapa $execucao)
    {
        // Verificar se o usuário pode acessar esta execução
        if (!$this->canAccessOrganizacao($execucao->acao->demanda->termoAdesao->organizacao_id)) {
            abort(403, 'Acesso negado a este histórico.');
        }

        // Carregar execução com relacionamentos
        $execucao->load([
            'acao',
            'etapaFluxo',
            'status',
            'usuarioResponsavel'
        ]);

        // Buscar históricos com relacionamentos
        $historicos = HistoricoEtapa::where('execucao_etapa_id', $execucao->id)
            ->with([
                'usuario',
                'statusAnterior',
                'statusNovo'
            ])
            ->orderBy('data_acao', 'desc')
            ->get();

        return view('workflow.historico-etapa', compact('execucao', 'historicos'));
    }

    /**
     * Concluir etapa
     */
    public function concluirEtapa(Request $request, ExecucaoEtapa $execucao)
    {
        $request->validate([
            'observacoes' => 'nullable|string|max:1000'
        ]);

        // Verificar permissões e documentos obrigatórios com feedback detalhado
        $user = Auth::user();
        
        // Verificar se é da organização solicitante
        if ($user->organizacao_id !== $execucao->etapaFluxo->organizacao_solicitante_id) {
            return response()->json(['error' => 'Apenas a organização solicitante pode concluir esta etapa'], 403);
        }
        
        // Verificar documentos obrigatórios
        $grupoExigencia = $execucao->etapaFluxo->grupoExigencia;
        if ($grupoExigencia) {
            $templatesObrigatorios = $grupoExigencia->templatesDocumento()
                ->where('is_obrigatorio', true)
                ->get();
            
            $documentosPendentes = [];
            foreach ($templatesObrigatorios as $template) {
                $documentoAprovado = $execucao->documentos()
                    ->where('tipo_documento_id', $template->tipo_documento_id)
                    ->where('status_documento', Documento::STATUS_APROVADO)
                    ->exists();

                if (!$documentoAprovado) {
                    $documentosPendentes[] = $template->nome;
                }
            }
            
            if (!empty($documentosPendentes)) {
                return response()->json([
                    'error' => 'Não é possível concluir a etapa. Os seguintes documentos obrigatórios precisam estar aprovados: ' . implode(', ', $documentosPendentes)
                ], 400);
            }
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

    /**
     * Alterar status da etapa
     */
    public function alterarStatusEtapa(Request $request, ExecucaoEtapa $execucao)
    {
        \Log::info('=== INICIO ALTERAÇÃO STATUS ETAPA ===', [
            'user_id' => Auth::id(),
            'user_email' => Auth::user()->email,
            'user_org_id' => Auth::user()->organizacao_id,
            'execucao_id' => $execucao->id,
            'etapa_nome' => $execucao->etapaFluxo->nome_etapa,
            'status_atual' => $execucao->status->codigo,
            'novo_status_id' => $request->status_id,
            'request_data' => $request->all()
        ]);

        $request->validate([
            'status_id' => 'required|exists:status,id',
            'justificativa' => 'nullable|string|max:1000',
            'observacoes' => 'nullable|string|max:1000'
        ]);

        // Verificar se o usuário pode alterar o status desta etapa
        $podeAlterar = $this->podeAlterarStatusEtapa($execucao);
        \Log::info('Resultado verificação permissão alteração', [
            'pode_alterar' => $podeAlterar,
            'execucao_id' => $execucao->id
        ]);
        
        if (!$podeAlterar) {
            \Log::warning('PERMISSÃO NEGADA para alterar status', [
                'user_id' => Auth::id(),
                'execucao_id' => $execucao->id,
                'user_org_id' => Auth::user()->organizacao_id,
                'org_solicitante_id' => $execucao->etapaFluxo->organizacao_solicitante_id,
                'org_executora_id' => $execucao->etapaFluxo->organizacao_executora_id
            ]);
            return response()->json(['error' => 'Sem permissão para alterar status desta etapa'], 403);
        }

        // Verificar se o status é válido para esta etapa
        if (!EtapaStatusOpcao::isStatusValido($execucao->etapa_fluxo_id, $request->status_id)) {
            return response()->json(['error' => 'Status não permitido para esta etapa'], 400);
        }

        // Verificar se requer justificativa
        $requerJustificativa = EtapaStatusOpcao::requerJustificativaStatus($execucao->etapa_fluxo_id, $request->status_id);
        if ($requerJustificativa && empty($request->justificativa)) {
            return response()->json(['error' => 'Justificativa é obrigatória para este status'], 400);
        }

        DB::beginTransaction();
        try {
            $statusAnterior = $execucao->status_id;
            $novoStatus = Status::findOrFail($request->status_id);

            // Atualizar execução
            $execucao->update([
                'status_id' => $request->status_id,
                'justificativa' => $request->justificativa,
                'observacoes' => $request->observacoes,
                'data_conclusao' => $novoStatus->codigo === 'APROVADO' ? now() : null,
                'percentual_conclusao' => $novoStatus->codigo === 'APROVADO' ? 100.00 : null,
                'updated_by' => Auth::id()
            ]);

            // Registrar no histórico
            HistoricoEtapa::create([
                'execucao_etapa_id' => $execucao->id,
                'usuario_id' => Auth::id(),
                'status_anterior_id' => $statusAnterior,
                'status_novo_id' => $request->status_id,
                'acao' => 'STATUS_ALTERADO',
                'descricao_acao' => "Status alterado para: {$novoStatus->nome}",
                'observacao' => $request->justificativa ?: $request->observacoes,
                'dados_alterados' => json_encode([
                    'status_anterior' => $statusAnterior,
                    'status_novo' => $request->status_id,
                    'justificativa' => $request->justificativa
                ]),
                'ip_usuario' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // ===== NOVA LÓGICA DE TRANSIÇÕES =====
            // Buscar transições configuradas para esta etapa e status
            $transicaoExecutada = $this->executarTransicoes($execucao, $request->status_id);

            DB::commit();

            $mensagem = "Status alterado para {$novoStatus->nome} com sucesso";
            if ($transicaoExecutada) {
                $mensagem .= ". Fluxo direcionado para próxima etapa automaticamente.";
            }

            return response()->json([
                'success' => true,
                'message' => $mensagem,
                'transicao_executada' => $transicaoExecutada
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Erro ao alterar status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Buscar opções de status disponíveis para uma etapa
     */
    public function getOpcoesStatus(ExecucaoEtapa $execucao)
    {
        // Verificar se o usuário pode acessar esta execução
        if (!$this->canAccessOrganizacao($execucao->acao->demanda->termoAdesao->organizacao_id)) {
            \Log::warning('Acesso negado para opções de status', [
                'user_id' => auth()->id(),
                'execucao_id' => $execucao->id,
                'organizacao_acao' => $execucao->acao->demanda->termoAdesao->organizacao_id
            ]);
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $user = Auth::user();
        
        \Log::info('Buscando opções de status', [
            'user_id' => $user->id,
            'user_org_id' => $user->organizacao_id,
            'execucao_id' => $execucao->id,
            'etapa_fluxo_id' => $execucao->etapa_fluxo_id,
            'etapa_nome' => $execucao->etapaFluxo->nome_etapa,
            'org_solicitante_id' => $execucao->etapaFluxo->organizacao_solicitante_id,
            'org_executora_id' => $execucao->etapaFluxo->organizacao_executora_id
        ]);
        
        $opcoes = EtapaStatusOpcao::getOpcoesDisponiveis($execucao->etapa_fluxo_id, $user->organizacao_id);
        
        \Log::info('Opções encontradas', [
            'count' => $opcoes->count(),
            'opcoes' => $opcoes->pluck('status.nome', 'status.id')->toArray()
        ]);

        return response()->json([
            'opcoes' => $opcoes->map(function ($opcao) {
                return [
                    'id' => $opcao->status->id,
                    'codigo' => $opcao->status->codigo,
                    'nome' => $opcao->status->nome,
                    'cor' => $opcao->status->cor,
                    'icone' => $opcao->status->icone,
                    'ordem' => $opcao->ordem,
                    'requer_justificativa' => $opcao->requer_justificativa
                ];
            }),
            'debug' => [
                'user_org_id' => $user->organizacao_id,
                'etapa_fluxo_id' => $execucao->etapa_fluxo_id,
                'is_solicitante' => $user->organizacao_id === $execucao->etapaFluxo->organizacao_solicitante_id,
                'is_executora' => $user->organizacao_id === $execucao->etapaFluxo->organizacao_executora_id
            ]
        ]);
    }

    // ===== MÉTODOS AUXILIARES =====

    private function determinarEtapaAtual(Acao $acao, $execucoes)
    {
        // Buscar etapas em ordem de execução
        $etapasFluxo = EtapaFluxo::where('tipo_fluxo_id', $acao->tipo_fluxo_id)
            ->orderBy('ordem_execucao')
            ->get();

        // 1. Verificar se há alguma etapa em execução (status = PENDENTE, EM_ANALISE, DEVOLVIDO)
        foreach ($etapasFluxo as $etapa) {
            $execucao = $execucoes->get($etapa->id);
            if ($execucao && in_array($execucao->status->codigo, ['PENDENTE', 'EM_ANALISE', 'DEVOLVIDO'])) {
                \Log::info('Etapa atual determinada (em trabalho)', [
                    'etapa_id' => $etapa->id,
                    'etapa_nome' => $etapa->nome_etapa,
                    'status' => $execucao->status->codigo
                ]);
                return $etapa;
            }
        }

        // 2. Se nenhuma em execução, buscar primeira não concluída (não APROVADA)
        foreach ($etapasFluxo as $etapa) {
            $execucao = $execucoes->get($etapa->id);
            if (!$execucao || $execucao->status->codigo !== 'APROVADO') {
                \Log::info('Etapa atual determinada (próxima a iniciar)', [
                    'etapa_id' => $etapa->id,
                    'etapa_nome' => $etapa->nome_etapa,
                    'tem_execucao' => $execucao ? true : false,
                    'status' => $execucao ? $execucao->status->codigo : 'NAO_INICIADA'
                ]);
                return $etapa;
            }
        }

        \Log::info('Nenhuma etapa atual encontrada - workflow concluído');
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
        
        // Pode iniciar etapa se for da organização solicitante
        if ($userOrgId === $etapaAtual->organizacao_solicitante_id) {
            $permissoes['pode_iniciar_etapa'] = true;
        }

        // Pode enviar documentos se for da organização executora
        if ($userOrgId === $etapaAtual->organizacao_executora_id) {
            $permissoes['pode_enviar_documento'] = true;
        }

        // Pode aprovar documentos e concluir etapa se for da organização solicitante
        if ($userOrgId === $etapaAtual->organizacao_solicitante_id) {
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

    private function podeEnviarDocumento(ExecucaoEtapa $execucao): bool
    {
        $user = Auth::user();
        
        // Admins sempre podem
        if ($user->hasRole(['admin', 'admin_paranacidade'])) {
            return true;
        }
        
        $userOrgId = $user->organizacao_id;
        $etapaFluxo = $execucao->etapaFluxo;
        
        // Verificar se o usuário pertence à organização solicitante OU executora da etapa
        $pertenceOrganizacao = ($userOrgId === $etapaFluxo->organizacao_solicitante_id) || 
                              ($userOrgId === $etapaFluxo->organizacao_executora_id);
        
        if (!$pertenceOrganizacao) {
            \Log::warning('Usuário não pertence às organizações da etapa para envio de documento', [
                'user_id' => $user->id,
                'user_org_id' => $userOrgId,
                'org_solicitante_id' => $etapaFluxo->organizacao_solicitante_id,
                'org_executora_id' => $etapaFluxo->organizacao_executora_id,
                'execucao_id' => $execucao->id
            ]);
            return false;
        }
        
        // Verificar se a etapa está em um status que permite envio de documento
        $statusPermitidos = ['PENDENTE', 'EM_ANALISE', 'DEVOLVIDO'];
        if (!in_array($execucao->status->codigo, $statusPermitidos)) {
            \Log::warning('Status da etapa não permite envio de documento', [
                'execucao_id' => $execucao->id,
                'status_atual' => $execucao->status->codigo,
                'status_permitidos' => $statusPermitidos
            ]);
            return false;
        }
        
        \Log::info('Permissão para enviar documento concedida', [
            'user_id' => $user->id,
            'user_org_id' => $userOrgId,
            'execucao_id' => $execucao->id,
            'is_solicitante' => $userOrgId === $etapaFluxo->organizacao_solicitante_id,
            'is_executora' => $userOrgId === $etapaFluxo->organizacao_executora_id
        ]);
        
        return true;
    }

    private function podeAprovarDocumento(Documento $documento): bool
    {
        $user = Auth::user();
        
        // Deve ser da organização solicitante e documento deve estar pendente ou em análise
        return $user->organizacao_id === $documento->execucaoEtapa->etapaFluxo->organizacao_solicitante_id &&
               in_array($documento->status_documento, [Documento::STATUS_PENDENTE, Documento::STATUS_EM_ANALISE]);
    }

    private function podeConcluirEtapa(ExecucaoEtapa $execucao): bool
    {
        $user = Auth::user();
        
        // Deve ser da organização solicitante
        if ($user->organizacao_id !== $execucao->etapaFluxo->organizacao_solicitante_id) {
            return false;
        }

        // Verificar se todos os documentos obrigatórios estão aprovados
        $grupoExigencia = $execucao->etapaFluxo->grupoExigencia;
        if (!$grupoExigencia) {
            return true; // Etapa sem documentos obrigatórios
        }

        $templatesObrigatorios = $grupoExigencia->templatesDocumento()
            ->where('is_obrigatorio', true)
            ->get();

        foreach ($templatesObrigatorios as $template) {
            $documentoAprovado = $execucao->documentos()
                ->where('tipo_documento_id', $template->tipo_documento_id)
                ->where('status_documento', Documento::STATUS_APROVADO)
                ->exists();

            if (!$documentoAprovado) {
                return false;
            }
        }

        return true;
    }

    private function verificarConclusaoEtapa(ExecucaoEtapa $execucao)
    {
        // Verificar se todos os documentos obrigatórios estão aprovados
        $grupoExigencia = $execucao->etapaFluxo->grupoExigencia;
        if (!$grupoExigencia) {
            return; // Etapa sem documentos obrigatórios
        }

        $templatesObrigatorios = $grupoExigencia->templatesDocumento()
            ->where('is_obrigatorio', true)
            ->get();

        $todosAprovados = true;
        foreach ($templatesObrigatorios as $template) {
            $documentoAprovado = $execucao->documentos()
                ->where('tipo_documento_id', $template->tipo_documento_id)
                ->where('status_documento', Documento::STATUS_APROVADO)
                ->exists();

            if (!$documentoAprovado) {
                $todosAprovados = false;
                break;
            }
        }

        // Se todos os documentos obrigatórios estão aprovados, marcar como pronto para conclusão
        if ($todosAprovados && $execucao->status->codigo !== 'APROVADO') {
            $statusProntoParaConclusao = Status::where('codigo', 'APROVADO')->first();
            if ($statusProntoParaConclusao) {
                $execucao->update(['status_id' => $statusProntoParaConclusao->id]);
            }
        }
    }

    private function verificarProximaEtapa(Acao $acao)
    {
        // Buscar a execução mais recente
        $execucaoAtual = ExecucaoEtapa::where('acao_id', $acao->id)
            ->with(['etapaFluxo', 'status'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$execucaoAtual) {
            return;
        }

        // Buscar próxima etapa baseada nas transições configuradas
        $proximaEtapa = TransicaoEtapa::buscarProximaEtapa(
            $execucaoAtual->etapa_fluxo_id, 
            $execucaoAtual->status_id
        );

        if ($proximaEtapa) {
            // Verificar se já existe execução para a próxima etapa
            $execucaoExistente = ExecucaoEtapa::where('acao_id', $acao->id)
                ->where('etapa_fluxo_id', $proximaEtapa->id)
                ->first();

            if (!$execucaoExistente) {
                // Criar nova execução para a próxima etapa
                ExecucaoEtapa::create([
                    'acao_id' => $acao->id,
                    'etapa_fluxo_id' => $proximaEtapa->id,
                    'status_id' => Status::where('codigo', 'PENDENTE')->first()->id,
                    'data_inicio' => now(),
                    'data_prazo' => $proximaEtapa->calcularDataPrazo(),
                    'etapa_anterior_id' => $execucaoAtual->id,
                    'created_by' => Auth::id()
                ]);

                // Registrar no histórico
                HistoricoEtapa::create([
                    'execucao_etapa_id' => $execucaoAtual->id,
                    'usuario_id' => Auth::id(),
                    'acao' => 'TRANSICAO_AUTOMATICA',
                    'descricao_acao' => "Transição automática para: {$proximaEtapa->nome_etapa}",
                    'dados_alterados' => json_encode([
                        'etapa_origem' => $execucaoAtual->etapaFluxo->nome_etapa,
                        'etapa_destino' => $proximaEtapa->nome_etapa,
                        'status_trigger' => $execucaoAtual->status->nome
                    ]),
                    'ip_usuario' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }
        }
    }

    private function podeAlterarStatusEtapa(ExecucaoEtapa $execucao): bool
    {
        $user = Auth::user();
        
        // Admins sempre podem
        if ($user->hasRole(['admin', 'admin_paranacidade'])) {
            return true;
        }
        
        $userOrgId = $user->organizacao_id;
        $etapaFluxo = $execucao->etapaFluxo;
        
        // Verificar se o usuário pertence à organização solicitante OU executora da etapa
        $pertenceOrganizacao = ($userOrgId === $etapaFluxo->organizacao_solicitante_id) || 
                              ($userOrgId === $etapaFluxo->organizacao_executora_id);
        
        if (!$pertenceOrganizacao) {
            \Log::warning('Usuário não pertence às organizações da etapa', [
                'user_id' => $user->id,
                'user_org_id' => $userOrgId,
                'org_solicitante_id' => $etapaFluxo->organizacao_solicitante_id,
                'org_executora_id' => $etapaFluxo->organizacao_executora_id,
                'execucao_id' => $execucao->id
            ]);
            return false;
        }

        // Verificar se a etapa está em um status que permite alteração
        $statusPermitidos = ['PENDENTE', 'EM_ANALISE', 'DEVOLVIDO'];
        if (!in_array($execucao->status->codigo, $statusPermitidos)) {
            \Log::warning('Status da etapa não permite alteração', [
                'execucao_id' => $execucao->id,
                'status_atual' => $execucao->status->codigo,
                'status_permitidos' => $statusPermitidos
            ]);
            return false;
        }

        \Log::info('Permissão para alterar status concedida', [
            'user_id' => $user->id,
            'user_org_id' => $userOrgId,
            'execucao_id' => $execucao->id,
            'status_atual' => $execucao->status->codigo
        ]);

        return true;
    }

    private function executarTransicoes(ExecucaoEtapa $execucao, $novoStatusId)
    {
        // Buscar transições configuradas para esta etapa e o status selecionado
        $transicoes = TransicaoEtapa::where('etapa_fluxo_origem_id', $execucao->etapa_fluxo_id)
            ->where('status_condicao_id', $novoStatusId)  // Status que o usuário selecionou
            ->ativas()
            ->ordenadaPorPrioridade()
            ->with(['etapaDestino', 'statusCondicao'])
            ->get();

        if ($transicoes->isEmpty()) {
            \Log::info('Nenhuma transição encontrada', [
                'etapa_origem_id' => $execucao->etapa_fluxo_id,
                'status_selecionado' => $novoStatusId
            ]);
            return false; // Não há transições configuradas para este status
        }

        $transicaoExecutada = false;

        // Executar a primeira transição válida (maior prioridade)
        foreach ($transicoes as $transicao) {
            \Log::info('Avaliando transição', [
                'transicao_id' => $transicao->id,
                'etapa_destino' => $transicao->etapaDestino->nome_etapa,
                'status_condicao' => $transicao->statusCondicao->nome
            ]);

            // Verificar se já existe execução para a etapa destino
            $execucaoExistente = ExecucaoEtapa::where('acao_id', $execucao->acao_id)
                ->where('etapa_fluxo_id', $transicao->etapa_fluxo_destino_id)
                ->first();

            if (!$execucaoExistente) {
                // Criar nova execução para a próxima etapa
                $statusPendente = Status::where('codigo', 'PENDENTE')->first();
                
                $novaExecucao = ExecucaoEtapa::create([
                    'acao_id' => $execucao->acao_id,
                    'etapa_fluxo_id' => $transicao->etapa_fluxo_destino_id,
                    'status_id' => $statusPendente->id,
                    'data_inicio' => now(),
                    'data_prazo' => now()->addDays($transicao->etapaDestino->prazo_dias),
                    'etapa_anterior_id' => $execucao->id,
                    'created_by' => Auth::id()
                ]);

                // Registrar no histórico
                HistoricoEtapa::create([
                    'execucao_etapa_id' => $execucao->id,
                    'usuario_id' => Auth::id(),
                    'acao' => 'TRANSICAO_AUTOMATICA',
                    'descricao_acao' => "Transição automática para: {$transicao->etapaDestino->nome_etapa}",
                    'observacao' => $transicao->descricao,
                    'dados_alterados' => json_encode([
                        'etapa_origem' => $execucao->etapaFluxo->nome_etapa,
                        'etapa_destino' => $transicao->etapaDestino->nome_etapa,
                        'status_trigger' => $transicao->statusCondicao->nome,
                        'transicao_id' => $transicao->id,
                        'nova_execucao_id' => $novaExecucao->id
                    ]),
                    'ip_usuario' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                \Log::info('Transição executada com sucesso', [
                    'transicao_id' => $transicao->id,
                    'nova_execucao_id' => $novaExecucao->id,
                    'etapa_destino' => $transicao->etapaDestino->nome_etapa
                ]);

                $transicaoExecutada = true;
                break; // Executar apenas a primeira transição válida
            } else {
                \Log::info('Execução já existe para etapa destino', [
                    'etapa_destino_id' => $transicao->etapa_fluxo_destino_id,
                    'execucao_existente_id' => $execucaoExistente->id
                ]);
            }
        }

        return $transicaoExecutada;
    }
} 