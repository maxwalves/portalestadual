@extends('adminlte::page')

@section('title', 'Workflow - ' . $acao->nome)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>
                <i class="fas fa-route text-primary"></i>
                Workflow da Ação
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acoes.index') }}">Ações</a></li>
                <li class="breadcrumb-item active">Workflow</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Informações da Ação -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i>
                Informações da Ação
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Nome:</strong> {{ $acao->nome }}<br>
                    <strong>Código:</strong> {{ $acao->codigo_referencia ?? 'N/A' }}<br>
                    <strong>Status:</strong> 
                    <span class="badge badge-{{ $acao->status === 'EM_EXECUCAO' ? 'warning' : 'secondary' }}">
                        {{ ucfirst(str_replace('_', ' ', $acao->status)) }}
                    </span>
                </div>
                <div class="col-md-6">
                    <strong>Organização:</strong> {{ $acao->demanda->termoAdesao->organizacao->nome }}<br>
                    <strong>Valor Estimado:</strong> R$ {{ number_format($acao->valor_estimado ?? 0, 2, ',', '.') }}<br>
                    <strong>Execução:</strong> {{ number_format($acao->percentual_execucao ?? 0, 1) }}%
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline do Workflow -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tasks"></i>
                Etapas do Workflow
            </h3>
        </div>
        <div class="card-body">
            <div class="timeline">
                @foreach($etapasFluxo as $index => $etapaFluxo)
                    @php
                        $execucao = $execucoes->get($etapaFluxo->id);
                        $isAtual = $etapaAtual && $etapaAtual->id === $etapaFluxo->id;
                        $isEmAndamento = $execucao && in_array($execucao->status->codigo, ['PENDENTE', 'EM_ANALISE', 'DEVOLVIDO']);
                        $isConcluida = $execucao && $execucao->status->codigo === 'APROVADO';
                        
                        // Ícone baseado no status
                        $icone = 'fas fa-circle';
                        $corIcone = 'bg-secondary';
                        
                        if ($isConcluida) {
                            $icone = 'fas fa-check-circle';
                            $corIcone = 'bg-success';
                        } elseif ($isEmAndamento) {
                            $icone = 'fas fa-clock';
                            $corIcone = 'bg-primary';
                        } elseif ($isAtual) {
                            $icone = 'fas fa-play-circle';
                            $corIcone = 'bg-info';
                        }
                        
                        $acessibilidade = $etapasAcessiveis->get($etapaFluxo->id, ['pode_acessar' => true, 'pode_ver_detalhes' => true, 'pode_ver_historico' => false]);
                        $podeAcessar = $acessibilidade['pode_acessar'];
                        $podeVerDetalhes = $acessibilidade['pode_ver_detalhes'];
                        $podeVerHistorico = $acessibilidade['pode_ver_historico'];
                        $motivoBloqueio = $acessibilidade['motivo_bloqueio'];
                    @endphp

                    <div class="timeline-item etapa-card">
                        <i class="{{ $icone }} {{ $corIcone }}"></i>
                        
                        <div class="timeline-item compact-timeline-item">
                            <!-- Header da Etapa - Mais Compacto -->
                            <div class="etapa-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="etapa-title">
                                        @if($podeVerDetalhes)
                                            <a href="{{ route('workflow.etapa-detalhada', [$acao->id, $etapaFluxo->id]) }}" 
                                               class="text-decoration-none etapa-link {{ !$podeAcessar ? 'text-muted' : '' }}"
                                               @if(!$podeAcessar) 
                                                   onclick="event.preventDefault(); 
                                                           Swal.fire({
                                                               title: 'Etapa Bloqueada',
                                                               text: '{{ $motivoBloqueio }}',
                                                               icon: 'warning',
                                                               confirmButtonText: 'Entendi'
                                                           });"
                                               @endif>
                                                <strong>{{ $etapaFluxo->nome_etapa }}</strong>
                                                @if(!$podeAcessar)
                                                    <i class="fas fa-lock ml-1 text-warning" title="{{ $motivoBloqueio }}"></i>
                                                @endif
                                            </a>
                                        @else
                                            <span class="text-muted">
                                                <strong>{{ $etapaFluxo->nome_etapa }}</strong>
                                                <i class="fas fa-lock ml-1 text-warning" title="{{ $motivoBloqueio }}"></i>
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="etapa-status">
                                        @if($execucao)
                                            <span class="badge badge-{{ $execucao->status->codigo === 'APROVADO' ? 'success' : ($execucao->status->codigo === 'REPROVADO' ? 'danger' : 'warning') }}">
                                                {{ $execucao->status->nome }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">Não Iniciada</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Informações Compactas -->
                            <div class="etapa-body">
                                <div class="row">
                                    <!-- Informações Principais - Coluna 1 -->
                                    <div class="col-md-8">
                                        <div class="info-compacta">
                                            <span class="info-item">
                                                <i class="fas fa-building text-muted"></i>
                                                <strong>Sol:</strong> {{ Str::limit($etapaFluxo->organizacaoSolicitante->nome, 20) }}
                                            </span>
                                            <span class="info-item">
                                                <i class="fas fa-user-cog text-muted"></i>
                                                <strong>Exec:</strong> {{ Str::limit($etapaFluxo->organizacaoExecutora->nome, 20) }}
                                            </span>
                                            <span class="info-item">
                                                <i class="fas fa-clock text-muted"></i>
                                                <strong>Prazo:</strong> {{ $etapaFluxo->prazo_dias }}d {{ $etapaFluxo->tipo_prazo === 'UTEIS' ? 'úteis' : 'corridos' }}
                                            </span>
                                            @if($etapaFluxo->grupoExigencia)
                                                <span class="info-item">
                                                    <i class="fas fa-folder text-muted"></i>
                                                    <strong>Docs:</strong> {{ $etapaFluxo->grupoExigencia->templatesDocumento->count() }} necessário(s)
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Ações - Coluna 2 -->
                                    <div class="col-md-4 text-right">
                                        <div class="etapa-actions">
                                            @if(!$execucao && $podeAcessar && $permissoes['pode_iniciar_etapa'] && $isAtual)
                                                <button class="btn btn-success btn-sm" onclick="iniciarEtapa({{ $etapaFluxo->id }})">
                                                    <i class="fas fa-play"></i> Iniciar
                                                </button>
                                            @elseif(!$execucao && !$podeAcessar)
                                                <button class="btn btn-outline-secondary btn-sm" disabled title="{{ $motivoBloqueio }}">
                                                    <i class="fas fa-lock"></i> Bloqueada
                                                </button>
                                            @elseif(!$execucao && !$isAtual)
                                                <small class="text-muted">
                                                    <i class="fas fa-hourglass-half"></i> Aguardando
                                                </small>
                                            @endif
                                            
                                            @if($execucao && $permissoes['pode_concluir_etapa'] && $isEmAndamento)
                                                <button class="btn btn-primary btn-sm" onclick="alterarStatusEtapa({{ $execucao->id }})">
                                                    <i class="fas fa-check"></i> Concluir
                                                </button>
                                            @endif

                                            @if($podeVerDetalhes)
                                                <a href="{{ route('workflow.etapa-detalhada', [$acao->id, $etapaFluxo->id]) }}" 
                                                   class="btn btn-outline-info btn-sm" title="Ver detalhes">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif
                                            
                                            @if($execucao && $execucao->id && $podeVerHistorico)
                                                <a href="{{ route('workflow.historico-etapa', $execucao->id) }}" 
                                                   class="btn btn-outline-secondary btn-sm" title="Histórico">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Progress dos Documentos (se houver execução) -->
                                @if($execucao && $etapaFluxo->grupoExigencia)
                                    @php
                                        $totalDocumentos = $etapaFluxo->grupoExigencia->templatesDocumento->count();
                                        $documentosEnviados = $execucao->documentos->count();
                                        $documentosAprovados = $execucao->documentos->where('status_documento', 'APROVADO')->count();
                                        $percentual = $totalDocumentos > 0 ? ($documentosAprovados / $totalDocumentos) * 100 : 0;
                                    @endphp
                                    
                                    <div class="progress-documentos mt-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted">Progresso dos Documentos</small>
                                            <small class="text-muted">{{ $documentosAprovados }}/{{ $totalDocumentos }}</small>
                                        </div>
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-success" style="width: {{ $percentual }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Conclusão do Workflow -->
                <div class="timeline-item">
                    <i class="fas fa-flag-checkered bg-success"></i>
                    <div class="timeline-item compact-timeline-item">
                        <div class="etapa-header">
                            <h6 class="mb-2">
                                <i class="fas fa-trophy text-success"></i>
                                Workflow Concluído
                            </h6>
                        </div>
                        <div class="etapa-body">
                            @if($execucoes->where('status.codigo', 'APROVADO')->count() === $etapasFluxo->count())
                                <div class="alert alert-success alert-sm mb-0">
                                    <i class="fas fa-check-circle"></i>
                                    Todas as etapas foram concluídas com sucesso!
                                </div>
                            @else
                                <p class="mb-0 text-muted">
                                    <small>O workflow será concluído quando todas as etapas forem aprovadas.</small>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modais -->
    @include('workflow.modals.upload-documento')
    @include('workflow.modals.aprovar-documento')
    @include('workflow.modals.reprovar-documento')
    @include('workflow.modals.alterar-status-etapa')
    @include('workflow.modals.concluir-etapa')
    @include('workflow.modals.historico-etapa')

@stop

@section('css')
    <style>
        /* ===== LAYOUT COMPACTO PARA ETAPAS DO WORKFLOW ===== */
        
        .compact-timeline-item {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-radius: 8px;
            border: 1px solid #e3e6f0;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
        }
        
        .compact-timeline-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-color: #5a6c7d;
        }
        
        .etapa-card {
            margin-bottom: 1rem;
        }
        
        /* Header da Etapa */
        .etapa-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1rem;
            border-radius: 8px 8px 0 0;
        }
        
        .etapa-title {
            flex: 1;
        }
        
        .etapa-link {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            transition: color 0.3s ease;
        }
        
        .etapa-link:hover {
            color: #3498db;
            text-decoration: none;
        }
        
        .etapa-status .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        /* Body da Etapa */
        .etapa-body {
            padding: 0.75rem 1rem;
            background: #ffffff;
            border-radius: 0 0 8px 8px;
        }
        
        /* Informações Compactas */
        .info-compacta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .info-item i {
            width: 16px;
            margin-right: 0.25rem;
            font-size: 0.8rem;
        }
        
        .info-item strong {
            margin-right: 0.25rem;
            color: #495057;
        }
        
        /* Ações da Etapa */
        .etapa-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            justify-content: flex-end;
            align-items: center;
        }
        
        .etapa-actions .btn {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        
        /* Progress dos Documentos */
        .progress-documentos {
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid #f1f3f4;
        }
        
        .progress-documentos .progress {
            border-radius: 2px;
            background-color: #f1f3f4;
        }
        
        .progress-documentos .progress-bar {
            border-radius: 2px;
        }
        
        /* Timeline Icons - Mais Compactos */
        .timeline > .timeline-item > .fas,
        .timeline > .timeline-item > .far,
        .timeline > .timeline-item > .fab {
            width: 30px;
            height: 30px;
            font-size: 14px;
            line-height: 30px;
            border-radius: 50%;
            text-align: center;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .timeline::before {
            background-color: #e3e6f0;
            width: 2px;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .info-compacta {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .etapa-actions {
                justify-content: center;
                margin-top: 0.5rem;
            }
            
            .col-md-4.text-right {
                text-align: center !important;
            }
            
            .etapa-header .d-flex {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .etapa-status {
                text-align: center;
            }
        }
        
        /* Estados das Etapas */
        .etapa-card:has(.bg-success) .compact-timeline-item {
            border-left: 4px solid #28a745;
        }
        
        .etapa-card:has(.bg-primary) .compact-timeline-item {
            border-left: 4px solid #007bff;
            animation: pulse-border 2s infinite;
        }
        
        .etapa-card:has(.bg-info) .compact-timeline-item {
            border-left: 4px solid #17a2b8;
        }
        
        .etapa-card:has(.bg-secondary) .compact-timeline-item {
            border-left: 4px solid #6c757d;
        }
        
        @keyframes pulse-border {
            0% {
                border-left-color: #007bff;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            }
            50% {
                border-left-color: #0056b3;
                box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
            }
            100% {
                border-left-color: #007bff;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            }
        }
        
        /* Badges personalizados */
        .badge {
            font-weight: 500;
        }
        
        .badge-success {
            background-color: #28a745 !important;
        }
        
        .badge-danger {
            background-color: #dc3545 !important;
        }
        
        .badge-warning {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }
        
        .badge-info {
            background-color: #17a2b8 !important;
        }
        
        .badge-secondary {
            background-color: #6c757d !important;
        }
        
        /* Botões compactos */
        .btn-sm {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            line-height: 1.5;
        }
        
        /* Alert compacto */
        .alert-sm {
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        /* Melhoria na timeline geral */
        .timeline {
            margin-bottom: 0;
        }
        
        .timeline-item {
            margin-bottom: 1rem;
        }
        
        /* Hover effects */
        .etapa-actions .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        /* Loading states */
        .btn:disabled {
            opacity: 0.6;
            transform: none !important;
        }
        
        /* Text utilities */
        .text-muted {
            color: #6c757d !important;
        }
        
        small.text-muted {
            font-size: 0.8rem;
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
        
        // Aguardar carregamento completo do jQuery e DOM
        $(document).ready(function() {
            console.log('Workflow JavaScript iniciado');
            
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
                let execucaoId = $('#uploadExecucaoId').val();
                
                if (!execucaoId) {
                    Swal.fire('Erro!', 'ID da execução não encontrado', 'error');
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
                
                $.post(`/workflow/documento/${documentoId}/aprovar`, {
                    _token: '{{ csrf_token() }}',
                    observacoes: observacoes
                })
                .done(function(response) {
                    Swal.fire('Sucesso!', response.message, 'success')
                        .then(() => {
                            $('#modalAprovarDocumento').modal('hide');
                            location.reload();
                        });
                })
                .fail(function(xhr) {
                    let message = xhr.responseJSON?.error || 'Erro ao aprovar documento';
                    Swal.fire('Erro!', message, 'error');
                });
            });
            
            // Submit do formulário de reprovação
            $(document).on('submit', '#formReprovarDocumento', function(e) {
                e.preventDefault();
                
                let documentoId = $('#reprovarDocumentoId').val();
                let motivo = $('#motivoReprovacao').val();
                
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
                            motivo: motivo
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
            
            // Submit do formulário de alteração de status
            $(document).on('submit', '#formAlterarStatusEtapa', function(e) {
                e.preventDefault();
                
                let execucaoId = $('#alterarStatusExecucaoId').val();
                let novoStatusId = $('#novoStatusId').val();
                let observacoes = $('#observacoesAlteracao').val();
                
                if (!novoStatusId) {
                    Swal.fire('Erro!', 'Selecione um status para prosseguir', 'error');
                    return;
                }
                
                // Mostrar loading
                Swal.fire({
                    title: 'Alterando Status...',
                    text: 'Aguarde enquanto o status é alterado',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.post(`/workflow/execucao/${execucaoId}/alterar-status`, {
                    _token: '{{ csrf_token() }}',
                    novo_status_id: novoStatusId,
                    observacoes: observacoes
                })
                .done(function(response) {
                    console.log('Status alterado:', response);
                    Swal.fire('Sucesso!', response.message, 'success')
                        .then(() => {
                            $('#modalAlterarStatusEtapa').modal('hide');
                            location.reload();
                        });
                })
                .fail(function(xhr) {
                    console.error('Erro ao alterar status:', xhr);
                    let message = xhr.responseJSON?.error || 'Erro ao alterar status';
                    Swal.fire('Erro!', message, 'error');
                });
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
                $('#novoStatusId').val('');
            });
        });

        // ===== FUNÇÕES GLOBAIS DE WORKFLOW =====
        
        // Iniciar etapa
        function iniciarEtapa(etapaFluxoId) {
            console.log('Iniciar etapa:', etapaFluxoId);
            Swal.fire({
                title: 'Iniciar Etapa',
                text: 'Deseja iniciar esta etapa do workflow?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, iniciar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(`/workflow/acao/{{ $acao->id }}/etapa/${etapaFluxoId}/iniciar`, {
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        console.log('Etapa iniciada:', response);
                        Swal.fire('Sucesso!', response.message, 'success')
                            .then(() => location.reload());
                    })
                    .fail(function(xhr) {
                        console.error('Erro ao iniciar etapa:', xhr);
                        Swal.fire('Erro!', xhr.responseJSON?.error || 'Erro ao iniciar etapa', 'error');
                    });
                }
            });
        }

        // Enviar documento
        function enviarDocumento(execucaoId, tipoDocumentoId) {
            console.log('Enviar documento:', execucaoId, tipoDocumentoId);
            $('#modalUploadDocumento').modal('show');
            $('#uploadExecucaoId').val(execucaoId);
            $('#uploadTipoDocumentoId').val(tipoDocumentoId);
        }

        // Aprovar documento
        function aprovarDocumento(documentoId) {
            console.log('Aprovar documento:', documentoId);
            $('#modalAprovarDocumento').modal('show');
            $('#aprovarDocumentoId').val(documentoId);
        }

        // Reprovar documento
        function reprovarDocumento(documentoId) {
            console.log('Reprovar documento:', documentoId);
            $('#modalReprovarDocumento').modal('show');
            $('#reprovarDocumentoId').val(documentoId);
        }

        // Alterar status da etapa
        function alterarStatusEtapa(execucaoId) {
            console.log('Alterar status etapa:', execucaoId);
            
            // Carregar opções de status via AJAX
            $.get(`/workflow/execucao/${execucaoId}/opcoes-status`)
                .done(function(response) {
                    if (response.opcoes && response.opcoes.length > 0) {
                        // Preencher select de status
                        let selectStatus = $('#novoStatusId');
                        selectStatus.empty();
                        selectStatus.append('<option value="">Selecione o novo status...</option>');
                        
                        response.opcoes.forEach(function(opcao) {
                            selectStatus.append(`<option value="${opcao.id}">${opcao.nome}</option>`);
                        });
                        
                        // Configurar modal
                        $('#alterarStatusExecucaoId').val(execucaoId);
                        $('#modalAlterarStatusEtapa').modal('show');
                        
                        console.log('Opções de status carregadas:', response.opcoes);
                    } else {
                        Swal.fire('Aviso', 'Não há opções de status disponíveis para esta etapa.', 'warning');
                    }
                })
                .fail(function(xhr) {
                    console.error('Erro ao carregar opções:', xhr);
                    Swal.fire('Erro!', 'Erro ao carregar opções de status', 'error');
                });
        }

        // Concluir etapa
        function concluirEtapa(execucaoId) {
            console.log('Concluir etapa:', execucaoId);
            $('#modalConcluirEtapa').modal('show');
            $('#concluirExecucaoId').val(execucaoId);
        }

        // Ver histórico
        function verHistorico(execucaoId) {
            console.log('Ver histórico:', execucaoId);
            $('#modalHistoricoEtapa').modal('show');
            carregarHistorico(execucaoId);
        }

        function carregarHistorico(execucaoId) {
            $('#historicoContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>');
            
            $.get(`/workflow/execucao/${execucaoId}/historico`)
                .done(function(response) {
                    $('#historicoContent').html(response);
                })
                .fail(function() {
                    $('#historicoContent').html('<div class="alert alert-danger">Erro ao carregar histórico</div>');
                });
        }
    </script>
@stop 