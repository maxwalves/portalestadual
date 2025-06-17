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
                @foreach($etapasFluxo as $etapaFluxo)
                    @php
                        $execucao = $execucoes->where('etapa_fluxo_id', $etapaFluxo->id)->first();
                        $isAtual = $etapaAtual && $etapaAtual->id === $etapaFluxo->id;
                        $isEmAndamento = $execucao && in_array($execucao->status->codigo, ['PENDENTE', 'EM_ANALISE', 'DEVOLVIDO']);
                        
                        // Definir cor de fundo e borda baseado no status
                        $corFundo = '#ffffff';
                        $corBorda = '#6c757d';
                        $corIcone = 'secondary';
                        $iconeEtapa = 'fas fa-circle';
                        
                        if ($execucao) {
                            switch($execucao->status->codigo) {
                                case 'APROVADO':
                                    $corFundo = '#f8fff8';
                                    $corBorda = '#28a745';
                                    $corIcone = 'success';
                                    $iconeEtapa = 'fas fa-check-circle';
                                    break;
                                case 'REPROVADO':
                                    $corFundo = '#fff8f8';
                                    $corBorda = '#dc3545';
                                    $corIcone = 'danger';
                                    $iconeEtapa = 'fas fa-times-circle';
                                    break;
                                case 'DEVOLVIDO':
                                    $corFundo = '#fff8e1';
                                    $corBorda = '#fd7e14';
                                    $corIcone = 'warning';
                                    $iconeEtapa = 'fas fa-undo-alt';
                                    break;
                                case 'EM_ANALISE':
                                    $corFundo = '#fff9e6';
                                    $corBorda = '#ffc107';
                                    $corIcone = 'warning';
                                    $iconeEtapa = 'fas fa-hourglass-half';
                                    break;
                                case 'PENDENTE':
                                    $corFundo = '#f8f9fa';
                                    $corBorda = '#007bff';
                                    $corIcone = 'info';
                                    $iconeEtapa = 'fas fa-clock';
                                    break;
                            }
                        }
                        
                        // Destacar etapa atual em trabalho
                        if ($isAtual && $isEmAndamento) {
                            $corFundo = '#e3f2fd';
                            $corBorda = '#2196f3';
                            $corIcone = 'primary';
                            $iconeEtapa = 'fas fa-play-circle';
                        }
                        
                        $estiloEtapa = "background-color: {$corFundo}; border: 3px solid {$corBorda}; box-shadow: 0 3px 6px rgba(0,0,0,0.1);";
                        if ($isAtual && $isEmAndamento) {
                            $estiloEtapa .= " animation: pulse-border 2s infinite;";
                        }
                    @endphp

                    <div class="time-label">
                        <span class="bg-{{ $corIcone }}">
                            Etapa {{ $etapaFluxo->ordem_execucao }} - {{ $etapaFluxo->nome_etapa }}
                        </span>
                    </div>

                    <div>
                        <i class="{{ $iconeEtapa }} bg-{{ $corIcone }}"></i>
                        <div class="timeline-item" style="{{ $estiloEtapa }}">
                            @if($isAtual && $isEmAndamento)
                                <div class="ribbon-wrapper ribbon-lg">
                                    <div class="ribbon bg-primary">
                                        EM TRABALHO
                                    </div>
                                </div>
                            @endif
                            
                            <span class="time">
                                <i class="fas fa-clock"></i>
                                @if($execucao)
                                    {{ $execucao->data_inicio->format('d/m/Y H:i') }}
                                    @if($execucao->data_conclusao)
                                        - {{ $execucao->data_conclusao->format('d/m/Y H:i') }}
                                    @endif
                                @else
                                    Não iniciada
                                @endif
                            </span>
                            
                            <h3 class="timeline-header">
                                <a href="{{ route('workflow.etapa-detalhada', [$acao, $etapaFluxo]) }}" 
                                   class="text-decoration-none">
                                    <i class="{{ $etapaFluxo->modulo->icone ?? 'fas fa-tasks' }}"></i>
                                    {{ $etapaFluxo->nome_etapa }}
                                    @if($isAtual && $isEmAndamento)
                                        <i class="fas fa-arrow-left text-primary ml-2" title="Etapa atual em trabalho"></i>
                                    @endif
                                </a>
                                
                                @if($execucao)
                                    @php
                                        $badgeClass = 'secondary';
                                        switch($execucao->status->codigo) {
                                            case 'APROVADO': $badgeClass = 'success'; break;
                                            case 'REPROVADO': $badgeClass = 'danger'; break;
                                            case 'DEVOLVIDO': $badgeClass = 'warning'; break;
                                            case 'EM_ANALISE': $badgeClass = 'warning'; break;
                                            case 'PENDENTE': $badgeClass = 'info'; break;
                                        }
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }} float-right">
                                        {{ $execucao->status->nome }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary float-right">Aguardando Início</span>
                                @endif
                            </h3>

                            <div class="timeline-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Descrição:</strong> {{ $etapaFluxo->descricao_customizada ?? $etapaFluxo->modulo->descricao }}</p>
                                        <p><strong>Solicitante:</strong> {{ $etapaFluxo->organizacaoSolicitante->nome }}</p>
                                        <p><strong>Executor:</strong> {{ $etapaFluxo->organizacaoExecutora->nome }}</p>
                                        <p><strong>Prazo:</strong> {{ $etapaFluxo->prazo_dias }} dias {{ $etapaFluxo->tipo_prazo === 'UTEIS' ? 'úteis' : 'corridos' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        @if($execucao)
                                            <p><strong>Responsável:</strong> {{ $execucao->usuarioResponsavel->name ?? 'N/A' }}</p>
                                            @if($execucao->observacoes)
                                                <p><strong>Observações:</strong> {{ $execucao->observacoes }}</p>
                                            @endif
                                            @if($execucao->justificativa)
                                                <p><strong>Justificativa:</strong> {{ $execucao->justificativa }}</p>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <!-- Resumo dos Documentos da Etapa -->
                                @if($etapaFluxo->grupoExigencia)
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6><i class="fas fa-folder-open"></i> Documentos da Etapa</h6>
                                            <a href="{{ route('workflow.etapa-detalhada', [$acao, $etapaFluxo]) }}" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i> Ver Detalhes
                                            </a>
                                        </div>
                                        
                                        @if($execucao)
                                            @php
                                                $totalDocumentos = $etapaFluxo->grupoExigencia->templatesDocumento->count();
                                                $documentosEnviados = $execucao->documentos->count();
                                                $documentosAprovados = $execucao->documentos->where('status_documento', 'APROVADO')->count();
                                            @endphp
                                            
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <small class="text-muted">Total de Documentos:</small>
                                                    <span class="badge badge-info">{{ $totalDocumentos }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted">Enviados:</small>
                                                    <span class="badge badge-warning">{{ $documentosEnviados }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted">Aprovados:</small>
                                                    <span class="badge badge-success">{{ $documentosAprovados }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-muted mb-2">
                                                <small>{{ $etapaFluxo->grupoExigencia->templatesDocumento->count() }} documento(s) necessário(s)</small>
                                            </p>
                                        @endif
                                    </div>
                                @endif

                                <!-- Ações da Etapa -->
                                <div class="mt-3">
                                    @if(!$execucao && $permissoes['pode_iniciar_etapa'] && $isAtual)
                                        <button class="btn btn-success btn-sm" onclick="iniciarEtapa({{ $etapaFluxo->id }})">
                                            <i class="fas fa-play"></i> Iniciar Etapa
                                        </button>
                                    @endif
                                    
                                    @if($execucao && $permissoes['pode_concluir_etapa'] && $isEmAndamento)
                                        <button class="btn btn-primary btn-sm" onclick="alterarStatusEtapa({{ $execucao->id }})">
                                            <i class="fas fa-check"></i> Concluir Etapa
                                        </button>
                                    @endif

                                    @if($execucao && $execucao->id)
                                        <a href="{{ route('workflow.historico-etapa', $execucao->id) }}" 
                                           class="btn btn-info btn-sm"
                                           title="Ver histórico desta etapa">
                                            <i class="fas fa-history"></i> Histórico
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div>
                    <i class="fas fa-flag-checkered bg-success"></i>
                    <div class="timeline-item">
                        <h3 class="timeline-header">Workflow Concluído</h3>
                        <div class="timeline-body">
                            @if($execucoes->where('status.codigo', 'APROVADO')->count() === $etapasFluxo->count())
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    Todas as etapas foram concluídas com sucesso!
                                </div>
                            @else
                                <p>O workflow será concluído quando todas as etapas forem aprovadas.</p>
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
        .timeline-item {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 0.375rem;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .timeline-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .timeline-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .badge-sm {
            font-size: 0.7em;
        }
        
        .alert-sm {
            padding: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .table-sm th,
        .table-sm td {
            padding: 0.3rem;
        }

        /* Animação para etapa atual em trabalho */
        @keyframes pulse-border {
            0% {
                border-color: #2196f3;
                box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            }
            50% {
                border-color: #1976d2;
                box-shadow: 0 0 0 4px rgba(33, 150, 243, 0.3);
            }
            100% {
                border-color: #2196f3;
                box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            }
        }

        /* Estilo para ribbon "EM TRABALHO" */
        .ribbon-wrapper {
            position: absolute !important;
            right: -2px;
            top: -2px;
            z-index: 10;
        }

        .ribbon {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: bold;
            text-align: center;
            color: white;
            position: relative;
            transform: rotate(45deg);
            transform-origin: 0 0;
            min-width: 80px;
        }

        .ribbon:before {
            content: '';
            position: absolute;
            left: 0;
            top: 100%;
            border-style: solid;
            border-width: 0 5px 5px 0;
            border-color: transparent rgba(0,0,0,0.2) transparent transparent;
        }

        /* Melhorar cores dos badges */
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
            background-color: #007bff !important;
        }

        /* Efeito para ícones da timeline */
        .timeline .timeline-item > .fas {
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        
        .timeline .timeline-item > .bg-primary {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Destacar melhor os links */
        .timeline-header a {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .timeline-header a:hover {
            text-decoration: underline !important;
        }

        /* Estilos para os cards de resumo */
        .info-box {
            border-radius: 0.375rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .info-box:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        /* Melhorar aparência dos botões */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.25rem;
            transition: all 0.2s ease;
        }

        .btn-sm:hover {
            transform: translateY(-1px);
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