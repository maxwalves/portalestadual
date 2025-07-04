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
                'organizacaoExecutora',
                'transicoesOrigem.etapaDestino',
                'transicoesOrigem.statusCondicao'
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

        // === NOVA FUNCIONALIDADE: Calcular acessibilidade de cada etapa ===
        $etapasAcessiveis = collect();
        foreach ($etapasFluxo as $etapaFluxo) {
            // NOVA LÓGICA: Usar podeInteragirComEtapa para determinar se pode EDITAR
            $podeInteragir = $this->podeInteragirComEtapa($acao, $etapaFluxo);
            $podeVisualizar = $this->podeVisualizarEtapa($acao, $etapaFluxo);
            $execucao = $execucoes->get($etapaFluxo->id);
            
            // Verificar se pode acessar histórico (específico para usuários envolvidos na etapa)
            $podeVerHistorico = false;
            if ($execucao && $podeVisualizar) {
                // Pode ver histórico se pode visualizar E pertence às organizações da etapa
                $userOrgId = $user->organizacao_id;
                $pertenceEtapa = ($userOrgId === $etapaFluxo->organizacao_solicitante_id) || 
                                ($userOrgId === $etapaFluxo->organizacao_executora_id);
                $podeVerHistorico = $pertenceEtapa || $user->hasRole(['admin', 'admin_paranacidade']);
            }
            
            $etapasAcessiveis->put($etapaFluxo->id, [
                'pode_acessar' => $podeInteragir,  // MUDANÇA: Agora usa podeInteragirComEtapa
                'pode_ver_detalhes' => $podeVisualizar,
                'pode_ver_historico' => $podeVerHistorico,
                'motivo_bloqueio' => !$podeInteragir ? $this->getMotivoBloqueioEtapa($acao, $etapaFluxo) : null
            ]);
        }

        return view('workflow.acao', compact(
            'acao',
            'etapasFluxo',
            'execucoes',
            'etapaAtual',
            'permissoes',
            'etapasAcessiveis'
        ));
    }

    /**
     * Obter motivo pelo qual uma etapa está bloqueada
     */
    private function getMotivoBloqueioEtapa(Acao $acao, EtapaFluxo $etapaFluxo): string
    {
        $user = Auth::user();
        
        // Verificar se é problema de organização
        $userOrgId = $user->organizacao_id;
        $pertenceEtapa = ($userOrgId === $etapaFluxo->organizacao_solicitante_id) || 
                        ($userOrgId === $etapaFluxo->organizacao_executora_id);
        
        if (!$pertenceEtapa) {
            return 'Sua organização não participa desta etapa';
        }

        // NOVA ABORDAGEM: Sistema flexível - não verificar etapas anteriores
        // O bloqueio agora é apenas por organização, não por sequência
        
        return 'Etapa acessível'; // Na verdade, esta função não deveria ser chamada se há acesso
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

        // === NOVA VALIDAÇÃO: Verificar se pode VISUALIZAR esta etapa ===
        if (!$this->podeVisualizarEtapa($acao, $etapaFluxo)) {
            abort(403, 'Sua organização não participa desta etapa.');
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
        $permissoes = $this->calcularPermissoes($user, $acao, $etapaFluxo, $execucao);
        
        // === VERIFICAR PERMISSÃO PARA VER HISTÓRICO ===
        $podeVerHistorico = false;
        if ($execucao) {
            // Pode ver histórico se pertence às organizações da etapa OU é admin
            $userOrgId = $user->organizacao_id;
            $pertenceEtapa = ($userOrgId === $etapaFluxo->organizacao_solicitante_id) || 
                            ($userOrgId === $etapaFluxo->organizacao_executora_id);
            $podeVerHistorico = $pertenceEtapa || $user->hasRole(['admin', 'admin_paranacidade']);
        }
        
        // Verificar status de visualização/interação
        $statusInteracao = [
            'pode_visualizar' => $this->podeVisualizarEtapa($acao, $etapaFluxo),
            'pode_interagir' => $this->podeInteragirComEtapa($acao, $etapaFluxo),
            'motivo_bloqueio' => null,
            'organizacao_responsavel_atual' => null
        ];
        
        // Se pode visualizar mas não pode interagir, explicar o motivo específico
        if ($statusInteracao['pode_visualizar'] && !$statusInteracao['pode_interagir']) {
            // Verificar primeiro se esta etapa é a atual no fluxo
            $etapaAtualDoFluxo = $this->determinarEtapaAtual($acao);
            
            if (!$etapaAtualDoFluxo || $etapaAtualDoFluxo->id !== $etapaFluxo->id) {
                // Esta não é a etapa atual do fluxo
                if ($etapaAtualDoFluxo) {
                    $statusInteracao['motivo_bloqueio'] = "Esta etapa não está ativa no momento. A etapa atual do fluxo é: {$etapaAtualDoFluxo->nome_etapa}";
                    $statusInteracao['organizacao_responsavel_atual'] = "Aguardando etapa: {$etapaAtualDoFluxo->nome_etapa}";
                } else {
                    $statusInteracao['motivo_bloqueio'] = "O fluxo de trabalho foi concluído.";
                    $statusInteracao['organizacao_responsavel_atual'] = "Fluxo concluído";
                }
            } else {
                // Esta é a etapa atual, verificar se o usuário não pertence às organizações desta etapa específica
                $userOrgId = $user->organizacao_id;
                $pertenceEtapa = ($userOrgId === $etapaFluxo->organizacao_solicitante_id) || 
                                ($userOrgId === $etapaFluxo->organizacao_executora_id);
                
                if (!$pertenceEtapa) {
                    // Usuário pode ver porque está no projeto, mas não pode agir nesta etapa específica
                    if (!$execucao) {
                        $statusInteracao['organizacao_responsavel_atual'] = $etapaFluxo->organizacaoSolicitante->nome;
                        $statusInteracao['motivo_bloqueio'] = "Esta etapa deve ser iniciada pela {$etapaFluxo->organizacaoSolicitante->nome}. Sua organização não participa desta etapa específica.";
                    } else {
                        $statusPermitidos = ['PENDENTE', 'EM_ANALISE', 'DEVOLVIDO'];
                        if (in_array($execucao->status->codigo, $statusPermitidos)) {
                            $statusInteracao['organizacao_responsavel_atual'] = $etapaFluxo->organizacaoExecutora->nome;
                            $statusInteracao['motivo_bloqueio'] = "Esta etapa está sendo executada pela {$etapaFluxo->organizacaoExecutora->nome}. Sua organização não participa desta etapa específica.";
                        } else {
                            $statusInteracao['motivo_bloqueio'] = "Esta etapa já foi concluída.";
                            $statusInteracao['organizacao_responsavel_atual'] = "Etapa concluída";
                        }
                    }
                } else {
                    // Usuário pertence à organização mas não pode agir por outros motivos
                    if (!$execucao) {
                        $statusInteracao['organizacao_responsavel_atual'] = $etapaFluxo->organizacaoSolicitante->nome;
                        $statusInteracao['motivo_bloqueio'] = "Aguardando a {$etapaFluxo->organizacaoSolicitante->nome} iniciar esta etapa.";
                    } else {
                        $statusPermitidos = ['PENDENTE', 'EM_ANALISE', 'DEVOLVIDO'];
                        if (in_array($execucao->status->codigo, $statusPermitidos)) {
                            $statusInteracao['organizacao_responsavel_atual'] = $etapaFluxo->organizacaoExecutora->nome;
                            $statusInteracao['motivo_bloqueio'] = "Aguardando a {$etapaFluxo->organizacaoExecutora->nome} enviar os documentos.";
                        } else {
                            $statusInteracao['motivo_bloqueio'] = "Esta etapa já foi concluída.";
                            $statusInteracao['organizacao_responsavel_atual'] = "Etapa concluída";
                        }
                    }
                }
            }
        }

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
            'documentosEnviados',
            'statusInteracao',
            'podeVerHistorico'
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

        \Log::info('Iniciando aprovação de documento', [
            'documento_id' => $documento->id,
            'user_id' => Auth::id(),
            'observacoes_recebidas' => $request->observacoes,
            'request_all' => $request->all()
        ]);

        if (!$this->podeAprovarDocumento($documento)) {
            return response()->json(['error' => 'Sem permissão para aprovar este documento'], 403);
        }

        DB::beginTransaction();
        try {
            // Dados para atualização
            $dadosUpdate = [
                'status_documento' => Documento::STATUS_APROVADO,
                'data_aprovacao' => now(),
                'usuario_aprovacao_id' => Auth::id(),
                'observacoes' => $request->observacoes,
                'motivo_reprovacao' => null, // Limpar motivo de reprovação anterior
                'updated_by' => Auth::id()
            ];

            \Log::info('Dados para atualização do documento', [
                'documento_id' => $documento->id,
                'dados_update' => $dadosUpdate
            ]);

            // Atualizar documento
            $documento->update($dadosUpdate);

            // Verificar se foi salvo corretamente
            $documento->refresh();
            \Log::info('Documento após atualização', [
                'documento_id' => $documento->id,
                'status_documento' => $documento->status_documento,
                'observacoes' => $documento->observacoes,
                'observacoes_length' => strlen($documento->observacoes ?? ''),
                'data_aprovacao' => $documento->data_aprovacao,
                'usuario_aprovacao_id' => $documento->usuario_aprovacao_id
            ]);

            // Teste direto no banco para verificar se o problema é do Eloquent
            $documentoBanco = \DB::table('documentos')->where('id', $documento->id)->first();
            \Log::info('Documento direto do banco', [
                'documento_id' => $documento->id,
                'observacoes_banco' => $documentoBanco->observacoes,
                'status_documento_banco' => $documentoBanco->status_documento
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
    public function historicoEtapa(Request $request, ExecucaoEtapa $execucao)
    {
        // Verificar se o usuário pode acessar esta ação (projeto)
        // Permite que todos os envolvidos no projeto vejam o histórico
        if (!$this->canAccessAcao($execucao->acao)) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Acesso negado a este histórico.'], 403);
            }
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
                'usuario.organizacao',
                'statusAnterior',
                'statusNovo'
            ])
            ->orderBy('data_acao', 'desc')
            ->get();

        // Se for requisição AJAX, retorna apenas o conteúdo do modal
        if ($request->ajax()) {
            \Log::info('Requisição AJAX para histórico detectada', [
                'execucao_id' => $execucao->id,
                'historicos_count' => $historicos->count(),
                'user_id' => Auth::id()
            ]);
            
            // Verificar se a view existe
            if (!view()->exists('workflow.historico-modal-content')) {
                \Log::error('View workflow.historico-modal-content não encontrada');
                return response()->json(['error' => 'Template de histórico não encontrado'], 500);
            }
            
            try {
                $content = view('workflow.historico-modal-content', compact('execucao', 'historicos'))->render();
                \Log::info('View renderizada com sucesso', ['content_length' => strlen($content)]);
                return $content;
            } catch (\Exception $e) {
                \Log::error('Erro ao renderizar view de histórico', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                return response()->json(['error' => 'Erro ao renderizar histórico: ' . $e->getMessage()], 500);
            }
        }

        // Caso contrário, retorna a página completa
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
                ->wherePivot('is_obrigatorio', true)
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

            // ===== NOVA LÓGICA: VERIFICAR SE É A ÚLTIMA ETAPA E FINALIZAR AUTOMATICAMENTE =====
            $acao = $execucao->acao;
            $ultimaEtapaConcluida = false;
            
            // Verificar se esta é a última etapa do fluxo
            if ($acao->isNaUltimaEtapa() && !$acao->isFinalizado()) {
                // É a última etapa e foi aprovada - finalizar automaticamente o projeto
                if ($acao->finalizar(Auth::user(), "Projeto finalizado automaticamente após conclusão da última etapa: {$execucao->etapaFluxo->nome_etapa}")) {
                    $ultimaEtapaConcluida = true;
                    
                    \Log::info('Projeto finalizado automaticamente', [
                        'acao_id' => $acao->id,
                        'execucao_id' => $execucao->id,
                        'etapa_final' => $execucao->etapaFluxo->nome_etapa,
                        'user_id' => Auth::id()
                    ]);

                    // Registrar a finalização no histórico
                    HistoricoEtapa::create([
                        'execucao_etapa_id' => $execucao->id,
                        'usuario_id' => Auth::id(),
                        'status_anterior_id' => null,
                        'status_novo_id' => $statusAprovado->id,
                        'acao' => 'FINALIZACAO_AUTOMATICA_PROJETO',
                        'descricao_acao' => 'Projeto finalizado automaticamente após conclusão da última etapa',
                        'observacao' => "Etapa final '{$execucao->etapaFluxo->nome_etapa}' concluída - projeto finalizado automaticamente",
                        'dados_alterados' => json_encode([
                            'acao_status_anterior' => $acao->getOriginal('status'),
                            'acao_status_novo' => 'FINALIZADO',
                            'projeto_finalizado' => true,
                            'data_finalizacao' => now()->toDateTimeString(),
                            'etapa_final' => $execucao->etapaFluxo->nome_etapa
                        ]),
                        'ip_usuario' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);
                }
            } else {
                // Verificar se deve iniciar próxima etapa (só se não finalizou)
                $this->verificarProximaEtapa($acao);
            }

            DB::commit();

            $mensagem = 'Etapa concluída com sucesso';
            if ($ultimaEtapaConcluida) {
                $mensagem .= '! O projeto foi finalizado automaticamente, pois esta era a última etapa do fluxo.';
            }

            return response()->json([
                'success' => true,
                'message' => $mensagem,
                'projeto_finalizado' => $ultimaEtapaConcluida,
                'data_finalizacao' => $ultimaEtapaConcluida ? $acao->fresh()->data_finalizacao->format('d/m/Y H:i') : null
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

            // ===== VERIFICAR FINALIZAÇÃO AUTOMÁTICA QUANDO STATUS FOR APROVADO =====
            $projetoFinalizadoAutomaticamente = false;
            if ($novoStatus->codigo === 'APROVADO') {
                $acao = $execucao->acao;
                
                // Verificar se esta é a última etapa do fluxo
                if ($acao->isNaUltimaEtapa() && !$acao->isFinalizado()) {
                    // É a última etapa e foi aprovada - finalizar automaticamente o projeto
                    if ($acao->finalizar(Auth::user(), "Projeto finalizado automaticamente após aprovação da última etapa: {$execucao->etapaFluxo->nome_etapa}")) {
                        $projetoFinalizadoAutomaticamente = true;
                        
                        \Log::info('Projeto finalizado automaticamente via alteração status', [
                            'acao_id' => $acao->id,
                            'execucao_id' => $execucao->id,
                            'etapa_final' => $execucao->etapaFluxo->nome_etapa,
                            'user_id' => Auth::id()
                        ]);

                        // Registrar a finalização no histórico
                        HistoricoEtapa::create([
                            'execucao_etapa_id' => $execucao->id,
                            'usuario_id' => Auth::id(),
                            'status_anterior_id' => null,
                            'status_novo_id' => $request->status_id,
                            'acao' => 'FINALIZACAO_AUTOMATICA_PROJETO',
                            'descricao_acao' => 'Projeto finalizado automaticamente após aprovação da última etapa',
                            'observacao' => "Etapa final '{$execucao->etapaFluxo->nome_etapa}' aprovada - projeto finalizado automaticamente",
                            'dados_alterados' => json_encode([
                                'acao_status_anterior' => $acao->getOriginal('status'),
                                'acao_status_novo' => 'FINALIZADO',
                                'projeto_finalizado' => true,
                                'data_finalizacao' => now()->toDateTimeString(),
                                'etapa_final' => $execucao->etapaFluxo->nome_etapa,
                                'trigger' => 'alteracao_status_aprovado'
                            ]),
                            'ip_usuario' => $request->ip(),
                            'user_agent' => $request->userAgent()
                        ]);
                    }
                }
            }

            // ===== NOVA LÓGICA DE TRANSIÇÕES (só se não finalizou) =====
            $transicaoExecutada = false;
            if (!$projetoFinalizadoAutomaticamente) {
                // Buscar transições configuradas para esta etapa e status
                $transicaoExecutada = $this->executarTransicoes($execucao, $request->status_id);
            }

            DB::commit();

            $mensagem = "Status alterado para {$novoStatus->nome} com sucesso";
            if ($projetoFinalizadoAutomaticamente) {
                $mensagem .= "! O projeto foi finalizado automaticamente, pois esta era a última etapa do fluxo.";
            } elseif ($transicaoExecutada) {
                $mensagem .= ". Fluxo direcionado para próxima etapa automaticamente.";
            }

            return response()->json([
                'success' => true,
                'message' => $mensagem,
                'transicao_executada' => $transicaoExecutada,
                'projeto_finalizado' => $projetoFinalizadoAutomaticamente,
                'data_finalizacao' => $projetoFinalizadoAutomaticamente ? $execucao->acao->fresh()->data_finalizacao->format('d/m/Y H:i') : null
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
        // Verificar se o usuário pode acessar esta ação (projeto)
        // Permite que todos os envolvidos no projeto vejam as opções
        if (!$this->canAccessAcao($execucao->acao)) {
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

    private function determinarEtapaAtual(Acao $acao, $execucoes = null)
    {
        // Se a ação está finalizada, não há etapa atual
        if ($acao->isFinalizado()) {
            \Log::info('Ação finalizada - nenhuma etapa atual.');
            return null;
        }
        // Se não foi passado execuções, buscar do banco
        if ($execucoes === null || $execucoes->isEmpty()) {
            $execucoes = ExecucaoEtapa::where('acao_id', $acao->id)
                ->with(['status'])
                ->get()
                ->keyBy('etapa_fluxo_id');
        }

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

    private function calcularPermissoes($user, $acao, $etapaAtual, $execucao = null)
    {
        $permissoes = [
            'pode_iniciar_etapa' => false,
            'pode_enviar_documento' => false,
            'pode_aprovar_documento' => false,
            'pode_concluir_etapa' => false,
            'pode_escolher_proxima_etapa' => false
        ];

        if (!$etapaAtual) {
            return $permissoes;
        }

        $userOrgId = $user->organizacao_id;
        
        // === VALIDAÇÃO DE INTERAÇÃO ===
        // Só permite ações se o usuário pode INTERAGIR com a etapa (considerando sequência)
        $podeInteragir = $this->podeInteragirComEtapa($acao, $etapaAtual);
        
        if ($podeInteragir) {
            // Pode iniciar etapa se for da organização solicitante e não existe execução
            if ($userOrgId === $etapaAtual->organizacao_solicitante_id && !$execucao) {
                $permissoes['pode_iniciar_etapa'] = true;
            }
            
            // PARANACIDADE sempre pode iniciar etapas (independente de ser solicitante)
            $organizacaoParanacidade = \App\Models\Organizacao::where('tipo', 'PARANACIDADE')->first();
            if ($organizacaoParanacidade && $userOrgId === $organizacaoParanacidade->id && !$execucao) {
                $permissoes['pode_iniciar_etapa'] = true;
            }

            // === PERMISSÕES PARA ETAPA JÁ INICIADA ===
            if ($execucao) {
                // NOVA ABORDAGEM: Ambas organizações podem enviar documentos
                if ($userOrgId === $etapaAtual->organizacao_executora_id || 
                    $userOrgId === $etapaAtual->organizacao_solicitante_id) {
                    $permissoes['pode_enviar_documento'] = $this->podeEnviarDocumento($execucao);
                }

                // Pode aprovar documentos e direcionar etapa se for da organização solicitante
                if ($userOrgId === $etapaAtual->organizacao_solicitante_id) {
                    $permissoes['pode_aprovar_documento'] = true;
                    $permissoes['pode_concluir_etapa'] = $this->podeConcluirEtapa($execucao);
                    
                    // NOVA ABORDAGEM: Sempre pode escolher próxima etapa (flexibilidade total)
                    // A validação de pendências será feita no dashboard, não como bloqueio
                    if ($execucao->status->codigo !== 'CANCELADO') {
                        $permissoes['pode_escolher_proxima_etapa'] = true;
                    }
                }
                
                // PARANACIDADE sempre pode direcionar etapa (independente de ser solicitante)
                $organizacaoParanacidade = \App\Models\Organizacao::where('tipo', 'PARANACIDADE')->first();
                if ($organizacaoParanacidade && $userOrgId === $organizacaoParanacidade->id) {
                    $permissoes['pode_aprovar_documento'] = true;
                    $permissoes['pode_concluir_etapa'] = $this->podeConcluirEtapa($execucao);
                    
                    if ($execucao->status->codigo !== 'CANCELADO') {
                        $permissoes['pode_escolher_proxima_etapa'] = true;
                    }
                }
            }
        }

        return $permissoes;
    }

    private function podeIniciarEtapa(Acao $acao, EtapaFluxo $etapaFluxo)
    {
        $user = Auth::user();
        
        // PARANACIDADE sempre pode iniciar qualquer etapa
        $organizacaoParanacidade = \App\Models\Organizacao::where('tipo', 'PARANACIDADE')->first();
        $isPARANACIDADE = ($organizacaoParanacidade && $user->organizacao_id === $organizacaoParanacidade->id);
        
        // Verificar se é da organização solicitante OU se é PARANACIDADE
        if ($user->organizacao_id !== $etapaFluxo->organizacao_solicitante_id && !$isPARANACIDADE) {
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
        
        // ===== REGRA UNIVERSAL: APENAS A ETAPA ATUAL PERMITE UPLOAD =====
        // Esta regra se aplica a TODOS os usuários, incluindo administradores
        $etapaFluxo = $execucao->etapaFluxo;
        $etapaAtualDoFluxo = $this->determinarEtapaAtual($execucao->acao);
        
        if (!$etapaAtualDoFluxo || $etapaAtualDoFluxo->id !== $etapaFluxo->id) {
            \Log::warning('Envio de documento negado - etapa não está ativa no fluxo (regra universal)', [
                'user_id' => $user->id,
                'user_role' => $user->roles->pluck('name')->toArray(),
                'execucao_id' => $execucao->id,
                'etapa_solicitada_id' => $etapaFluxo->id,
                'etapa_solicitada_nome' => $etapaFluxo->nome_etapa,
                'etapa_atual_id' => $etapaAtualDoFluxo ? $etapaAtualDoFluxo->id : null,
                'etapa_atual_nome' => $etapaAtualDoFluxo ? $etapaAtualDoFluxo->nome_etapa : 'Nenhuma',
                'motivo' => 'Apenas etapa atual permite upload - regra universal'
            ]);
            return false;
        }
        
        // ===== VERIFICAÇÃO DE ORGANIZAÇÃO =====
        $userOrgId = $user->organizacao_id;
        
        // CORREÇÃO: Apenas a organização EXECUTORA pode enviar documentos
        // Admins NÃO podem enviar documentos - apenas aprovar/reprovar
        $pertenceOrganizacaoExecutora = ($userOrgId === $etapaFluxo->organizacao_executora_id);
        
        // Verificar se é admin (mas sem permissão para envio)
        if ($user->hasRole(['admin', 'admin_paranacidade'])) {
            \Log::warning('Upload negado - admin não pode enviar documentos', [
                'user_id' => $user->id,
                'execucao_id' => $execucao->id,
                'etapa_nome' => $etapaFluxo->nome_etapa,
                'org_executora_id' => $etapaFluxo->organizacao_executora_id,
                'org_executora_nome' => $etapaFluxo->organizacaoExecutora->nome ?? 'N/A',
                'motivo' => 'Admin pode apenas aprovar/reprovar - não enviar documentos'
            ]);
            return false;
        }
        
        if (!$pertenceOrganizacaoExecutora) {
            \Log::warning('Usuário não pertence à organização EXECUTORA da etapa para envio de documento', [
                'user_id' => $user->id,
                'user_org_id' => $userOrgId,
                'user_org_nome' => $user->organizacao->nome ?? 'N/A',
                'org_executora_id' => $etapaFluxo->organizacao_executora_id,
                'org_executora_nome' => $etapaFluxo->organizacaoExecutora->nome ?? 'N/A',
                'execucao_id' => $execucao->id,
                'motivo' => 'Apenas organização executora pode enviar documentos'
            ]);
            return false;
        }
        
        // Apenas etapas CANCELADAS não permitem envio
        if ($execucao->status->codigo === 'CANCELADO') {
            \Log::warning('Etapa cancelada não permite envio de documento', [
                'execucao_id' => $execucao->id,
                'status_atual' => $execucao->status->codigo
            ]);
            return false;
        }
        
        \Log::info('Permissão para enviar documento concedida - organização executora na etapa atual', [
            'user_id' => $user->id,
            'user_org_id' => $userOrgId,
            'user_org_nome' => $user->organizacao->nome ?? 'N/A',
            'execucao_id' => $execucao->id,
            'org_executora_id' => $etapaFluxo->organizacao_executora_id,
            'org_executora_nome' => $etapaFluxo->organizacaoExecutora->nome ?? 'N/A',
            'etapa_ativa' => true,
            'motivo' => 'Usuário da organização executora pode enviar documentos'
        ]);
        
        return true;
    }

    private function podeAprovarDocumento(Documento $documento): bool
    {
        $user = Auth::user();
        $execucao = $documento->execucaoEtapa;
        $etapaFluxo = $execucao->etapaFluxo;
        
        // ===== REGRA UNIVERSAL: APENAS A ETAPA ATUAL PERMITE APROVAÇÃO =====
        // Esta regra se aplica a TODOS os usuários, incluindo administradores
        $etapaAtualDoFluxo = $this->determinarEtapaAtual($execucao->acao);
        
        if (!$etapaAtualDoFluxo || $etapaAtualDoFluxo->id !== $etapaFluxo->id) {
            \Log::warning('Aprovação de documento negada - etapa não está ativa no fluxo (regra universal)', [
                'user_id' => $user->id,
                'user_role' => $user->roles->pluck('name')->toArray(),
                'documento_id' => $documento->id,
                'etapa_solicitada_id' => $etapaFluxo->id,
                'etapa_solicitada_nome' => $etapaFluxo->nome_etapa,
                'etapa_atual_id' => $etapaAtualDoFluxo ? $etapaAtualDoFluxo->id : null,
                'etapa_atual_nome' => $etapaAtualDoFluxo ? $etapaAtualDoFluxo->nome_etapa : 'Nenhuma',
                'motivo' => 'Apenas etapa atual permite aprovacao - regra universal'
            ]);
            return false;
        }
        
        // ===== VERIFICAÇÃO DE ORGANIZAÇÃO =====
        // Admins podem aprovar documentos na etapa atual de qualquer organização
        if ($user->hasRole(['admin', 'admin_paranacidade'])) {
            // Documento deve estar pendente ou em análise
            if (!in_array($documento->status_documento, [Documento::STATUS_PENDENTE, Documento::STATUS_EM_ANALISE])) {
                return false;
            }
            
            \Log::info('Aprovação permitida - admin na etapa atual', [
                'user_id' => $user->id,
                'documento_id' => $documento->id,
                'etapa_nome' => $etapaFluxo->nome_etapa,
                'motivo' => 'Admin pode aprovar documento na etapa atual'
            ]);
            return true;
        }
        
        // PARANACIDADE sempre pode aprovar documentos (independente de ser solicitante)
        $organizacaoParanacidade = \App\Models\Organizacao::where('tipo', 'PARANACIDADE')->first();
        if ($organizacaoParanacidade && $user->organizacao_id === $organizacaoParanacidade->id) {
            // Documento deve estar pendente ou em análise
            if (!in_array($documento->status_documento, [Documento::STATUS_PENDENTE, Documento::STATUS_EM_ANALISE])) {
                return false;
            }
            
            \Log::info('Aprovação permitida - usuário PARANACIDADE na etapa atual', [
                'user_id' => $user->id,
                'documento_id' => $documento->id,
                'etapa_nome' => $etapaFluxo->nome_etapa,
                'motivo' => 'PARANACIDADE pode aprovar documento na etapa atual'
            ]);
            return true;
        }
        
        // Deve ser da organização solicitante
        if ($user->organizacao_id !== $etapaFluxo->organizacao_solicitante_id) {
            return false;
        }
        
        // Documento deve estar pendente ou em análise
        if (!in_array($documento->status_documento, [Documento::STATUS_PENDENTE, Documento::STATUS_EM_ANALISE])) {
            return false;
        }
        
        return true;
    }

    private function podeConcluirEtapa(ExecucaoEtapa $execucao): bool
    {
        $user = Auth::user();
        
        // PARANACIDADE sempre pode concluir etapas (independente de ser solicitante)
        $organizacaoParanacidade = \App\Models\Organizacao::where('tipo', 'PARANACIDADE')->first();
        if ($organizacaoParanacidade && $user->organizacao_id === $organizacaoParanacidade->id) {
            return $execucao->status->codigo !== 'CANCELADO';
        }
        
        // NOVA ABORDAGEM: Flexibilidade total
        // Deve ser da organização solicitante E etapa não pode estar cancelada
        return $user->organizacao_id === $execucao->etapaFluxo->organizacao_solicitante_id && 
               $execucao->status->codigo !== 'CANCELADO';
    }

    private function verificarConclusaoEtapa(ExecucaoEtapa $execucao)
    {
        // REMOVIDO: Aprovação automática da etapa quando todos documentos estão aprovados
        // Agora o usuário demandante deve escolher manualmente o destino da etapa
        
        // Esta função agora apenas verifica se todos os documentos estão aprovados
        // mas NÃO altera automaticamente o status da etapa
        
        \Log::info('Verificação de conclusão de etapa executada', [
            'execucao_id' => $execucao->id,
            'etapa_nome' => $execucao->etapaFluxo->nome_etapa,
            'status_atual' => $execucao->status->codigo,
            'observacao' => 'Aprovação automática removida - aguardando escolha manual do usuário'
        ]);
        
        // Não faz mais nada - a escolha é manual através do botão "Concluir Etapa"
    }

    /**
     * Verificar se todos os documentos obrigatórios estão aprovados
     */
    private function todosDocumentosAprovados(ExecucaoEtapa $execucao): bool
    {
        $grupoExigencia = $execucao->etapaFluxo->grupoExigencia;
        if (!$grupoExigencia) {
            return true; // Etapa sem documentos obrigatórios = considera aprovada
        }

        $templatesObrigatorios = $grupoExigencia->templatesDocumento()
            ->wherePivot('is_obrigatorio', true)
            ->get();

        if ($templatesObrigatorios->isEmpty()) {
            return true; // Não há documentos obrigatórios
        }

        foreach ($templatesObrigatorios as $template) {
            $documentoAprovado = $execucao->documentos()
                ->where('tipo_documento_id', $template->tipo_documento_id)
                ->where('status_documento', Documento::STATUS_APROVADO)
                ->exists();

            if (!$documentoAprovado) {
                return false; // Pelo menos um documento não está aprovado
            }
        }

        return true; // Todos os documentos obrigatórios estão aprovados
    }

    /**
     * Buscar opções de transição disponíveis para a etapa atual
     */
    public function getOpcoesTransicao(ExecucaoEtapa $execucao)
    {
        try {
            
            $user = Auth::user();
            
            // PARANACIDADE sempre pode escolher transições (independente de ser solicitante)
            $organizacaoParanacidade = \App\Models\Organizacao::where('tipo', 'PARANACIDADE')->first();
            $isPARANACIDADE = ($organizacaoParanacidade && $user->organizacao_id === $organizacaoParanacidade->id);
            
            // Verificar se o usuário pode escolher transições (deve ser da organização solicitante OU PARANACIDADE)
            if (!$user->hasRole(['admin', 'admin_paranacidade']) && 
                $user->organizacao_id !== $execucao->etapaFluxo->organizacao_solicitante_id &&
                !$isPARANACIDADE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas a organização solicitante ou PARANACIDADE pode escolher o destino da etapa'
                ], 403);
            }

            // NOVA ABORDAGEM: Não bloquear por documentos pendentes
            // Mostrar informação, mas permitir direcionamento (flexibilidade total)
            $documentosPendentes = !$this->todosDocumentosAprovados($execucao);
            if ($documentosPendentes) {
                \Log::info('Direcionamento permitido mesmo com documentos pendentes', [
                    'execucao_id' => $execucao->id,
                    'user_id' => $user->id,
                    'observacao' => 'Flexibilidade total - validação apenas informativa'
                ]);
            }

            // Buscar opções de status disponíveis para esta etapa
            $statusOpcoes = $execucao->etapaFluxo->statusOpcoes()
                ->where('etapa_status_opcoes.mostra_para_responsavel', true)
                ->get();

            if ($statusOpcoes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma opção de status disponível para esta etapa'
                ], 404);
            }

            $opcoesDisponiveis = [];
            
            foreach ($statusOpcoes as $status) {
                // $status é diretamente o model Status através do relacionamento
                
                // Verificar se é o status atual
                $isStatusAtual = $status->id == $execucao->status_id;
                
                // Buscar transições para este status
                $transicoes = TransicaoEtapa::where('etapa_fluxo_origem_id', $execucao->etapa_fluxo_id)
                    ->where('status_condicao_id', $status->id)
                    ->where('is_ativo', true)
                    ->with(['etapaDestino.organizacaoExecutora'])
                    ->orderBy('prioridade', 'desc')
                    ->get();

                // Se não há transições específicas para este status, buscar transições gerais (SEMPRE)
                if ($transicoes->isEmpty()) {
                    $transicoes = TransicaoEtapa::where('etapa_fluxo_origem_id', $execucao->etapa_fluxo_id)
                        ->where('condicao_tipo', 'SEMPRE')
                        ->where('is_ativo', true)
                        ->with(['etapaDestino.organizacaoExecutora'])
                        ->orderBy('prioridade', 'desc')
                        ->get();
                }

                // Se há transições configuradas para este status
                if (!$transicoes->isEmpty()) {
                    foreach ($transicoes as $transicao) {
                        // Para transições que voltam para a mesma etapa (correção), sempre permitir
                        if ($transicao->etapa_fluxo_destino_id == $execucao->etapa_fluxo_id) {
                            $descricao = $isStatusAtual 
                                ? ($transicao->descricao ?? "Manter status {$status->nome} (reprocessar)")
                                : ($transicao->descricao ?? "Alterar status para {$status->nome}");
                                
                            $opcoesDisponiveis[] = [
                                'transicao_id' => $transicao->id,
                                'status_id' => $status->id,
                                'status_nome' => $status->nome,
                                'status_cor' => $status->cor,
                                'etapa_destino_id' => null, // Não muda de etapa
                                'etapa_destino_nome' => 'Mesma etapa (correção)',
                                'descricao' => $descricao,
                                'status_condicao' => $status->nome,
                                'prioridade' => $transicao->prioridade,
                                'organizacao_executora' => $execucao->etapaFluxo->organizacaoExecutora->nome,
                                'requer_justificativa' => $status->pivot->requer_justificativa ?? false,
                                'tipo_operacao' => $isStatusAtual ? 'manter_status' : 'alterar_status',
                                'is_status_atual' => $isStatusAtual
                            ];
                        } else {
                            // Verificar se já existe execução para esta etapa destino
                            $execucaoExistente = ExecucaoEtapa::where('acao_id', $execucao->acao_id)
                                ->where('etapa_fluxo_id', $transicao->etapa_fluxo_destino_id)
                                ->first();

                            // CORREÇÃO: Verificar se é transição de "voltar" baseada no nome do status
                            $isTransicaoVoltar = stripos($status->nome, 'voltar') !== false || 
                                               stripos($status->codigo, 'VOLTAR') !== false ||
                                               stripos($transicao->descricao ?? '', 'voltar') !== false;

                            // NOVA ABORDAGEM: Permitir TODAS as transições configuradas independente de já ter sido executada
                            // Isso dá flexibilidade total para navegar no fluxo (pode voltar ou avançar)
                            $permitirTransicao = true; // Flexibilidade total

                            if ($permitirTransicao) {
                                $descricao = '';
                                $tipoOperacao = 'iniciar_etapa';
                                
                                if ($execucaoExistente) {
                                    // Etapa já foi executada - será reativada
                                    if ($isTransicaoVoltar) {
                                        $descricao = $isStatusAtual 
                                            ? ($transicao->descricao ?? "Manter status {$status->nome} e retornar para {$transicao->etapaDestino->nome_etapa}")
                                            : ($transicao->descricao ?? "Alterar status para {$status->nome} e retornar para {$transicao->etapaDestino->nome_etapa}");
                                        $tipoOperacao = 'reativar_etapa';
                                    } else {
                                        $descricao = $isStatusAtual 
                                            ? ($transicao->descricao ?? "Manter status {$status->nome} e reativar etapa {$transicao->etapaDestino->nome_etapa}")
                                            : ($transicao->descricao ?? "Alterar status para {$status->nome} e reativar etapa {$transicao->etapaDestino->nome_etapa}");
                                        $tipoOperacao = 'reativar_etapa';
                                    }
                                } else {
                                    // Etapa ainda não foi executada - criação normal
                                    $descricao = $isStatusAtual 
                                        ? ($transicao->descricao ?? "Manter status {$status->nome} e prosseguir para {$transicao->etapaDestino->nome_etapa}")
                                        : ($transicao->descricao ?? "Alterar status para {$status->nome} e prosseguir para {$transicao->etapaDestino->nome_etapa}");
                                    $tipoOperacao = 'iniciar_etapa';
                                }
                                
                                $opcoesDisponiveis[] = [
                                    'transicao_id' => $transicao->id,
                                    'status_id' => $status->id,
                                    'status_nome' => $status->nome,
                                    'status_cor' => $status->cor,
                                    'etapa_destino_id' => $transicao->etapa_fluxo_destino_id,
                                    'etapa_destino_nome' => $transicao->etapaDestino->nome_etapa,
                                    'descricao' => $descricao,
                                    'status_condicao' => $status->nome,
                                    'prioridade' => $transicao->prioridade,
                                    'organizacao_executora' => $transicao->etapaDestino->organizacaoExecutora->nome,
                                    'requer_justificativa' => $status->pivot->requer_justificativa ?? false,
                                    'tipo_operacao' => $tipoOperacao,
                                    'is_status_atual' => $isStatusAtual,
                                    'is_voltar' => $isTransicaoVoltar,
                                    'etapa_ja_executada' => !is_null($execucaoExistente),
                                    'flexibilidade_total' => true
                                ];
                                
                                \Log::info('Transição adicionada às opções (flexibilidade total)', [
                                    'transicao_id' => $transicao->id,
                                    'status_nome' => $status->nome,
                                    'etapa_destino' => $transicao->etapaDestino->nome_etapa,
                                    'is_voltar' => $isTransicaoVoltar,
                                    'etapa_ja_executada' => !is_null($execucaoExistente),
                                    'tipo_operacao' => $tipoOperacao,
                                    'permite_reativacao' => true
                                ]);
                            }
                        }
                    }
                } else {
                    // Se não há transições configuradas, apenas alterar o status (ou manter se for o atual)
                    if (!$isStatusAtual) { // Só mostrar se não for o status atual
                        $opcoesDisponiveis[] = [
                            'transicao_id' => null,
                            'status_id' => $status->id,
                            'status_nome' => $status->nome,
                            'status_cor' => $status->cor,
                            'etapa_destino_id' => null,
                            'etapa_destino_nome' => 'Finalizar Ação',
                            'descricao' => "Alterar status para {$status->nome}",
                            'status_condicao' => $status->nome,
                            'prioridade' => 1,
                            'organizacao_executora' => $execucao->etapaFluxo->organizacaoExecutora->nome,
                            'requer_justificativa' => $status->pivot->requer_justificativa ?? false,
                            'tipo_operacao' => 'alterar_status', // Só altera status
                            'is_status_atual' => false
                        ];
                    }
                }
            }

            // Se não há transições configuradas, buscar próxima etapa sequencial
            if (empty($opcoesDisponiveis)) {
                $proximaEtapaSequencial = EtapaFluxo::where('tipo_fluxo_id', $execucao->etapaFluxo->tipo_fluxo_id)
                    ->where('ordem_execucao', '>', $execucao->etapaFluxo->ordem_execucao)
                    ->orderBy('ordem_execucao')
                    ->with('organizacaoExecutora')
                    ->first();

                if ($proximaEtapaSequencial) {
                    // Verificar se já existe execução para esta etapa
                    $execucaoExistente = ExecucaoEtapa::where('acao_id', $execucao->acao_id)
                        ->where('etapa_fluxo_id', $proximaEtapaSequencial->id)
                        ->first();

                    if (!$execucaoExistente) {
                        $statusAprovado = Status::where('codigo', 'APROVADO')->first();
                        
                        $opcoesDisponiveis[] = [
                            'transicao_id' => null, // Transição sequencial automática
                            'status_id' => $statusAprovado->id,
                            'status_nome' => $statusAprovado->nome,
                            'status_cor' => $statusAprovado->cor,
                            'etapa_destino_id' => $proximaEtapaSequencial->id,
                            'etapa_destino_nome' => $proximaEtapaSequencial->nome_etapa,
                            'descricao' => "Aprovar e prosseguir para próxima etapa: {$proximaEtapaSequencial->nome_etapa}",
                            'status_condicao' => 'Aprovado',
                            'prioridade' => 10,
                            'organizacao_executora' => $proximaEtapaSequencial->organizacaoExecutora->nome,
                            'requer_justificativa' => false,
                            'tipo_operacao' => 'iniciar_etapa'
                        ];
                    }
                } else {
                    // === VERIFICAR SE É A ÚLTIMA ETAPA DO FLUXO - OPÇÃO DE FINALIZAR ===
                    // CORREÇÃO: Verificar se é realmente a última etapa do fluxo (maior ordem_execucao)
                    $acao = $execucao->acao;
                    $ultimaEtapaDoFluxo = EtapaFluxo::where('tipo_fluxo_id', $acao->tipo_fluxo_id)
                        ->orderBy('ordem_execucao', 'desc')
                        ->first();
                    
                    $isUltimaEtapaDoFluxo = $ultimaEtapaDoFluxo && $ultimaEtapaDoFluxo->id === $execucao->etapa_fluxo_id;
                    
                    if ($isUltimaEtapaDoFluxo && !$acao->isFinalizado()) {
                        $statusFinalizado = Status::where('codigo', 'FINALIZADO')->first();
                        
                        if ($statusFinalizado) {
                            $opcoesDisponiveis[] = [
                                'transicao_id' => null,
                                'status_id' => $statusFinalizado->id,
                                'status_nome' => $statusFinalizado->nome,
                                'status_cor' => $statusFinalizado->cor,
                                'etapa_destino_id' => null,
                                'etapa_destino_nome' => 'Finalizar Ação',
                                'descricao' => 'Finalizar projeto - não poderá mais ser alterado (apenas admins podem reativar)',
                                'status_condicao' => 'Finalizado',
                                'prioridade' => 20,
                                'organizacao_executora' => 'Sistema',
                                'requer_justificativa' => false,
                                'tipo_operacao' => 'finalizar_projeto',
                                'is_finalizar' => true
                            ];
                        }
                    }
                }
            }

            // === ADICIONAR OPÇÃO DE FINALIZAR APENAS NA ÚLTIMA ETAPA DO FLUXO ===
            $acao = $execucao->acao;
            
            // CORREÇÃO: Verificar se é realmente a última etapa do fluxo (maior ordem_execucao)
            $ultimaEtapaDoFluxo = EtapaFluxo::where('tipo_fluxo_id', $acao->tipo_fluxo_id)
                ->orderBy('ordem_execucao', 'desc')
                ->first();
            
            $isUltimaEtapaDoFluxo = $ultimaEtapaDoFluxo && $ultimaEtapaDoFluxo->id === $execucao->etapa_fluxo_id;
            
            if ($isUltimaEtapaDoFluxo && !$acao->isFinalizado()) {
                $statusFinalizado = Status::where('codigo', 'FINALIZADO')->first();
                
                if ($statusFinalizado) {
                    // Verificar se já existe uma opção de finalizar
                    $jaTemOpcaoFinalizar = false;
                    foreach ($opcoesDisponiveis as $opcao) {
                        if ($opcao['tipo_operacao'] === 'finalizar_projeto') {
                            $jaTemOpcaoFinalizar = true;
                            break;
                        }
                    }
                    
                    // Se não tem opção de finalizar, adicionar uma
                    if (!$jaTemOpcaoFinalizar) {
                        $opcoesDisponiveis[] = [
                            'transicao_id' => 'finalizar', // ID especial para identificar
                            'status_id' => $statusFinalizado->id,
                            'status_nome' => $statusFinalizado->nome,
                            'status_cor' => $statusFinalizado->cor,
                            'etapa_destino_id' => null,
                            'etapa_destino_nome' => 'Finalizar Projeto',
                            'descricao' => 'Finalizar projeto completamente - todas as etapas puladas serão marcadas como não aplicáveis',
                            'status_condicao' => 'Finalizado',
                            'prioridade' => 100, // Prioridade alta para aparecer no topo
                            'organizacao_executora' => 'Sistema',
                            'requer_justificativa' => false,
                            'tipo_operacao' => 'finalizar_projeto',
                            'is_finalizar' => true
                        ];
                        
                        \Log::info('Opção de finalizar adicionada na última etapa do fluxo', [
                            'execucao_id' => $execucao->id,
                            'acao_id' => $acao->id,
                            'etapa_atual' => $execucao->etapaFluxo->nome_etapa,
                            'etapa_atual_ordem' => $execucao->etapaFluxo->ordem_execucao,
                            'ultima_etapa_ordem' => $ultimaEtapaDoFluxo->ordem_execucao,
                            'is_ultima_etapa' => $isUltimaEtapaDoFluxo
                        ]);
                    }
                }
            }

            if (empty($opcoesDisponiveis)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Todas as etapas destino já foram iniciadas ou não há próximas etapas configuradas'
                ], 404);
            }

            // Ordenar opções por prioridade (maior prioridade primeiro)
            usort($opcoesDisponiveis, function($a, $b) {
                return $b['prioridade'] - $a['prioridade'];
            });

            return response()->json([
                'success' => true,
                'opcoes' => $opcoesDisponiveis,
                'etapa_atual' => [
                    'id' => $execucao->etapa_fluxo_id,
                    'nome' => $execucao->etapaFluxo->nome_etapa
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar opções de transição', [
                'execucao_id' => $execucao->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Executar transição escolhida pelo usuário
     */
    public function executarTransicaoEscolhida(Request $request, ExecucaoEtapa $execucao)
    {
        // DEBUG: Log do que está sendo recebido
        \Log::info('=== DEBUG executarTransicaoEscolhida ===', [
            'execucao_id' => $execucao->id,
            'etapa_nome' => $execucao->etapaFluxo->nome_etapa,
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        $request->validate([
            'transicao_id' => 'nullable|exists:transicao_etapas,id',
            'status_id' => 'required|exists:status,id',
            'etapa_destino_id' => 'nullable|exists:etapa_fluxo,id',
            'tipo_operacao' => 'required|in:alterar_status,iniciar_etapa,manter_status,voltar_etapa,reativar_etapa,finalizar_projeto',
            'observacoes' => 'nullable|string|max:1000'
        ]);

        try {
            $user = Auth::user();
            
            // Verificar se o usuário pode executar transições
            // PARANACIDADE sempre pode executar transições (independente de ser solicitante)
            $organizacaoParanacidade = \App\Models\Organizacao::where('tipo', 'PARANACIDADE')->first();
            $isPARANACIDADE = ($organizacaoParanacidade && $user->organizacao_id === $organizacaoParanacidade->id);
            
            if (!$user->hasRole(['admin', 'admin_paranacidade']) && 
                $user->organizacao_id !== $execucao->etapaFluxo->organizacao_solicitante_id &&
                !$isPARANACIDADE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas a organização solicitante ou PARANACIDADE pode escolher o destino da etapa'
                ], 403);
            }

            // NOVA ABORDAGEM: Permitir transição mesmo com documentos pendentes
            // A validação será apenas informativa no dashboard
            $documentosPendentes = !$this->todosDocumentosAprovados($execucao);
            if ($documentosPendentes) {
                \Log::info('Transição executada mesmo com documentos pendentes', [
                    'execucao_id' => $execucao->id,
                    'user_id' => $user->id,
                    'observacao' => 'Sistema flexível - permitindo transição'
                ]);
            }

            // Buscar o novo status
            $novoStatus = Status::findOrFail($request->status_id);
            
            // === TRATAMENTO ESPECIAL PARA FINALIZAÇÃO DE PROJETO ===
            if ($request->tipo_operacao === 'finalizar_projeto') {
                \Log::info('FINALIZAR PROJETO: Bloco de finalização iniciado.', ['request' => $request->all()]);

                $acao = $execucao->acao;
                
                // Verificar se pode finalizar
                if ($acao->isFinalizado()) {
                    \Log::warning('FINALIZAR PROJETO: Tentativa de finalizar projeto já finalizado.', ['acao_id' => $acao->id]);
                    return response()->json([
                        'success' => false,
                        'message' => 'O projeto já está finalizado.'
                    ], 400);
                }

                if (!$acao->isNaUltimaEtapa()) {
                    \Log::warning('FINALIZAR PROJETO: Tentativa de finalizar fora da última etapa.', ['acao_id' => $acao->id]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Só é possível finalizar o projeto na última etapa do fluxo.'
                    ], 400);
                }

                // Verificar se o status escolhido é FINALIZADO
                if ($novoStatus->codigo !== 'FINALIZADO') {
                    \Log::warning('FINALIZAR PROJETO: Status escolhido não é FINALIZADO.', ['status_codigo' => $novoStatus->codigo]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Para finalizar o projeto, é necessário usar o status FINALIZADO.'
                    ], 400);
                }

                DB::beginTransaction();
                \Log::info('FINALIZAR PROJETO: Transação iniciada.');
                try {
                    // 1. Marcar etapa atual como concluída com status FINALIZADO
                    $statusAnterior = $execucao->status;
                    $execucao->update([
                        'status_id' => $novoStatus->id,
                        'data_conclusao' => now(),
                        'observacoes' => $request->observacoes,
                        'updated_by' => $user->id
                    ]);
                    \Log::info('FINALIZAR PROJETO: Execução da etapa atual marcada como concluída.', ['execucao_id' => $execucao->id]);

                    // 2. Finalizar o projeto usando o método do modelo
                    \Log::info('FINALIZAR PROJETO: Chamando $acao->finalizar()...', ['acao_id' => $acao->id]);
                    $acao->finalizar($user, $request->observacoes);
                    \Log::info('FINALIZAR PROJETO: Método $acao->finalizar() concluído.');

                    // 3. Registrar no histórico
                    HistoricoEtapa::create([
                        'execucao_etapa_id' => $execucao->id,
                        'usuario_id' => $user->id,
                        'status_anterior_id' => $statusAnterior->id,
                        'status_novo_id' => $novoStatus->id,
                        'acao' => 'FINALIZACAO_PROJETO',
                        'descricao_acao' => "Projeto finalizado - Etapa concluída com status FINALIZADO",
                        'observacao' => $request->observacoes,
                        'dados_alterados' => json_encode([
                            'status_anterior' => $statusAnterior->nome,
                            'status_novo' => $novoStatus->nome,
                            'tipo_operacao' => 'finalizar_projeto',
                            'projeto_finalizado' => true,
                            'data_finalizacao' => now()->toDateTimeString()
                        ]),
                        'ip_usuario' => request()->ip(),
                        'user_agent' => request()->userAgent()
                    ]);
                    \Log::info('FINALIZAR PROJETO: Histórico registrado.');

                    DB::commit();
                    \Log::info('FINALIZAR PROJETO: Transação commitada com sucesso!');

                    return response()->json([
                        'success' => true,
                        'message' => 'Projeto finalizado com sucesso! O projeto não poderá mais ser alterado.',
                        'projeto_finalizado' => true,
                        'data_finalizacao' => $acao->fresh()->data_finalizacao->format('d/m/Y H:i'),
                        'usuario_finalizacao' => $user->name
                    ]);

                } catch (\Exception $e) {
                    DB::rollback();
                    \Log::error('Erro ao finalizar projeto', [
                        'execucao_id' => $execucao->id,
                        'acao_id' => $acao->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro ao finalizar projeto: ' . $e->getMessage()
                    ], 500);
                }
                
                // IMPORTANTE: Quando projeto é finalizado, interromper execução aqui
                // Não deve continuar o fluxo nem criar novas etapas
                return;
            }
            
            // Buscar etapa destino apenas se for para iniciar nova etapa
            $etapaDestino = null;
            if (in_array($request->tipo_operacao, ['iniciar_etapa', 'voltar_etapa', 'reativar_etapa']) && $request->etapa_destino_id) {
                $etapaDestino = EtapaFluxo::with('organizacaoExecutora')->findOrFail($request->etapa_destino_id);
            }

            // Se há transição específica, verificar se pertence à etapa atual
            $transicao = null;
            if ($request->transicao_id) {
                $transicao = TransicaoEtapa::with(['etapaDestino', 'statusCondicao'])
                    ->findOrFail($request->transicao_id);

                if ($transicao->etapa_fluxo_origem_id !== $execucao->etapa_fluxo_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Transição não pertence à etapa atual'
                    ], 400);
                }
            }

            // Verificar se já existe execução para a etapa destino (apenas se for iniciar nova etapa)
            if ($etapaDestino) {
                $execucaoExistente = ExecucaoEtapa::where('acao_id', $execucao->acao_id)
                    ->where('etapa_fluxo_id', $etapaDestino->id)
                    ->first();

                // CORREÇÃO: Verificar se é transição de "voltar"
                $isTransicaoVoltar = false;
                if ($request->tipo_operacao === 'voltar_etapa' || 
                    $request->tipo_operacao === 'reativar_etapa' ||
                    ($novoStatus && (stripos($novoStatus->nome, 'voltar') !== false || stripos($novoStatus->codigo, 'VOLTAR') !== false))) {
                    $isTransicaoVoltar = true;
                }

                // Se já existe execução E não é transição de voltar, rejeitar
                if ($execucaoExistente && !$isTransicaoVoltar) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A etapa destino já foi iniciada'
                    ], 400);
                }

                // Se é transição de voltar e já existe execução, vamos REATIVAR ao invés de criar nova
                if ($execucaoExistente && $isTransicaoVoltar) {
                    \Log::info('Reativando etapa existente através de transição de voltar', [
                        'execucao_origem_id' => $execucao->id,
                        'execucao_destino_id' => $execucaoExistente->id,
                        'user_id' => $user->id,
                        'status_escolhido' => $novoStatus->nome
                    ]);
                    
                    // Marcar a flag para reativação
                    $request->merge(['reativar_execucao_existente' => $execucaoExistente->id]);
                }
            }

            DB::beginTransaction();

            // 1. Atualizar status da etapa atual
            $statusAnterior = $execucao->status;
            $datasConclusao = [];
            
            if (in_array($request->tipo_operacao, ['iniciar_etapa', 'voltar_etapa', 'reativar_etapa'])) {
                // Se for iniciar nova etapa ou voltar, marcar como concluída
                $datasConclusao['data_conclusao'] = now();
            }
            
            $dadosAtualizacao = [
                'observacoes' => $request->observacoes,
                'updated_by' => $user->id
            ];
            
            // Só atualizar status se não for manter o atual
            if ($request->tipo_operacao !== 'manter_status') {
                $dadosAtualizacao['status_id'] = $novoStatus->id;
            }
            
            $execucao->update(array_merge($dadosAtualizacao, $datasConclusao));

            $novaExecucao = null;
            $execucaoReativada = null;

            // 2. Criar nova execução ou reativar existente
            if (in_array($request->tipo_operacao, ['iniciar_etapa', 'voltar_etapa', 'reativar_etapa']) && $etapaDestino) {
                
                // Verificar se deve reativar execução existente (transições de voltar)
                if ($request->has('reativar_execucao_existente')) {
                    $execucaoReativada = ExecucaoEtapa::findOrFail($request->reativar_execucao_existente);
                    
                    // Reativar a execução existente
                    $statusPendente = Status::where('codigo', 'PENDENTE')->first();
                    $execucaoReativada->update([
                        'status_id' => $statusPendente->id,
                        'data_inicio' => now(),
                        'data_prazo' => $etapaDestino->calcularDataPrazo(),
                        'data_conclusao' => null, // Limpar conclusão anterior
                        'etapa_anterior_id' => $execucao->id, // Atualizar referência
                        'motivo_transicao' => $transicao 
                            ? "REATIVAÇÃO - Transição de voltar: {$transicao->descricao}"
                            : "REATIVAÇÃO - Retorno para etapa: {$etapaDestino->nome_etapa}",
                        'updated_by' => $user->id
                    ]);
                    
                    \Log::info('Etapa reativada com sucesso', [
                        'execucao_reativada_id' => $execucaoReativada->id,
                        'execucao_origem_id' => $execucao->id,
                        'etapa_destino' => $etapaDestino->nome_etapa,
                        'user_id' => $user->id
                    ]);
                    
                } else {
                    // Criar nova execução (fluxo normal)
                    $statusPendente = Status::where('codigo', 'PENDENTE')->first();
                    
                    $motivoTransicao = $transicao 
                        ? "Transição escolhida pelo usuário: {$transicao->descricao}"
                        : "Transição sequencial escolhida pelo usuário para: {$etapaDestino->nome_etapa}";
                    
                    $novaExecucao = ExecucaoEtapa::create([
                        'acao_id' => $execucao->acao_id,
                        'etapa_fluxo_id' => $etapaDestino->id,
                        'status_id' => $statusPendente->id,
                        'data_inicio' => now(),
                        'data_prazo' => $etapaDestino->calcularDataPrazo(),
                        'etapa_anterior_id' => $execucao->id,
                        'motivo_transicao' => $motivoTransicao,
                        'created_by' => $user->id
                    ]);
                }
            }

            // 3. Registrar no histórico da etapa atual
            $acaoHistorico = in_array($request->tipo_operacao, ['iniciar_etapa', 'voltar_etapa', 'reativar_etapa']) ? 'CONCLUSAO_ETAPA' : 
                           ($request->tipo_operacao === 'manter_status' ? 'REPROCESSAMENTO' : 'ALTERACAO_STATUS');
            
            $descricaoAcao = match($request->tipo_operacao) {
                'iniciar_etapa' => "Etapa concluída com status '{$novoStatus->nome}'" . ($etapaDestino ? " e direcionada para: {$etapaDestino->nome_etapa}" : ""),
                'voltar_etapa', 'reativar_etapa' => "Etapa concluída com status '{$novoStatus->nome}'" . ($etapaDestino ? " e retornada para: {$etapaDestino->nome_etapa}" : ""),
                'manter_status' => "Status mantido como '{$statusAnterior->nome}' para reprocessamento",
                'alterar_status' => "Status alterado para '{$novoStatus->nome}'"
            };

            $dadosAlterados = [
                'status_anterior' => $statusAnterior->nome,
                'status_novo' => $request->tipo_operacao === 'manter_status' ? $statusAnterior->nome : $novoStatus->nome,
                'tipo_operacao' => $request->tipo_operacao,
                'transicao_id' => $transicao ? $transicao->id : null
            ];

            if ($etapaDestino) {
                $dadosAlterados['etapa_destino'] = $etapaDestino->nome_etapa;
            }
            
            if ($novaExecucao) {
                $dadosAlterados['nova_execucao_id'] = $novaExecucao->id;
            }
            
            if ($execucaoReativada) {
                $dadosAlterados['execucao_reativada_id'] = $execucaoReativada->id;
                $dadosAlterados['tipo_transicao'] = 'reativacao';
            }

            HistoricoEtapa::create([
                'execucao_etapa_id' => $execucao->id,
                'usuario_id' => $user->id,
                'status_anterior_id' => $statusAnterior->id,
                'status_novo_id' => $request->tipo_operacao === 'manter_status' ? $statusAnterior->id : $novoStatus->id,
                'acao' => $acaoHistorico,
                'descricao_acao' => $descricaoAcao,
                'observacao' => $request->observacoes,
                'dados_alterados' => json_encode($dadosAlterados),
                'ip_usuario' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // 4. Registrar no histórico da nova etapa (apenas se foi criada)
            if ($novaExecucao) {
                HistoricoEtapa::create([
                    'execucao_etapa_id' => $novaExecucao->id,
                    'usuario_id' => $user->id,
                    'acao' => 'INICIO_ETAPA',
                    'descricao_acao' => "Etapa iniciada através de transição da etapa: {$execucao->etapaFluxo->nome_etapa}",
                    'observacao' => $transicao ? "Transição executada: {$transicao->descricao}" : "Transição sequencial",
                    'dados_alterados' => json_encode([
                        'etapa_origem' => $execucao->etapaFluxo->nome_etapa,
                        'transicao_id' => $transicao ? $transicao->id : null,
                        'usuario_decisao' => $user->name,
                        'status_origem' => $novoStatus->nome
                    ]),
                    'ip_usuario' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }

            // 5. Registrar no histórico da etapa reativada (se aplicável)
            if ($execucaoReativada) {
                HistoricoEtapa::create([
                    'execucao_etapa_id' => $execucaoReativada->id,
                    'usuario_id' => $user->id,
                    'acao' => 'REATIVACAO_ETAPA',
                    'descricao_acao' => "Etapa reativada através de transição de voltar da etapa: {$execucao->etapaFluxo->nome_etapa}",
                    'observacao' => $transicao ? "Transição de voltar executada: {$transicao->descricao}" : "Retorno para etapa anterior",
                    'dados_alterados' => json_encode([
                        'etapa_origem' => $execucao->etapaFluxo->nome_etapa,
                        'transicao_id' => $transicao ? $transicao->id : null,
                        'usuario_decisao' => $user->name,
                        'status_origem' => $novoStatus->nome,
                        'tipo_operacao' => 'reativacao'
                    ]),
                    'ip_usuario' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }

            DB::commit();

            $responseData = [
                'success' => true,
                'tipo_operacao' => $request->tipo_operacao
            ];

            if (in_array($request->tipo_operacao, ['iniciar_etapa', 'voltar_etapa', 'reativar_etapa']) && ($novaExecucao || $execucaoReativada)) {
                if ($execucaoReativada) {
                    $responseData['message'] = "Etapa concluída com sucesso! Etapa '{$etapaDestino->nome_etapa}' foi reativada.";
                    $responseData['etapa_reativada'] = [
                        'id' => $execucaoReativada->id,
                        'nome' => $etapaDestino->nome_etapa,
                        'organizacao_executora' => $etapaDestino->organizacaoExecutora->nome,
                        'tipo' => 'reativacao'
                    ];
                } else {
                    $mensagem = in_array($request->tipo_operacao, ['voltar_etapa', 'reativar_etapa']) 
                        ? "Etapa concluída com sucesso! Retornando para etapa '{$etapaDestino->nome_etapa}'."
                        : "Etapa concluída com sucesso! Próxima etapa '{$etapaDestino->nome_etapa}' foi iniciada.";
                    
                    $responseData['message'] = $mensagem;
                    $responseData['proxima_etapa'] = [
                        'id' => $novaExecucao->id,
                        'nome' => $etapaDestino->nome_etapa,
                        'organizacao_executora' => $etapaDestino->organizacaoExecutora->nome
                    ];
                }
            } else if ($request->tipo_operacao === 'manter_status') {
                $responseData['message'] = "Status mantido como '{$statusAnterior->nome}' para reprocessamento!";
                $responseData['status_atual'] = [
                    'id' => $statusAnterior->id,
                    'nome' => $statusAnterior->nome,
                    'cor' => $statusAnterior->cor
                ];
            } else {
                $responseData['message'] = "Status alterado para '{$novoStatus->nome}' com sucesso!";
                $responseData['status_atual'] = [
                    'id' => $novoStatus->id,
                    'nome' => $novoStatus->nome,
                    'cor' => $novoStatus->cor
                ];
            }

            return response()->json($responseData);

        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Erro ao executar transição escolhida', [
                'execucao_id' => $execucao->id,
                'transicao_id' => $request->transicao_id,
                'status_id' => $request->status_id,
                'etapa_destino_id' => $request->etapa_destino_id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar transição: ' . $e->getMessage()
            ], 500);
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
        
        // ===== REGRA UNIVERSAL: APENAS A ETAPA ATUAL PERMITE ALTERAÇÃO DE STATUS =====
        // Esta regra se aplica a TODOS os usuários, incluindo administradores
        $etapaFluxo = $execucao->etapaFluxo;
        $etapaAtualDoFluxo = $this->determinarEtapaAtual($execucao->acao);
        
        if (!$etapaAtualDoFluxo || $etapaAtualDoFluxo->id !== $etapaFluxo->id) {
            \Log::warning('Alteração de status negada - etapa não está ativa no fluxo (regra universal)', [
                'user_id' => $user->id,
                'user_role' => $user->roles->pluck('name')->toArray(),
                'execucao_id' => $execucao->id,
                'etapa_solicitada_id' => $etapaFluxo->id,
                'etapa_solicitada_nome' => $etapaFluxo->nome_etapa,
                'etapa_atual_id' => $etapaAtualDoFluxo ? $etapaAtualDoFluxo->id : null,
                'etapa_atual_nome' => $etapaAtualDoFluxo ? $etapaAtualDoFluxo->nome_etapa : 'Nenhuma',
                'motivo' => 'Apenas etapa atual permite alteracao de status - regra universal'
            ]);
            return false;
        }
        
        // ===== VERIFICAÇÃO DE ORGANIZAÇÃO =====
        $userOrgId = $user->organizacao_id;
        
        // Admins podem alterar status da etapa atual de qualquer organização
        if ($user->hasRole(['admin', 'admin_paranacidade'])) {
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
            
            \Log::info('Alteração de status permitida - admin na etapa atual', [
                'user_id' => $user->id,
                'execucao_id' => $execucao->id,
                'etapa_nome' => $etapaFluxo->nome_etapa,
                'status_atual' => $execucao->status->codigo,
                'motivo' => 'Admin pode alterar status na etapa atual'
            ]);
            return true;
        }
        
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

        \Log::info('Permissão para alterar status na etapa atual concedida', [
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

    /**
     * Verificar se uma etapa pode ser acessada baseado na sequência do workflow
     */
    private function podeAcessarEtapa(Acao $acao, EtapaFluxo $etapaFluxo): bool
    {
        $user = Auth::user();
        
        // Admins do sistema e Paranacidade sempre podem acessar qualquer etapa
        if ($user->hasRole(['admin', 'admin_paranacidade'])) {
            return true;
        }

        // Verificar se o usuário pertence às organizações envolvidas nesta etapa
        $userOrgId = $user->organizacao_id;
        $pertenceEtapa = ($userOrgId === $etapaFluxo->organizacao_solicitante_id) || 
                        ($userOrgId === $etapaFluxo->organizacao_executora_id);
        
        if (!$pertenceEtapa) {
            \Log::warning('Usuário não pertence às organizações da etapa', [
                'user_id' => $user->id,
                'user_org_id' => $userOrgId,
                'org_solicitante_id' => $etapaFluxo->organizacao_solicitante_id,
                'org_executora_id' => $etapaFluxo->organizacao_executora_id,
                'etapa_id' => $etapaFluxo->id
            ]);
            return false;
        }

        // Se for a primeira etapa (ordem_execucao = 1), sempre pode acessar
        if ($etapaFluxo->ordem_execucao <= 1) {
            return true;
        }

        // Verificar se todas as etapas anteriores foram concluídas
        $etapasAnteriores = EtapaFluxo::where('tipo_fluxo_id', $etapaFluxo->tipo_fluxo_id)
            ->where('ordem_execucao', '<', $etapaFluxo->ordem_execucao)
            ->orderBy('ordem_execucao')
            ->get();

        foreach ($etapasAnteriores as $etapaAnterior) {
            $execucaoAnterior = ExecucaoEtapa::where('acao_id', $acao->id)
                ->where('etapa_fluxo_id', $etapaAnterior->id)
                ->first();

            // Se não há execução OU a execução não está aprovada, não pode acessar
            if (!$execucaoAnterior || $execucaoAnterior->status->codigo !== 'APROVADO') {
                \Log::info('Etapa anterior não concluída - acesso negado', [
                    'etapa_atual_id' => $etapaFluxo->id,
                    'etapa_atual_ordem' => $etapaFluxo->ordem_execucao,
                    'etapa_anterior_id' => $etapaAnterior->id,
                    'etapa_anterior_ordem' => $etapaAnterior->ordem_execucao,
                    'execucao_anterior_status' => $execucaoAnterior ? $execucaoAnterior->status->codigo : 'NAO_INICIADA',
                    'user_id' => $user->id
                ]);
                return false;
            }
        }

        \Log::info('Acesso à etapa permitido - sequência respeitada', [
            'user_id' => $user->id,
            'etapa_id' => $etapaFluxo->id,
            'etapa_ordem' => $etapaFluxo->ordem_execucao,
            'etapas_anteriores_concluidas' => $etapasAnteriores->count()
        ]);

        return true;
    }

    /**
     * Verificar se um usuário pode VISUALIZAR uma etapa (mais permissivo que interagir)
     */
    private function podeVisualizarEtapa(Acao $acao, EtapaFluxo $etapaFluxo): bool
    {
        $user = Auth::user();
        
        // Admins do sistema e Paranacidade sempre podem visualizar qualquer etapa
        if ($user->hasRole(['admin', 'admin_paranacidade'])) {
            return true;
        }

        // ===== NOVA LÓGICA: Se o usuário pode acessar a AÇÃO, pode visualizar TODAS as etapas =====
        // Isso permite que todos os envolvidos no projeto vejam todas as etapas
        if ($this->canAccessAcao($acao)) {
            \Log::info('Visualização da etapa permitida - usuário envolvido no projeto', [
                'user_id' => $user->id,
                'user_org_id' => $user->organizacao_id,
                'acao_id' => $acao->id,
                'etapa_id' => $etapaFluxo->id,
                'etapa_nome' => $etapaFluxo->nome_etapa
            ]);
            return true;
        }

        \Log::warning('Usuário não tem acesso ao projeto - visualização negada', [
            'user_id' => $user->id,
            'user_org_id' => $user->organizacao_id,
            'acao_id' => $acao->id,
            'etapa_id' => $etapaFluxo->id
        ]);

        return false;
    }

    /**
     * Verificar se um usuário pode INTERAGIR com uma etapa (mais restritivo)
     */
    private function podeInteragirComEtapa(Acao $acao, EtapaFluxo $etapaFluxo): bool
    {
        $user = Auth::user();
        
        // ===== REGRA UNIVERSAL: APENAS A ETAPA ATUAL PERMITE EDIÇÃO =====
        // Esta regra se aplica a TODOS os usuários, incluindo administradores
        // para garantir a integridade do fluxo de trabalho
        
        $etapaAtualDoFluxo = $this->determinarEtapaAtual($acao, collect());
        
        if (!$etapaAtualDoFluxo || $etapaAtualDoFluxo->id !== $etapaFluxo->id) {
            \Log::info('Interação negada - etapa não é a atual do fluxo (regra universal)', [
                'user_id' => $user->id,
                'user_role' => $user->roles->pluck('name')->toArray(),
                'etapa_solicitada_id' => $etapaFluxo->id,
                'etapa_solicitada_nome' => $etapaFluxo->nome_etapa,
                'etapa_atual_id' => $etapaAtualDoFluxo ? $etapaAtualDoFluxo->id : null,
                'etapa_atual_nome' => $etapaAtualDoFluxo ? $etapaAtualDoFluxo->nome_etapa : 'Nenhuma',
                'motivo' => 'Apenas etapa atual permite edicao - regra universal'
            ]);
            return false;
        }

        // ===== VERIFICAÇÃO DE ORGANIZAÇÃO =====
        // Agora verificar se o usuário pertence às organizações envolvidas
        
        // Admins de sistema podem interagir com a etapa atual de qualquer organização
        if ($user->hasRole(['admin', 'admin_paranacidade'])) {
            \Log::info('Interação permitida - admin em etapa atual', [
                'user_id' => $user->id,
                'etapa_id' => $etapaFluxo->id,
                'etapa_nome' => $etapaFluxo->nome_etapa,
                'motivo' => 'Admin pode interagir com etapa atual'
            ]);
            return true;
        }

        // Para usuários não-admin, verificar se pertence às organizações da etapa
        $userOrgId = $user->organizacao_id;
        $pertenceEtapa = ($userOrgId === $etapaFluxo->organizacao_solicitante_id) || 
                        ($userOrgId === $etapaFluxo->organizacao_executora_id);
        
        if (!$pertenceEtapa) {
            \Log::info('Usuário não pertence às organizações desta etapa - interação negada', [
                'user_id' => $user->id,
                'user_org_id' => $userOrgId,
                'org_solicitante_id' => $etapaFluxo->organizacao_solicitante_id,
                'org_executora_id' => $etapaFluxo->organizacao_executora_id,
                'etapa_id' => $etapaFluxo->id,
                'motivo' => 'Usuario nao pertence as organizacoes desta etapa'
            ]);
            return false;
        }

        // ===== VERIFICAÇÃO FINAL DE ACESSO À AÇÃO =====
        if (!$this->canAccessAcao($acao)) {
            \Log::info('Usuário não tem acesso ao projeto - interação negada', [
                'user_id' => $user->id,
                'acao_id' => $acao->id,
                'etapa_id' => $etapaFluxo->id,
                'motivo' => 'Usuario nao tem acesso ao projeto'
            ]);
            return false;
        }
        
        \Log::info('Interação com etapa atual permitida', [
            'user_id' => $user->id,
            'etapa_id' => $etapaFluxo->id,
            'etapa_ordem' => $etapaFluxo->ordem_execucao,
            'etapa_nome' => $etapaFluxo->nome_etapa,
            'motivo' => 'Usuario autorizado na etapa atual do fluxo'
        ]);

        return true;
    }

    /**
     * Reativar projeto finalizado (apenas admins)
     */
    public function reativarProjetoFinalizado(Request $request, Acao $acao)
    {
        $request->validate([
            'motivo_reativacao' => 'required|string|max:1000'
        ]);

        $user = Auth::user();

        // Verificar se é admin
        if (!$user->hasRole(['admin', 'admin_paranacidade'])) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem reativar projetos finalizados.'
            ], 403);
        }

        // Verificar se o projeto está finalizado
        if (!$acao->isFinalizado()) {
            return response()->json([
                'success' => false,
                'message' => 'O projeto não está finalizado.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Reativar o projeto
            $sucesso = $acao->reabrir($user, $request->motivo_reativacao);
            
            if (!$sucesso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível reativar o projeto.'
                ], 400);
            }

            // Buscar a última etapa executada para reativar
            $ultimaExecucao = ExecucaoEtapa::where('acao_id', $acao->id)
                ->whereHas('status', function($query) {
                    $query->where('codigo', 'FINALIZADO');
                })
                ->with(['etapaFluxo', 'status'])
                ->orderBy('created_at', 'desc')
                ->first();

            if ($ultimaExecucao) {
                // Reativar a última etapa (voltar para status anterior à finalização)
                $statusPendente = Status::where('codigo', 'PENDENTE')->first();
                $statusAnterior = $ultimaExecucao->status;
                
                $ultimaExecucao->update([
                    'status_id' => $statusPendente->id,
                    'data_conclusao' => null,
                    'observacoes' => "REATIVADO PELO ADMIN: {$request->motivo_reativacao}",
                    'updated_by' => $user->id
                ]);

                // Registrar no histórico
                HistoricoEtapa::create([
                    'execucao_etapa_id' => $ultimaExecucao->id,
                    'usuario_id' => $user->id,
                    'status_anterior_id' => $statusAnterior->id,
                    'status_novo_id' => $statusPendente->id,
                    'acao' => 'REATIVACAO_PROJETO',
                    'descricao_acao' => "Projeto reativado pelo administrador - Status alterado de FINALIZADO para PENDENTE",
                    'observacao' => $request->motivo_reativacao,
                    'dados_alterados' => json_encode([
                        'status_anterior' => $statusAnterior->nome,
                        'status_novo' => $statusPendente->nome,
                        'tipo_operacao' => 'reativar_projeto',
                        'admin_responsavel' => $user->name,
                        'data_reativacao' => now()->toDateTimeString()
                    ]),
                    'ip_usuario' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Projeto reativado com sucesso! O projeto pode ser editado novamente.',
                'projeto_reativado' => true,
                'etapa_reativada' => $ultimaExecucao ? $ultimaExecucao->etapaFluxo->nome_etapa : null,
                'admin_responsavel' => $user->name
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Erro ao reativar projeto finalizado', [
                'acao_id' => $acao->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao reativar projeto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalizar projeto completo - independente de etapas pendentes
     * Usado quando etapas são puladas em fluxo condicional
     */
    public function finalizarProjetoCompleto(Request $request, Acao $acao)
    {
        // Verificar se o usuário pode acessar esta ação
        if (!$this->canAccessAcao($acao)) {
            abort(403, 'Acesso negado a esta ação.');
        }

        $user = Auth::user();

        // Verificar se o projeto já está finalizado
        if ($acao->is_finalizado) {
            return response()->json([
                'success' => false,
                'error' => 'O projeto já está finalizado.'
            ], 400);
        }

        // Verificar se está na última etapa do fluxo executado
        if (!$acao->isNaUltimaEtapa()) {
            return response()->json([
                'success' => false,
                'error' => 'Só é possível finalizar o projeto quando estiver na última etapa executada.'
            ], 400);
        }

        // Verificar permissões (usuário deve poder interagir com alguma etapa do projeto)
        $podeInteragir = false;
        $etapasFluxo = EtapaFluxo::where('tipo_fluxo_id', $acao->tipo_fluxo_id)->get();
        foreach ($etapasFluxo as $etapa) {
            if ($this->podeInteragirComEtapa($acao, $etapa)) {
                $podeInteragir = true;
                break;
            }
        }

        if (!$podeInteragir && !$user->hasRole(['admin', 'admin_paranacidade'])) {
            return response()->json([
                'success' => false,
                'error' => 'Você não tem permissão para finalizar este projeto.'
            ], 403);
        }

        $observacao = $request->input('observacao', 'Projeto finalizado manualmente');

        DB::beginTransaction();
        try {
            // 1. Buscar todas as etapas do fluxo
            $todasEtapas = EtapaFluxo::where('tipo_fluxo_id', $acao->tipo_fluxo_id)
                ->orderBy('ordem_execucao')
                ->get();

            // 2. Buscar todas as execuções existentes
            $execucoesExistentes = ExecucaoEtapa::where('acao_id', $acao->id)
                ->with('status')
                ->get()
                ->keyBy('etapa_fluxo_id');

            // 3. Buscar status "Não Aplicável" para etapas puladas
            $statusNaoAplicavel = Status::where('codigo', 'NAO_APLICAVEL')->first();
            if (!$statusNaoAplicavel) {
                // Criar o status se não existir
                $statusNaoAplicavel = Status::create([
                    'nome' => 'Não Aplicável',
                    'codigo' => 'NAO_APLICAVEL',
                    'descricao' => 'Etapa não aplicável no fluxo condicional',
                    'categoria' => 'GERAL',
                    'cor' => '#6c757d',
                    'icone' => 'fas fa-minus-circle',
                    'is_ativo' => true,
                    'ordem' => 99
                ]);
            }

            // 4. Marcar etapas não executadas como "Não Aplicável"
            $etapasPuladas = 0;
            foreach ($todasEtapas as $etapa) {
                $execucao = $execucoesExistentes->get($etapa->id);
                
                if (!$execucao) {
                    // Etapa não foi executada - criar execução como "Não Aplicável"
                    ExecucaoEtapa::create([
                        'acao_id' => $acao->id,
                        'etapa_fluxo_id' => $etapa->id,
                        'status_id' => $statusNaoAplicavel->id,
                        'usuario_responsavel_id' => $user->id,
                        'data_inicio' => now(),
                        'data_conclusao' => now(),
                        'observacoes' => 'Etapa marcada como não aplicável na finalização do projeto',
                        'created_by' => $user->id,
                        'updated_by' => $user->id
                    ]);
                    $etapasPuladas++;
                }
            }

            // 5. Finalizar o projeto
            $acao->update([
                'is_finalizado' => true,
                'data_finalizacao' => now(),
                'usuario_finalizacao_id' => $user->id,
                'observacao_finalizacao' => "{$observacao} | {$etapasPuladas} etapas marcadas como não aplicáveis",
                'status' => 'FINALIZADO',
                'updated_by' => $user->id
            ]);

            // 6. Registrar no histórico da última etapa executada
            $ultimaExecucao = $execucoesExistentes->where('status.codigo', '!=', 'NAO_APLICAVEL')->last();
            if ($ultimaExecucao) {
                HistoricoEtapa::create([
                    'execucao_etapa_id' => $ultimaExecucao->id,
                    'usuario_id' => $user->id,
                    'status_anterior_id' => $ultimaExecucao->status_id,
                    'status_novo_id' => $ultimaExecucao->status_id, // Mantém o mesmo status
                    'acao' => 'FINALIZACAO_COMPLETA',
                    'descricao_acao' => 'Projeto finalizado manualmente com etapas puladas',
                    'observacao' => $observacao,
                    'dados_alterados' => json_encode([
                        'tipo_operacao' => 'finalizacao_completa',
                        'etapas_puladas' => $etapasPuladas,
                        'etapas_total' => $todasEtapas->count(),
                        'usuario_finalizacao' => $user->name,
                        'data_finalizacao' => now()->toDateTimeString()
                    ]),
                    'ip_usuario' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }

            DB::commit();

            \Log::info('Projeto finalizado completamente', [
                'acao_id' => $acao->id,
                'user_id' => $user->id,
                'etapas_total' => $todasEtapas->count(),
                'etapas_puladas' => $etapasPuladas,
                'observacao' => $observacao
            ]);

            return response()->json([
                'success' => true,
                'message' => "Projeto finalizado com sucesso! {$etapasPuladas} etapas foram marcadas como não aplicáveis.",
                'etapas_puladas' => $etapasPuladas,
                'etapas_total' => $todasEtapas->count()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Erro ao finalizar projeto completo', [
                'acao_id' => $acao->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao finalizar projeto: ' . $e->getMessage()
            ], 500);
        }
    }
} 