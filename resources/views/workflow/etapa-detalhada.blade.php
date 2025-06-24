@extends('adminlte::page')

@section('title', 'Etapa: ' . $etapaFluxo->nome_etapa)

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-tasks text-primary"></i>
                    {{ $etapaFluxo->nome_etapa }}
                </h1>
                <small class="text-muted">{{ $acao->nome }}</small>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('acoes.index') }}">Ações</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('workflow.acao', $acao) }}">Workflow</a></li>
                    <li class="breadcrumb-item active">{{ $etapaFluxo->nome_etapa }}</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        
        <!-- Aviso de Status de Interação -->
        @if($statusInteracao['pode_visualizar'] && !$statusInteracao['pode_interagir'])
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-info-circle"></i> Modo Visualização</h5>
                        
                        <p class="mb-1">
                            Você pode visualizar esta etapa, mas não pode interagir com ela no momento.
                        </p>
                        
                        @if($statusInteracao['motivo_bloqueio'])
                            <p class="mb-1">
                                <strong>Motivo:</strong> {{ $statusInteracao['motivo_bloqueio'] }}
                            </p>
                        @endif
                        
                        @if($statusInteracao['organizacao_responsavel_atual'])
                            <p class="mb-0">
                                <strong>Organização responsável atual:</strong> 
                                <span class="badge badge-primary">{{ $statusInteracao['organizacao_responsavel_atual'] }}</span>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Informações da Etapa -->
        <div class="row">
            <div class="col-md-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="{{ $etapaFluxo->modulo->icone ?? 'fas fa-tasks' }}"></i>
                            Informações da Etapa
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-building mr-1"></i> Solicitante:</strong>
                                <span class="badge badge-info">{{ $etapaFluxo->organizacaoSolicitante->nome }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-cogs mr-1"></i> Executora:</strong>
                                <span class="badge badge-success">{{ $etapaFluxo->organizacaoExecutora->nome }}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-clock mr-1"></i> Prazo:</strong>
                                {{ $etapaFluxo->prazo_dias }} {{ $etapaFluxo->tipo_prazo === 'UTEIS' ? 'dias úteis' : 'dias corridos' }}
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-layer-group mr-1"></i> Módulo:</strong>
                                <span class="badge badge-secondary">{{ $etapaFluxo->modulo->nome }}</span>
                            </div>
                        </div>
                        @if($etapaFluxo->descricao_customizada)
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <strong><i class="fas fa-info-circle mr-1"></i> Descrição:</strong>
                                    <p class="text-muted mt-2">{{ $etapaFluxo->descricao_customizada }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Status da Execução -->
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            Status da Execução
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        @if($execucao)
                            <div class="icon-circle bg-{{ $execucao->status_cor }} text-white mx-auto mb-3">
                                <i class="fas fa-{{ $execucao->status->codigo === 'PENDENTE' ? 'clock' : ($execucao->status->codigo === 'APROVADO' ? 'check' : 'exclamation') }}"></i>
                            </div>
                            <h5>{{ $execucao->status->nome }}</h5>
                            <small class="text-muted">
                                Iniciada em: {{ $execucao->data_inicio->format('d/m/Y H:i') }}
                            </small>
                            @if($execucao->data_prazo)
                                <br>
                                <small class="text-muted">
                                    Prazo: {{ $execucao->data_prazo->format('d/m/Y H:i') }}
                                </small>
                            @endif
                        @else
                            <div class="icon-circle bg-secondary text-white mx-auto mb-3">
                                <i class="fas fa-pause"></i>
                            </div>
                            <h5>Não Iniciada</h5>
                            <small class="text-muted">Esta etapa ainda não foi iniciada</small>
                            
                            @if($permissoes['pode_iniciar_etapa'])
                                <br><br>
                                <button class="btn btn-primary btn-sm" onclick="iniciarEtapa()">
                                    <i class="fas fa-play"></i> Iniciar Etapa
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Documentos/Exigências -->
        @if($templatesDocumento->isNotEmpty())
            <div class="row">
                <div class="col-12">
                    <div class="card card-elegante-principal border-0 shadow">
                        <div class="card-header-principal position-relative">
                            <div class="header-background"></div>
                            <div class="position-relative z-index-1">
                                <!-- Layout responsivo -->
                                <div class="row align-items-center">
                                    <!-- Título e informações - col completa em mobile -->
                                    <div class="col-12 col-lg-8">
                                        <div class="header-title">
                                            <div class="d-flex align-items-center flex-wrap">
                                                <h3 class="card-title text-white mb-0 mr-3 font-weight-bold">
                                                    <i class="fas fa-folder-open mr-2"></i>
                                                    Documentos Exigidos
                                                </h3>
                                                <span class="badge badge-light badge-pill mb-0">
                                                    <i class="fas fa-file-alt mr-1"></i>{{ $templatesDocumento->count() }} documentos
                                                </span>
                                            </div>
                                            <div class="mt-2 d-none d-lg-block">
                                                <small class="text-white-50">Gerencie os documentos da etapa</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Botões de visualização - col completa em mobile -->
                                    <div class="col-12 col-lg-4 mt-3 mt-lg-0">
                                        <div class="card-tools d-flex justify-content-center justify-content-lg-end">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-light" onclick="toggleViewMode('table')" id="btn-view-table">
                                                    <i class="fas fa-list mr-1 d-none d-sm-inline"></i>
                                                    <span class="d-none d-sm-inline">Lista</span>
                                                    <i class="fas fa-list d-sm-none"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-light active" onclick="toggleViewMode('grid')" id="btn-view-grid">
                                                    <i class="fas fa-th mr-1 d-none d-sm-inline"></i>
                                                    <span class="d-none d-sm-inline">Grade</span>
                                                    <i class="fas fa-th d-sm-none"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            
                            <!-- ===== VISÃO EM TABELA ===== -->
                            <div id="documentos-table-view" style="display: none;">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm mb-0 table-elegante">
                                        <thead class="thead-elegante">
                                            <tr>
                                                <th style="width: 30%" class="border-0">
                                                    <i class="fas fa-file-alt text-primary mr-2"></i>Documento
                                                </th>
                                                <th style="width: 15%" class="border-0">
                                                    <i class="fas fa-flag text-success mr-2"></i>Status
                                                </th>
                                                <th style="width: 25%" class="border-0">
                                                    <i class="fas fa-info-circle text-info mr-2"></i>Informações
                                                </th>
                                                <th style="width: 30%" class="border-0">
                                                    <i class="fas fa-cogs text-warning mr-2"></i>Ações
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($templatesDocumento as $template)
                                                @php
                                                    $ultimoDocumento = $documentosEnviados->get($template->tipo_documento_id);
                                                    $statusClass = $ultimoDocumento ? 
                                                        ($ultimoDocumento->status_documento === 'APROVADO' ? 'success' : 
                                                         ($ultimoDocumento->status_documento === 'REPROVADO' ? 'danger' : 'warning')) : 'secondary';
                                                @endphp
                                                <tr class="documento-row">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-2">
                                                                <i class="fas fa-file-{{ $ultimoDocumento ? 'check' : 'plus' }} text-{{ $statusClass }} fa-lg"></i>
                                                            </div>
                                                            <div>
                                                                <strong>{{ $template->nome }}</strong>
                                                                @if($template->is_obrigatorio)
                                                                    <span class="badge badge-danger badge-sm ml-1">Obrigatório</span>
                                                                @else
                                                                    <span class="badge badge-info badge-sm ml-1">Opcional</span>
                                                                @endif
                                                                @if($ultimoDocumento)
                                                                    <br><small class="text-muted">{{ $ultimoDocumento->nome_arquivo }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($ultimoDocumento)
                                                            <span class="badge badge-{{ $statusClass }}">
                                                                {{ $ultimoDocumento->status_documento }}
                                                            </span>
                                                        @else
                                                            <span class="badge badge-light">Não enviado</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($ultimoDocumento)
                                                            <small class="text-muted d-block">
                                                                <i class="fas fa-upload"></i> {{ $ultimoDocumento->data_upload->format('d/m/Y H:i') }}
                                                            </small>
                                                            <small class="text-muted d-block">
                                                                <i class="fas fa-user"></i> {{ $ultimoDocumento->usuarioUpload->name }}
                                                            </small>
                                                            @if($ultimoDocumento->status_documento === 'APROVADO' && $ultimoDocumento->data_aprovacao)
                                                                <small class="text-success d-block">
                                                                    <i class="fas fa-check"></i> Aprovado {{ $ultimoDocumento->data_aprovacao->format('d/m/Y H:i') }}
                                                                </small>
                                                            @endif
                                                        @else
                                                            <small class="text-muted">Aguardando envio</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            @if($ultimoDocumento)
                                                                <a href="{{ route('documentos.download', $ultimoDocumento) }}" 
                                                                   class="btn btn-outline-primary btn-sm" target="_blank" title="Download">
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                                
                                                                @if($permissoes['pode_aprovar_documento'] && in_array($ultimoDocumento->status_documento, ['PENDENTE', 'EM_ANALISE']))
                                                                    <button class="btn btn-outline-success btn-sm" 
                                                                            onclick="aprovarDocumento({{ $ultimoDocumento->id }}, '{{ $template->nome }}')" title="Aprovar">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                    <button class="btn btn-outline-danger btn-sm" 
                                                                            onclick="reprovarDocumento({{ $ultimoDocumento->id }}, '{{ $template->nome }}')" title="Reprovar">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                @endif
                                                                
                                                                @if($ultimoDocumento->observacoes || $ultimoDocumento->motivo_reprovacao)
                                                                    <button class="btn btn-outline-info btn-sm" 
                                                                            onclick="verObservacoes({{ $ultimoDocumento->id }})" title="Ver Observações">
                                                                        <i class="fas fa-comment"></i>
                                                                    </button>
                                                                @endif
                                                            @endif
                                                            
                                                            @if($permissoes['pode_enviar_documento'])
                                                                <button class="btn btn-primary btn-sm" 
                                                                        onclick="enviarDocumento({{ $template->tipo_documento_id }})" title="Enviar">
                                                                    <i class="fas fa-upload"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- ===== VISÃO EM GRADE ===== -->
                            <div id="documentos-grid-view" class="p-4">
                                <div class="row">
                                    @foreach($templatesDocumento as $template)
                                        @php
                                            $ultimoDocumento = $documentosEnviados->get($template->tipo_documento_id);
                                            $statusClass = $ultimoDocumento ? 
                                                ($ultimoDocumento->status_documento === 'APROVADO' ? 'success' : 
                                                 ($ultimoDocumento->status_documento === 'REPROVADO' ? 'danger' : 'warning')) : 'light';
                                            $statusIcon = $ultimoDocumento ? 
                                                ($ultimoDocumento->status_documento === 'APROVADO' ? 'check-circle' : 
                                                 ($ultimoDocumento->status_documento === 'REPROVADO' ? 'times-circle' : 'clock')) : 'plus-circle';
                                        @endphp
                                        <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                                            <div class="card documento-card-elegante h-100 shadow-sm border-0">
                                                <!-- Header com gradiente -->
                                                <div class="card-header-elegante text-white position-relative overflow-hidden 
                                                            bg-{{ $statusClass === 'light' ? 'secondary' : $statusClass }}">
                                                    <div class="header-pattern"></div>
                                                    <div class="position-relative z-index-1">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="flex-grow-1">
                                                                <h6 class="card-title mb-1 font-weight-600 text-truncate" title="{{ $template->nome }}">
                                                                    {{ $template->nome }}
                                                                </h6>
                                                            </div>
                                                            <div class="status-icon">
                                                                <i class="fas fa-{{ $statusIcon }} fa-lg opacity-75"></i>
                                                            </div>
                                                        </div>
                                                        <div class="mt-1">
                                                            @if($template->is_obrigatorio)
                                                                <span class="badge badge-light badge-pill badge-sm">
                                                                    <i class="fas fa-exclamation fa-xs mr-1"></i>Obrigatório
                                                                </span>
                                                            @else
                                                                <span class="badge badge-outline-light badge-pill badge-sm">
                                                                    <i class="fas fa-info fa-xs mr-1"></i>Opcional
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Corpo do card -->
                                                <div class="card-body p-3">
                                                    @if($ultimoDocumento)
                                                        <div class="status-section mb-3">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <span class="text-muted small">Status:</span>
                                                                <span class="badge badge-{{ $ultimoDocumento->status_documento === 'APROVADO' ? 'success' : ($ultimoDocumento->status_documento === 'REPROVADO' ? 'danger' : 'warning') }} badge-pill">
                                                                    {{ $ultimoDocumento->status_documento }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="arquivo-info">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="fas fa-paperclip text-muted mr-2"></i>
                                                                <span class="text-truncate small" title="{{ $ultimoDocumento->nome_arquivo }}">
                                                                    {{ $ultimoDocumento->nome_arquivo }}
                                                                </span>
                                                            </div>
                                                            <div class="d-flex align-items-center text-muted">
                                                                <i class="fas fa-clock mr-2"></i>
                                                                <span class="small">{{ $ultimoDocumento->data_upload->format('d/m/Y H:i') }}</span>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="documento-vazio text-center py-3">
                                                            <i class="fas fa-file-upload text-muted mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                                            <p class="text-muted small mb-0">Nenhum documento enviado</p>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <!-- Footer com ações -->
                                                <div class="card-footer bg-light border-0 p-2">
                                                    <div class="btn-toolbar justify-content-center" role="toolbar">
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            @if($ultimoDocumento)
                                                                <a href="{{ route('documentos.download', $ultimoDocumento) }}" 
                                                                   class="btn btn-outline-primary btn-elegante" target="_blank" 
                                                                   data-toggle="tooltip" title="Download">
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                                
                                                                @if($permissoes['pode_aprovar_documento'] && in_array($ultimoDocumento->status_documento, ['PENDENTE', 'EM_ANALISE']))
                                                                    <button class="btn btn-outline-success btn-elegante" 
                                                                            onclick="aprovarDocumento({{ $ultimoDocumento->id }}, '{{ $template->nome }}')"
                                                                            data-toggle="tooltip" title="Aprovar">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                    <button class="btn btn-outline-danger btn-elegante" 
                                                                            onclick="reprovarDocumento({{ $ultimoDocumento->id }}, '{{ $template->nome }}')"
                                                                            data-toggle="tooltip" title="Reprovar">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                @endif
                                                                
                                                                @if($ultimoDocumento->observacoes || $ultimoDocumento->motivo_reprovacao)
                                                                    <button class="btn btn-outline-info btn-elegante" 
                                                                            onclick="verObservacoes({{ $ultimoDocumento->id }})"
                                                                            data-toggle="tooltip" title="Ver Observações">
                                                                        <i class="fas fa-comment-alt"></i>
                                                                    </button>
                                                                @endif
                                                            @endif
                                                            
                                                            @if($permissoes['pode_enviar_documento'])
                                                                <button class="btn btn-primary btn-elegante" 
                                                                        onclick="enviarDocumento({{ $template->tipo_documento_id }})"
                                                                        data-toggle="tooltip" title="{{ $ultimoDocumento ? 'Enviar Nova Versão' : 'Enviar Documento' }}">
                                                                    <i class="fas fa-{{ $ultimoDocumento ? 'sync-alt' : 'upload' }}"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Botões de Ação -->
        @if($execucao)
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary card-outline">
                        <div class="card-body text-center">
                            @if($permissoes['pode_concluir_etapa'] && $execucao->status->codigo !== 'APROVADO')
                                @php
                                    // Verificar se todos os documentos obrigatórios estão aprovados
                                    $podeAprovar = true;
                                    $documentosPendentes = [];
                                    
                                    if($etapaFluxo->grupoExigencia) {
                                        $templatesObrigatorios = $etapaFluxo->grupoExigencia->templatesDocumento()
                                            ->where('is_obrigatorio', true)
                                            ->get();
                                            
                                        foreach($templatesObrigatorios as $template) {
                                            $documentoAprovado = $execucao ? $execucao->documentos()
                                                ->where('tipo_documento_id', $template->tipo_documento_id)
                                                ->where('status_documento', 'APROVADO')
                                                ->exists() : false;
                                                
                                            if(!$documentoAprovado) {
                                                $podeAprovar = false;
                                                $documentosPendentes[] = $template->nome;
                                            }
                                        }
                                    }
                                @endphp
                                
                                @if($podeAprovar)
                                    <!-- Botão para escolher próxima etapa (novo fluxo condicional) -->
                                    @if($permissoes['pode_escolher_proxima_etapa'] ?? false)
                                        <button class="btn btn-primary btn-lg mr-2" onclick="escolherProximaEtapa()">
                                            <i class="fas fa-route"></i> Escolher Próxima Etapa
                                        </button>
                                    @else
                                        <button class="btn btn-success btn-lg mr-2" onclick="alterarStatusEtapa()">
                                            <i class="fas fa-check-circle"></i> Concluir Etapa
                                        </button>
                                    @endif
                                @else
                                    <button class="btn btn-success btn-lg mr-2" disabled 
                                            title="Aguardando aprovação de documentos obrigatórios: {{ implode(', ', $documentosPendentes) }}"
                                            data-toggle="tooltip">
                                        <i class="fas fa-clock"></i> Aguardando Documentos
                                    </button>
                                    <div class="mt-2">
                                        <small class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Para concluir esta etapa, todos os documentos obrigatórios devem estar aprovados.
                                        </small>
                                        @if(!empty($documentosPendentes))
                                            <br>
                                            <small class="text-muted">
                                                Pendentes: {{ implode(', ', $documentosPendentes) }}
                                            </small>
                                        @endif
                                    </div>
                                @endif
                            @elseif($execucao->status->codigo === 'APROVADO')
                                <div class="alert alert-success mb-3">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Etapa Concluída com Sucesso!</strong><br>
                                    Esta etapa foi aprovada em {{ $execucao->data_conclusao ? $execucao->data_conclusao->format('d/m/Y H:i') : $execucao->updated_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                            
                            @if($podeVerHistorico)
                                <button class="btn btn-info" onclick="verHistorico()">
                                    <i class="fas fa-history"></i> Ver Histórico
                                </button>
                            @endif
                            
                            <a href="{{ route('workflow.acao', $acao) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar ao Workflow
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Modais -->
    @include('workflow.modals.upload-documento')
    @include('workflow.modals.aprovar-documento')
    @include('workflow.modals.reprovar-documento')
    @include('workflow.modals.alterar-status-etapa')
    @include('workflow.modals.concluir-etapa')
    @include('workflow.modals.historico-etapa')
    @include('workflow.modals.observacoes-documento')
    @include('workflow.modals.escolher-proxima-etapa')

@stop

@section('css')
    <style>
        /* ===== LAYOUT ORIGINAL ===== */
        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .card-outline {
            border-top: 3px solid;
        }
        
        .badge-sm {
            font-size: 0.7em;
        }
        
        /* ===== OTIMIZAÇÃO DOCUMENTOS ===== */
        
        /* Tabela de documentos otimizada */
        .documento-row {
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .documento-row:hover {
            background: linear-gradient(90deg, #f8f9fa 0%, #ffffff 100%);
            transform: translateX(3px);
            border-left-color: #007bff;
            box-shadow: 0 2px 8px rgba(0,123,255,0.1);
        }
        
        /* ===== CARDS ELEGANTES ===== */
        
        .documento-card-elegante {
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            background: #ffffff;
        }
        
        .documento-card-elegante:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15), 0 8px 20px rgba(0,0,0,0.1);
        }
        
        /* Header com gradiente e padrão */
        .card-header-elegante {
            padding: 0.9rem;
            position: relative;
            background: linear-gradient(135deg, var(--header-color-1), var(--header-color-2));
            border: none;
            min-height: 85px;
        }
        
        .header-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 2px, transparent 2px),
                radial-gradient(circle at 80% 50%, rgba(255,255,255,0.1) 2px, transparent 2px);
            background-size: 30px 30px;
            opacity: 0.7;
        }
        
        .z-index-1 { z-index: 1; }
        
        .font-weight-600 { font-weight: 600; }
        
        /* Espaçamento específico para badges nos cards */
        .card-header-elegante .badge {
            margin-top: 0.25rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Estados dos headers com gradientes */
        .bg-success {
            --header-color-1: #28a745;
            --header-color-2: #20c997;
        }
        
        .bg-danger {
            --header-color-1: #dc3545;
            --header-color-2: #fd7e14;
        }
        
        .bg-warning {
            --header-color-1: #ffc107;
            --header-color-2: #fd7e14;
        }
        
        .bg-secondary {
            --header-color-1: #6c757d;
            --header-color-2: #495057;
        }
        
        /* Badges elegantes */
        .badge-outline-light {
            color: rgba(255,255,255,0.9);
            border: 1px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
        }
        
        .badge-pill {
            border-radius: 50px;
            font-weight: 500;
            padding: 0.35rem 0.7rem;
            font-size: 0.75rem;
            margin-top: 0.2rem;
            display: inline-block;
        }
        
        /* Seções do corpo */
        .status-section {
            padding: 0.5rem;
            background: rgba(0,123,255,0.05);
            border-radius: 8px;
            border-left: 3px solid var(--bs-primary);
        }
        
        .arquivo-info {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .documento-vazio {
            background: linear-gradient(45deg, #f8f9fa, #ffffff);
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            margin: 0.5rem 0;
        }
        
        /* Botões elegantes */
        .btn-elegante {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 2px;
            position: relative;
            overflow: hidden;
        }
        
        .btn-elegante::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.3s, height 0.3s;
        }
        
        .btn-elegante:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-elegante:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn-outline-primary.btn-elegante:hover {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border-color: #0056b3;
        }
        
        .btn-outline-success.btn-elegante:hover {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            border-color: #1e7e34;
        }
        
        .btn-outline-danger.btn-elegante:hover {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border-color: #c82333;
        }
        
        .btn-outline-info.btn-elegante:hover {
            background: linear-gradient(135deg, #17a2b8, #138496);
            border-color: #138496;
        }
        
        .btn-primary.btn-elegante {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
        }
        
        .btn-primary.btn-elegante:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: translateY(-2px);
        }
        
        /* ===== TABELA ELEGANTE ===== */
        
        .table-elegante {
            border-spacing: 0;
            border-collapse: separate;
        }
        
        .thead-elegante {
            background: linear-gradient(135deg, #00013d 0%, #6f90af 100%);
            color: white;
        }
        
        .thead-elegante th {
            font-weight: 600;
            font-size: 0.875rem;
            padding: 1rem 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            position: relative;
        }
        
        .thead-elegante th:first-child {
            border-radius: 10px 0 0 10px;
        }
        
        .thead-elegante th:last-child {
            border-radius: 0 10px 10px 0;
        }
        
        .table-elegante tbody tr {
            background: #ffffff;
            border: none;
        }
        
        .table-elegante tbody tr:nth-child(even) {
            background: rgba(0,123,255,0.02);
        }
        
        .table-elegante td {
            padding: 1rem 0.75rem;
            border: none;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            vertical-align: middle;
        }
        
        /* Botões de alternar visualização */
        .card-tools .btn-group .btn {
            border-radius: 25px;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid #dee2e6;
            background: #ffffff;
            color: #6c757d;
            margin: 0 2px;
        }
        
        .card-tools .btn-group .btn:first-child {
            border-radius: 25px;
        }
        
        .card-tools .btn-group .btn:last-child {
            border-radius: 25px;
        }
        
        .card-tools .btn-group .btn.active {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border-color: #007bff;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
        }
        
        .card-tools .btn-group .btn:hover:not(.active) {
            border-color: #007bff;
            color: #007bff;
            background: rgba(0,123,255,0.05);
        }
        
        /* Responsividade melhorada */
        @media (max-width: 768px) {
            .documento-card .card-header h6 {
                font-size: 0.8rem;
            }
            
            .documento-card .card-body {
                padding: 8px;
                font-size: 0.75rem;
            }
            
            .btn-group-sm .btn {
                padding: 0.2rem 0.4rem;
                font-size: 0.7rem;
            }
        }
        
        /* Estados dos documentos com cores sutis */
        .card-success {
            border-left: 4px solid #28a745;
        }
        
        .card-danger {
            border-left: 4px solid #dc3545;
        }
        
        .card-warning {
            border-left: 4px solid #ffc107;
        }
        
        .card-light {
            border-left: 4px solid #6c757d;
        }
        
        /* Badge personalizado para documentos */
        .badge-sm {
            font-size: 0.65rem;
            padding: 0.2rem 0.4rem;
        }
        
        /* Texto truncado em nomes de arquivos */
        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* ===== HEADER PRINCIPAL ELEGANTE ===== */
        
        .card-elegante-principal {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .card-header-principal {
            padding: 1rem 1.5rem;
            background: #007bff;
            border: none;
            min-height: 80px;
        }
        
        .header-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(255,255,255,0.08) 2px, transparent 2px),
                radial-gradient(circle at 75% 75%, rgba(255,255,255,0.08) 2px, transparent 2px);
            background-size: 40px 40px, 40px 40px;
            animation: headerShine 10s ease-in-out infinite;
        }
        
        @keyframes headerShine {
            0%, 100% { opacity: 0.7; }
            50% { opacity: 1; }
        }
        
        .header-title h3 {
            font-size: 1.2rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 0.25rem;
        }
        
        .text-white-50 {
            color: rgba(255,255,255,0.7) !important;
        }
        
        /* Botões do header principal */
        .card-header-principal .btn-group .btn {
            border: 1px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.9);
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .card-header-principal .btn-group .btn.active {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.5);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .card-header-principal .btn-group .btn:hover:not(.active) {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.4);
            transform: translateY(-1px);
        }
        
        /* Animações suaves */
        .btn, .card, .badge {
            transition: all 0.2s ease;
        }
        
        /* Contador de documentos */
        .badge-secondary {
            background-color: #6c757d;
            font-weight: 500;
        }
        
        /* ===== MODAL ESCOLHER PRÓXIMA ETAPA ===== */
        
        .opcao-transicao {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #ffffff;
        }
        
        .opcao-transicao:hover {
            border-color: #007bff;
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.15);
        }
        
        .opcao-transicao.selecionada {
            border-color: #007bff;
            background: linear-gradient(135deg, #e3f2fd, #ffffff);
            box-shadow: 0 4px 12px rgba(0,123,255,0.2);
        }
        
        .etapa-info {
            flex-grow: 1;
        }
        
        .etapa-titulo {
            font-size: 1.1rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.25rem;
        }
        
        .etapa-descricao {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .etapa-detalhes {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .detalhe-item {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .detalhe-item i {
            margin-right: 0.3rem;
        }
        
        .radio-icon {
            width: 24px;
            height: 24px;
            border: 2px solid #dee2e6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            transition: all 0.3s ease;
        }
        
        .opcao-transicao.selecionada .radio-icon {
            border-color: #007bff;
            background: #007bff;
            color: white;
        }
        
        /* Estilos de prioridade removidos - agora são controlados pelo modal include */
        
        /* ===== RESPONSIVIDADE MELHORADA PARA HEADER ===== */
        
        /* Mobile pequeno (até 575px) */
        @media (max-width: 575.98px) {
            .card-header-principal {
                padding: 0.75rem !important;
                min-height: auto;
            }
            
            .header-title .d-flex {
                flex-direction: column !important;
                align-items: flex-start !important;
            }
            
            .header-title .card-title {
                margin-right: 0 !important;
                margin-bottom: 0.5rem !important;
                font-size: 1rem !important;
            }
            
            .header-title .badge {
                margin-top: 0;
                font-size: 0.7rem;
            }
            
            .btn-group-sm .btn {
                padding: 0.375rem 0.5rem;
                font-size: 0.75rem;
            }
            
            .col-12.mt-3 {
                margin-top: 1rem !important;
            }
        }
        
        /* Mobile médio (576px a 767px) */
        @media (min-width: 576px) and (max-width: 767.98px) {
            .card-header-principal {
                padding: 1rem;
            }
            
            .header-title .card-title {
                font-size: 1.1rem;
            }
            
            .btn-group-sm .btn {
                padding: 0.375rem 0.75rem;
                font-size: 0.8rem;
            }
        }
        
        /* Ajustes para layout em linha no desktop */
        @media (min-width: 992px) {
            .header-title .d-flex {
                align-items: center;
            }
            
            .header-title .badge {
                margin-left: 0.75rem;
                margin-top: 0;
            }
        }
        
        /* Ajuste específico para badges responsivos */
        .badge.mb-0 {
            margin-bottom: 0 !important;
        }
        
        .header-title .badge {
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        /* Melhorar espaçamento vertical em mobile */
        @media (max-width: 991.98px) {
            .card-tools {
                margin-top: 0.5rem;
            }
            
            .mt-lg-0 {
                margin-top: 0.75rem !important;
            }
        }
    </style>
@stop

@section('js')
    <script>
        // Verificar se jQuery está disponível
        if (typeof $ === 'undefined') {
            console.error('jQuery não está carregado!');
            alert('Erro: jQuery não encontrado. Recarregue a página.');
        } else {
            console.log('jQuery encontrado:', $.fn.jquery);
        }
        
        $(document).ready(function() {
            console.log('Etapa detalhada JavaScript iniciado');
            
            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // ===== EVENTOS DOS MODAIS =====
            
            // Atualizar label do arquivo selecionado
            $(document).on('change', '.custom-file-input', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName);
            });
            
            // Submit do formulário de upload de documento
            $(document).on('submit', '#formUploadDocumento', function(e) {
                e.preventDefault();
                console.log('Submit upload documento');
                
                let formData = new FormData(this);
                let execucaoId = {{ $execucao ? $execucao->id : 'null' }};
                
                if (!execucaoId) {
                    Swal.fire('Erro!', 'Esta etapa ainda não foi iniciada', 'error');
                    return;
                }
                
                // Mostrar loading
                Swal.fire({
                    title: 'Enviando...',
                    text: 'Aguarde enquanto o documento é enviado',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: `/workflow/execucao/${execucaoId}/documento`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('Upload sucesso:', response);
                        Swal.fire('Sucesso!', response.message || 'Documento enviado com sucesso!', 'success')
                            .then(() => {
                                $('#modalUploadDocumento').modal('hide');
                                location.reload();
                            });
                    },
                    error: function(xhr) {
                        console.error('Erro upload:', xhr);
                        let message = 'Erro ao enviar documento';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            message = xhr.responseJSON.error;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        }
                        Swal.fire('Erro!', message, 'error');
                    }
                });
            });
            
            // Submit do formulário de aprovação
            $(document).on('submit', '#formAprovarDocumento', function(e) {
                e.preventDefault();
                
                let documentoId = $('#aprovarDocumentoId').val();
                let observacoes = $('#observacoesAprovacao').val();
                
                console.log('=== DEBUG APROVAÇÃO DOCUMENTO ===');
                console.log('Documento ID:', documentoId);
                console.log('Observações:', observacoes);
                console.log('Observações length:', observacoes ? observacoes.length : 0);
                console.log('Observações empty?', observacoes === '');
                
                let dados = {
                    _token: '{{ csrf_token() }}',
                    observacoes: observacoes
                };
                
                console.log('Dados a serem enviados:', dados);
                
                $.post(`/workflow/documento/${documentoId}/aprovar`, dados)
                .done(function(response) {
                    console.log('Resposta do servidor:', response);
                    Swal.fire('Sucesso!', response.message, 'success')
                        .then(() => {
                            $('#modalAprovarDocumento').modal('hide');
                            location.reload();
                        });
                })
                .fail(function(xhr) {
                    console.error('Erro na requisição:', xhr);
                    console.error('Response text:', xhr.responseText);
                    let message = xhr.responseJSON?.error || 'Erro ao aprovar documento';
                    Swal.fire('Erro!', message, 'error');
                });
            });
            
            // Submit do formulário de reprovação
            $(document).on('submit', '#formReprovarDocumento', function(e) {
                e.preventDefault();
                
                let documentoId = $('#reprovarDocumentoId').val();
                let motivo_reprovacao = $('#motivoReprovacao').val();
                
                // Confirmação adicional
                Swal.fire({
                    title: 'Confirmar Reprovação',
                    text: 'Tem certeza que deseja reprovar este documento? Esta ação irá devolver a etapa para correção.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, reprovar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(`/workflow/documento/${documentoId}/reprovar`, {
                            _token: '{{ csrf_token() }}',
                            motivo_reprovacao: motivo_reprovacao
                        })
                        .done(function(response) {
                            Swal.fire('Documento Reprovado!', response.message, 'success')
                                .then(() => {
                                    $('#modalReprovarDocumento').modal('hide');
                                    location.reload();
                                });
                        })
                        .fail(function(xhr) {
                            let message = xhr.responseJSON?.error || 'Erro ao reprovar documento';
                            Swal.fire('Erro!', message, 'error');
                        });
                    }
                });
            });

            // Submit do formulário de alteração de status da etapa
            $(document).on('submit', '#formAlterarStatusEtapa', function(e) {
                e.preventDefault();
                
                let execucaoId = $('#alterarStatusExecucaoId').val();
                let statusId = $('#novoStatus').val();
                let justificativa = $('#justificativaStatus').val();
                let observacoes = $('#observacoesStatus').val();
                
                $.post(`/workflow/execucao/${execucaoId}/alterar-status`, {
                    _token: '{{ csrf_token() }}',
                    status_id: statusId,
                    justificativa: justificativa,
                    observacoes: observacoes
                })
                .done(function(response) {
                    Swal.fire('Status Alterado!', response.message, 'success')
                        .then(() => {
                            $('#modalAlterarStatusEtapa').modal('hide');
                            location.reload();
                        });
                })
                .fail(function(xhr) {
                    let message = xhr.responseJSON?.error || 'Erro ao alterar status';
                    Swal.fire('Erro!', message, 'error');
                });
            });

            // Controlar exibição da justificativa baseado no status selecionado
            $(document).on('change', '#novoStatus', function() {
                let selectedOption = $(this).find('option:selected');
                let requerJustificativa = selectedOption.data('requer-justificativa');
                
                if (requerJustificativa) {
                    $('#grupoJustificativa').show();
                    $('#justificativaStatus').prop('required', true);
                } else {
                    $('#grupoJustificativa').hide();
                    $('#justificativaStatus').prop('required', false);
                }
            });
            
            // Limpar formulários ao fechar modais
            $(document).on('hidden.bs.modal', '#modalUploadDocumento', function() {
                $('#formUploadDocumento')[0].reset();
                $('.custom-file-label').html('Escolher arquivo...');
            });
            
            $(document).on('hidden.bs.modal', '#modalAprovarDocumento', function() {
                $('#formAprovarDocumento')[0].reset();
            });
            
            $(document).on('hidden.bs.modal', '#modalReprovarDocumento', function() {
                $('#formReprovarDocumento')[0].reset();
            });

            $(document).on('hidden.bs.modal', '#modalAlterarStatusEtapa', function() {
                $('#formAlterarStatusEtapa')[0].reset();
                $('#grupoJustificativa').hide();
            });
        });

        // ===== FUNÇÕES DE LAYOUT DOS DOCUMENTOS =====
        
        // Alternar entre visualização em tabela e grade
        window.toggleViewMode = function(mode) {
            console.log('Alternando para modo:', mode);
            
            if (mode === 'table') {
                $('#documentos-table-view').show();
                $('#documentos-grid-view').hide();
                $('#btn-view-table').addClass('active');
                $('#btn-view-grid').removeClass('active');
                localStorage.setItem('documentos_view_mode', 'table');
            } else {
                $('#documentos-table-view').hide();
                $('#documentos-grid-view').show();
                $('#btn-view-table').removeClass('active');
                $('#btn-view-grid').addClass('active');
                localStorage.setItem('documentos_view_mode', 'grid');
            }
        };
        
        // Restaurar modo de visualização salvo
        $(document).ready(function() {
            const savedMode = localStorage.getItem('documentos_view_mode') || 'grid';
            toggleViewMode(savedMode);
            
            // Ativar tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
        
        // ===== FLUXO CONDICIONAL - ESCOLHER PRÓXIMA ETAPA =====
        
        // Função para abrir o modal - A lógica completa está no include do modal
        window.escolherProximaEtapa = function() {
            const execucaoId = {{ $execucao->id ?? 'null' }};
            if (execucaoId && typeof abrirModalEscolherEtapa === 'function') {
                abrirModalEscolherEtapa(execucaoId);
            } else {
                Swal.fire('Erro', 'Modal de escolha não carregado corretamente. Recarregue a página.', 'error');
            }
        }

        // Visualizar observações de documento
        window.verObservacoes = function(documentoId) {
            console.log('Ver observações do documento:', documentoId);
            
            // Buscar documento nos dados já carregados
            const documentos = @json($documentosEnviados);
            let documento = null;
            
            // Procurar o documento
            Object.values(documentos).forEach(doc => {
                if (doc && doc.id === documentoId) {
                    documento = doc;
                }
            });
            
            if (!documento) {
                Swal.fire('Erro', 'Documento não encontrado', 'error');
                return;
            }
            
            // Atualizar título do modal baseado no status
            let tituloModal = 'Observações do Documento';
            let headerClass = 'bg-info';
            
            if (documento.status_documento === 'APROVADO') {
                tituloModal = 'Documento Aprovado - Observações';
                headerClass = 'bg-success';
            } else if (documento.status_documento === 'REPROVADO') {
                tituloModal = 'Documento Reprovado - Observações';
                headerClass = 'bg-danger';
            }
            
            // Atualizar header do modal
            $('#modalObservacoesDocumento .modal-header').removeClass('bg-info bg-success bg-danger').addClass(headerClass);
            $('#modalObservacoesDocumento .modal-title').html('<i class="fas fa-comment-alt"></i> ' + tituloModal);
            
            let content = '<div class="row">';
            
            // Informações básicas
            content += '<div class="col-12 mb-3">';
            content += '<div class="card card-outline card-info">';
            content += '<div class="card-header"><h6 class="mb-0"><i class="fas fa-file-alt"></i> ' + documento.nome_arquivo + '</h6></div>';
            content += '<div class="card-body">';
            content += '<p><strong>Status:</strong> <span class="badge badge-' + (documento.status_documento === 'APROVADO' ? 'success' : (documento.status_documento === 'REPROVADO' ? 'danger' : 'warning')) + '">' + documento.status_documento + '</span></p>';
            content += '<p><strong>Enviado em:</strong> ' + new Date(documento.data_upload).toLocaleString('pt-BR') + '</p>';
            content += '<p><strong>Enviado por:</strong> ' + documento.usuario_upload.name + '</p>';
            content += '</div>';
            content += '</div>';
            content += '</div>';
            
            // Observações do documento (dinâmicas baseadas no status)
            if (documento.observacoes) {
                content += '<div class="col-12 mb-3">';
                
                // Definir classe e título baseado no status
                let alertClass = 'alert-info';
                let iconClass = 'fa-comment-alt';
                let titulo = 'Observações do Documento';
                
                if (documento.status_documento === 'APROVADO') {
                    alertClass = 'alert-success';
                    iconClass = 'fa-check-circle';
                    titulo = 'Observações da Aprovação';
                } else if (documento.status_documento === 'REPROVADO') {
                    alertClass = 'alert-danger';
                    iconClass = 'fa-times-circle';
                    titulo = 'Observações da Reprovação';
                } else {
                    // PENDENTE ou outros status
                    alertClass = 'alert-info';
                    iconClass = 'fa-comment-alt';
                    titulo = 'Observações do Documento';
                }
                
                content += '<div class="alert ' + alertClass + '">';
                content += '<h6 class="alert-heading"><i class="fas ' + iconClass + '"></i> ' + titulo + '</h6>';
                content += '<p class="mb-0">' + documento.observacoes + '</p>';
                
                // Se aprovado, mostrar dados da aprovação
                if (documento.status_documento === 'APROVADO' && documento.data_aprovacao) {
                    content += '<hr>';
                    content += '<small class="mb-0"><strong>Aprovado em:</strong> ' + new Date(documento.data_aprovacao).toLocaleString('pt-BR');
                    if (documento.usuario_aprovacao) {
                        content += ' por ' + documento.usuario_aprovacao.name;
                    }
                    content += '</small>';
                } else if (documento.status_documento === 'REPROVADO' && documento.data_reprovacao) {
                    content += '<hr>';
                    content += '<small class="mb-0"><strong>Reprovado em:</strong> ' + new Date(documento.data_reprovacao).toLocaleString('pt-BR');
                    if (documento.usuario_reprovacao) {
                        content += ' por ' + documento.usuario_reprovacao.name;
                    }
                    content += '</small>';
                }
                
                content += '</div>';
                content += '</div>';
            }
            
            // Motivo de reprovação
            if (documento.motivo_reprovacao) {
                content += '<div class="col-12 mb-3">';
                content += '<div class="alert alert-danger">';
                content += '<h6 class="alert-heading"><i class="fas fa-times-circle"></i> Motivo da Reprovação</h6>';
                content += '<p class="mb-0">' + documento.motivo_reprovacao + '</p>';
                content += '</div>';
                content += '</div>';
            }
            
            content += '</div>';
            
            $('#observacoes-content').html(content);
            $('#modalObservacoesDocumento').modal('show');
        };

        // ===== FUNÇÕES GLOBAIS =====
        
        // Iniciar etapa
        function iniciarEtapa() {
            Swal.fire({
                title: 'Iniciar Etapa',
                text: 'Deseja iniciar esta etapa do workflow?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, iniciar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(`/workflow/acao/{{ $acao->id }}/etapa/{{ $etapaFluxo->id }}/iniciar`, {
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        if (response.success) {
                            Swal.fire('Sucesso!', response.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Erro!', response.error || 'Erro ao iniciar etapa', 'error');
                        }
                    })
                    .fail(function(xhr) {
                        const error = xhr.responseJSON?.error || 'Erro ao iniciar etapa';
                        Swal.fire('Erro!', error, 'error');
                    });
                }
            });
        }

        // Enviar documento
        function enviarDocumento(tipoDocumentoId) {
            console.log('Enviar documento:', tipoDocumentoId);
            $('#modalUploadDocumento').modal('show');
            $('#uploadTipoDocumentoId').val(tipoDocumentoId);
            @if($execucao)
                $('#uploadExecucaoId').val({{ $execucao->id }});
            @else
                Swal.fire('Erro!', 'Esta etapa ainda não foi iniciada', 'error');
                return;
            @endif
        }

        // Aprovar documento
        function aprovarDocumento(documentoId, nomeDocumento) {
            console.log('Aprovar documento:', documentoId, nomeDocumento);
            $('#modalAprovarDocumento').modal('show');
            $('#aprovarDocumentoId').val(documentoId);
            $('#nomeDocumento').val(nomeDocumento);
        }

        // Reprovar documento
        function reprovarDocumento(documentoId, nomeDocumento) {
            console.log('Reprovar documento:', documentoId, nomeDocumento);
            $('#modalReprovarDocumento').modal('show');
            $('#reprovarDocumentoId').val(documentoId);
            $('#nomeDocumento').val(nomeDocumento);
        }

        // Concluir etapa
        function concluirEtapa() {
            console.log('Concluir etapa');
            $('#modalConcluirEtapa').modal('show');
            @if($execucao)
                $('#concluirExecucaoId').val({{ $execucao->id }});
            @endif
        }

        // Ver histórico
        function verHistorico() {
            @if($execucao && $execucao->id)
                window.location.href = '{{ route("workflow.historico-etapa", $execucao->id) }}';
            @else
                Swal.fire('Aviso', 'Esta etapa ainda não foi iniciada', 'warning');
            @endif
        }

        // Alterar status da etapa
        function alterarStatusEtapa() {
            console.log('Alterar status da etapa');
            @if($execucao)
                $('#alterarStatusExecucaoId').val({{ $execucao->id }});
                carregarOpcoesStatusEtapa({{ $execucao->id }});
                $('#modalAlterarStatusEtapa').modal('show');
            @else
                Swal.fire('Erro!', 'Esta etapa ainda não foi iniciada', 'error');
            @endif
        }

        // Carregar opções de status para a etapa
        function carregarOpcoesStatusEtapa(execucaoId) {
            console.log('=== DEBUG: Carregando opções de status ===');
            console.log('Execução ID:', execucaoId);
            
            // Limpar select e mostrar loading
            const select = $('#novoStatus');
            select.empty().append('<option value="">Carregando opções...</option>');
            
            $.ajax({
                url: `/workflow/execucao/${execucaoId}/opcoes-status`,
                type: 'GET',
                beforeSend: function() {
                    console.log('Enviando requisição para:', `/workflow/execucao/${execucaoId}/opcoes-status`);
                },
                success: function(response) {
                    console.log('=== RESPOSTA RECEBIDA ===');
                    console.log('Response completa:', response);
                    console.log('Opções encontradas:', response.opcoes ? response.opcoes.length : 0);
                    
                    if(response.debug) {
                        console.log('=== DEBUG INFO ===');
                        console.log('User org ID:', response.debug.user_org_id);
                        console.log('Etapa fluxo ID:', response.debug.etapa_fluxo_id);
                        console.log('É solicitante?', response.debug.is_solicitante);
                        console.log('É executora?', response.debug.is_executora);
                    }
                    
                    select.empty().append('<option value="">Selecione o novo status</option>');
                    
                    if (response.opcoes && response.opcoes.length > 0) {
                        console.log('Adicionando opções ao select...');
                        response.opcoes.forEach(function(opcao, index) {
                            console.log(`Opção ${index + 1}:`, opcao.nome, `(ID: ${opcao.id})`);
                            
                            const option = $('<option></option>')
                                .val(opcao.id)
                                .text(opcao.nome)
                                .data('requer-justificativa', opcao.requer_justificativa);
                            
                            if (opcao.cor) {
                                option.css('color', opcao.cor);
                            }
                            
                            select.append(option);
                        });
                        
                        console.log('✅ Opções adicionadas com sucesso!');
                    } else {
                        console.log('❌ Nenhuma opção retornada pelo servidor');
                        select.empty().append('<option value="">Nenhuma opção disponível</option>');
                        
                        // Mostrar informações de debug se disponíveis
                        let debugMsg = 'Nenhuma opção de status disponível para esta etapa.';
                        if(response.debug) {
                            debugMsg += `\n\nDebug:\n- Sua organização: ${response.debug.user_org_id}\n- É solicitante: ${response.debug.is_solicitante}\n- É executora: ${response.debug.is_executora}`;
                        }
                        
                        Swal.fire({
                            title: 'Aviso',
                            text: debugMsg,
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, textStatus, errorThrown) {
                    console.error('=== ERRO NA REQUISIÇÃO ===');
                    console.error('Status:', xhr.status);
                    console.error('Status Text:', textStatus);
                    console.error('Error:', errorThrown);
                    console.error('Response Text:', xhr.responseText);
                    
                    if(xhr.responseJSON) {
                        console.error('Response JSON:', xhr.responseJSON);
                    }
                    
                    select.empty().append('<option value="">Erro ao carregar</option>');
                    
                    let message = 'Erro ao carregar opções de status';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        message = xhr.responseJSON.error;
                    } else if (xhr.status === 403) {
                        message = 'Sem permissão para acessar as opções de status desta etapa';
                    } else if (xhr.status === 404) {
                        message = 'Execução não encontrada';
                    }
                    
                    Swal.fire('Erro!', message, 'error');
                }
            });
        }

        // ===== ALIASES PARA COMPATIBILIDADE =====
        // Alias para uploadDocumento (compatibilidade com código antigo)
        function uploadDocumento(tipoDocumentoId, templateId, nomeTemplate) {
            console.log('uploadDocumento chamado (alias), redirecionando para enviarDocumento');
            enviarDocumento(tipoDocumentoId);
        }

        // ===== ALIASES PARA COMPATIBILIDADE =====
        // Alias para uploadDocumento (compatibilidade com código antigo)
        function uploadDocumento(tipoDocumentoId, templateId, nomeTemplate) {
            console.log('uploadDocumento chamado (alias), redirecionando para enviarDocumento');
            enviarDocumento(tipoDocumentoId);
        }
    </script>
@stop 