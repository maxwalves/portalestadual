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

        // === NOVA FUNCIONALIDADE: Calcular acessibilidade de cada etapa ===
        $etapasAcessiveis = collect();
        foreach ($etapasFluxo as $etapaFluxo) {
            $podeAcessar = $this->podeAcessarEtapa($acao, $etapaFluxo);
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
                'pode_acessar' => $podeAcessar,
                'pode_ver_detalhes' => $podeVisualizar,
                'pode_ver_historico' => $podeVerHistorico,
                'motivo_bloqueio' => !$podeAcessar ? $this->getMotivoBloqueioEtapa($acao, $etapaFluxo) : null
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

        // Verificar se há etapas anteriores não concluídas
        if ($etapaFluxo->ordem_execucao > 1) {
            $etapasAnterioresPendentes = EtapaFluxo::where('tipo_fluxo_id', $etapaFluxo->tipo_fluxo_id)
                ->where('ordem_execucao', '<', $etapaFluxo->ordem_execucao)
                ->whereDoesntHave('execucoesEtapa', function($query) use ($acao) {
                    $query->where('acao_id', $acao->id)
                          ->whereHas('status', function($q) {
                              $q->where('codigo', 'APROVADO');
                          });
                })
                ->orderBy('ordem_execucao')
                ->first();

            if ($etapasAnterioresPendentes) {
                return "Complete a etapa anterior: {$etapasAnterioresPendentes->nome_etapa}";
            }
        }

        return 'Etapa não acessível';
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
            // Verificar primeiro se há etapas anteriores pendentes (prioridade máxima)
            $etapasAnterioresPendentes = EtapaFluxo::where('tipo_fluxo_id', $etapaFluxo->tipo_fluxo_id)
                ->where('ordem_execucao', '<', $etapaFluxo->ordem_execucao)
                ->whereDoesntHave('execucoesEtapa', function($query) use ($acao) {
                    $query->where('acao_id', $acao->id)
                          ->whereHas('status', function($q) {
                              $q->where('codigo', 'APROVADO');
                          });
                })
                ->orderBy('ordem_execucao')
                ->first();

            if ($etapasAnterioresPendentes) {
                $statusInteracao['motivo_bloqueio'] = "Aguardando conclusão da etapa anterior: {$etapasAnterioresPendentes->nome_etapa}";
                $statusInteracao['organizacao_responsavel_atual'] = "Etapa anterior pendente";
            } else {
                // Verificar se o usuário não pertence às organizações desta etapa específica
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
    public function historicoEtapa(ExecucaoEtapa $execucao)
    {
        // Verificar se o usuário pode acessar esta ação (projeto)
        // Permite que todos os envolvidos no projeto vejam o histórico
        if (!$this->canAccessAcao($execucao->acao)) {
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

            // === PERMISSÕES PARA ETAPA JÁ INICIADA ===
            if ($execucao) {
                // Pode enviar documentos se for da organização executora E a etapa estiver ativa
                if ($userOrgId === $etapaAtual->organizacao_executora_id) {
                    $permissoes['pode_enviar_documento'] = $this->podeEnviarDocumento($execucao);
                }

                // Pode aprovar documentos e concluir etapa se for da organização solicitante
                if ($userOrgId === $etapaAtual->organizacao_solicitante_id) {
                    $permissoes['pode_aprovar_documento'] = true;
                    $permissoes['pode_concluir_etapa'] = $this->podeConcluirEtapa($execucao);
                    
                    // Nova permissão: pode escolher próxima etapa quando todos documentos aprovados
                    if ($this->todosDocumentosAprovados($execucao) && 
                        in_array($execucao->status->codigo, ['PENDENTE', 'EM_ANALISE'])) {
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
            ->where('is_obrigatorio', true)
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
            
            // Verificar se o usuário pode escolher transições (deve ser da organização solicitante)
            if (!$user->hasRole(['admin', 'admin_paranacidade']) && 
                $user->organizacao_id !== $execucao->etapaFluxo->organizacao_solicitante_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas a organização solicitante pode escolher o destino da etapa'
                ], 403);
            }

            // Verificar se todos os documentos estão aprovados
            if (!$this->todosDocumentosAprovados($execucao)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nem todos os documentos obrigatórios foram aprovados'
                ], 400);
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

                            if (!$execucaoExistente) {
                                $descricao = $isStatusAtual 
                                    ? ($transicao->descricao ?? "Manter status {$status->nome} e prosseguir para {$transicao->etapaDestino->nome_etapa}")
                                    : ($transicao->descricao ?? "Alterar status para {$status->nome} e prosseguir para {$transicao->etapaDestino->nome_etapa}");
                                    
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
                                    'tipo_operacao' => 'iniciar_etapa', // Inicia nova etapa
                                    'is_status_atual' => $isStatusAtual
                                ];
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
                            'etapa_destino_nome' => 'Somente alteração de status',
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
                            'requer_justificativa' => false
                        ];
                    }
                }
            }

            if (empty($opcoesDisponiveis)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Todas as etapas destino já foram iniciadas ou não há próximas etapas configuradas'
                ], 404);
            }

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
        $request->validate([
            'transicao_id' => 'nullable|exists:transicao_etapas,id',
            'status_id' => 'required|exists:status,id',
            'etapa_destino_id' => 'nullable|exists:etapa_fluxo,id',
            'tipo_operacao' => 'required|in:alterar_status,iniciar_etapa,manter_status',
            'observacoes' => 'nullable|string|max:1000'
        ]);

        try {
            $user = Auth::user();
            
            // Verificar se o usuário pode executar transições
            if (!$user->hasRole(['admin', 'admin_paranacidade']) && 
                $user->organizacao_id !== $execucao->etapaFluxo->organizacao_solicitante_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas a organização solicitante pode escolher o destino da etapa'
                ], 403);
            }

            // Verificar se todos os documentos estão aprovados
            if (!$this->todosDocumentosAprovados($execucao)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nem todos os documentos obrigatórios foram aprovados'
                ], 400);
            }

            // Buscar o novo status
            $novoStatus = Status::findOrFail($request->status_id);
            
            // Buscar etapa destino apenas se for para iniciar nova etapa
            $etapaDestino = null;
            if ($request->tipo_operacao === 'iniciar_etapa' && $request->etapa_destino_id) {
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

                if ($execucaoExistente) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A etapa destino já foi iniciada'
                    ], 400);
                }
            }

            DB::beginTransaction();

            // 1. Atualizar status da etapa atual
            $statusAnterior = $execucao->status;
            $datasConclusao = [];
            
            if ($request->tipo_operacao === 'iniciar_etapa') {
                // Se for iniciar nova etapa, marcar como concluída
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

            // 2. Criar nova execução apenas se for para iniciar nova etapa
            if ($request->tipo_operacao === 'iniciar_etapa' && $etapaDestino) {
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

            // 3. Registrar no histórico da etapa atual
            $acaoHistorico = $request->tipo_operacao === 'iniciar_etapa' ? 'CONCLUSAO_ETAPA' : 
                           ($request->tipo_operacao === 'manter_status' ? 'REPROCESSAMENTO' : 'ALTERACAO_STATUS');
            
            $descricaoAcao = match($request->tipo_operacao) {
                'iniciar_etapa' => "Etapa concluída com status '{$novoStatus->nome}'" . ($etapaDestino ? " e direcionada para: {$etapaDestino->nome_etapa}" : ""),
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

            DB::commit();

            $responseData = [
                'success' => true,
                'tipo_operacao' => $request->tipo_operacao
            ];

            if ($request->tipo_operacao === 'iniciar_etapa' && $novaExecucao) {
                $responseData['message'] = "Etapa concluída com sucesso! Próxima etapa '{$etapaDestino->nome_etapa}' foi iniciada.";
                $responseData['proxima_etapa'] = [
                    'id' => $novaExecucao->id,
                    'nome' => $etapaDestino->nome_etapa,
                    'organizacao_executora' => $etapaDestino->organizacaoExecutora->nome
                ];
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
        
        // Admins do sistema e Paranacidade sempre podem interagir
        if ($user->hasRole(['admin', 'admin_paranacidade'])) {
            return true;
        }

        // Primeiro verificar se pode visualizar
        if (!$this->podeVisualizarEtapa($acao, $etapaFluxo)) {
            return false;
        }

        // ===== VALIDAÇÃO ESPECÍFICA DE ORGANIZAÇÃO =====
        // Verificar se o usuário pertence às organizações envolvidas NESTA etapa específica
        $userOrgId = $user->organizacao_id;
        $pertenceEtapa = ($userOrgId === $etapaFluxo->organizacao_solicitante_id) || 
                        ($userOrgId === $etapaFluxo->organizacao_executora_id);
        
        if (!$pertenceEtapa) {
            \Log::info('Usuário não pertence às organizações desta etapa específica - interação negada', [
                'user_id' => $user->id,
                'user_org_id' => $userOrgId,
                'org_solicitante_id' => $etapaFluxo->organizacao_solicitante_id,
                'org_executora_id' => $etapaFluxo->organizacao_executora_id,
                'etapa_id' => $etapaFluxo->id,
                'motivo' => 'Usuario nao pertence as organizacoes desta etapa'
            ]);
            return false;
        }

        // Se for a primeira etapa (ordem_execucao = 1), sempre pode interagir se pertence à organização
        if ($etapaFluxo->ordem_execucao <= 1) {
            return true;
        }

        // ===== VALIDAÇÃO DE SEQUÊNCIA =====
        // Verificar se todas as etapas anteriores foram concluídas
        $etapasAnteriores = EtapaFluxo::where('tipo_fluxo_id', $etapaFluxo->tipo_fluxo_id)
            ->where('ordem_execucao', '<', $etapaFluxo->ordem_execucao)
            ->orderBy('ordem_execucao')
            ->get();

        foreach ($etapasAnteriores as $etapaAnterior) {
            $execucaoAnterior = ExecucaoEtapa::where('acao_id', $acao->id)
                ->where('etapa_fluxo_id', $etapaAnterior->id)
                ->first();

            // Se não há execução OU a execução não está aprovada, não pode interagir
            if (!$execucaoAnterior || $execucaoAnterior->status->codigo !== 'APROVADO') {
                \Log::info('Etapa anterior não concluída - interação negada (mas visualização permitida)', [
                    'etapa_atual_id' => $etapaFluxo->id,
                    'etapa_atual_ordem' => $etapaFluxo->ordem_execucao,
                    'etapa_anterior_id' => $etapaAnterior->id,
                    'etapa_anterior_ordem' => $etapaAnterior->ordem_execucao,
                    'execucao_anterior_status' => $execucaoAnterior ? $execucaoAnterior->status->codigo : 'NAO_INICIADA',
                    'user_id' => $user->id,
                    'motivo' => 'Etapa anterior nao aprovada'
                ]);
                return false;
            }
        }

        \Log::info('Interação com etapa permitida - sequência respeitada', [
            'user_id' => $user->id,
            'etapa_id' => $etapaFluxo->id,
            'etapa_ordem' => $etapaFluxo->ordem_execucao,
            'etapas_anteriores_concluidas' => $etapasAnteriores->count()
        ]);

        return true;
    }
} 