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
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-folder-open"></i>
                                Documentos Exigidos
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($templatesDocumento as $template)
                                    @php
                                        $ultimoDocumento = $documentosEnviados->get($template->tipo_documento_id);
                                    @endphp
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card card-outline {{ $ultimoDocumento ? ($ultimoDocumento->status_documento === 'APROVADO' ? 'card-success' : ($ultimoDocumento->status_documento === 'REPROVADO' ? 'card-danger' : 'card-warning')) : 'card-secondary' }}">
                                            <div class="card-header">
                                                <h5 class="card-title">
                                                    <i class="fas fa-file-alt"></i>
                                                    {{ $template->nome }}
                                                    @if($template->is_obrigatorio)
                                                        <span class="badge badge-danger ml-1">Obrigatório</span>
                                                    @endif
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                @if($ultimoDocumento)
                                                    <div class="mb-2">
                                                        <strong>Status do Documento:</strong>
                                                        <span class="badge badge-{{ $ultimoDocumento->status_documento === 'APROVADO' ? 'success' : ($ultimoDocumento->status_documento === 'REPROVADO' ? 'danger' : 'warning') }}">
                                                            {{ $ultimoDocumento->status_documento }}
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="mb-2">
                                                        <strong>Arquivo:</strong> {{ $ultimoDocumento->nome_arquivo }}<br>
                                                        <small class="text-muted">
                                                            Enviado em {{ $ultimoDocumento->data_upload->format('d/m/Y H:i') }}
                                                            por {{ $ultimoDocumento->usuarioUpload->name }}
                                                        </small>
                                                    </div>

                                                    @if($ultimoDocumento->status_documento === 'REPROVADO' && $ultimoDocumento->motivo_reprovacao)
                                                        <div class="alert alert-danger alert-sm">
                                                            <strong>Motivo da Reprovação:</strong><br>
                                                            {{ $ultimoDocumento->motivo_reprovacao }}
                                                        </div>
                                                    @endif

                                                    @if($ultimoDocumento->status_documento === 'APROVADO' && $ultimoDocumento->data_aprovacao)
                                                        <div class="alert alert-success alert-sm">
                                                            <strong>Aprovado em:</strong> {{ $ultimoDocumento->data_aprovacao->format('d/m/Y H:i') }}
                                                            @if($ultimoDocumento->usuarioAprovacao)
                                                                <br><strong>Por:</strong> {{ $ultimoDocumento->usuarioAprovacao->name }}
                                                            @endif
                                                        </div>
                                                    @endif

                                                    @if($ultimoDocumento->observacoes)
                                                        <div class="mb-2">
                                                            <strong>Observações:</strong><br>
                                                            <small>{{ $ultimoDocumento->observacoes }}</small>
                                                        </div>
                                                    @endif

                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('documentos.download', $ultimoDocumento) }}" 
                                                           class="btn btn-outline-primary" target="_blank">
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                        
                                                        @if($permissoes['pode_aprovar_documento'] && in_array($ultimoDocumento->status_documento, ['PENDENTE', 'EM_ANALISE']))
                                                            <button class="btn btn-outline-success" 
                                                                    onclick="aprovarDocumento({{ $ultimoDocumento->id }}, '{{ $template->nome }}')">
                                                                <i class="fas fa-check"></i> Aprovar
                                                            </button>
                                                            <button class="btn btn-outline-danger" 
                                                                    onclick="reprovarDocumento({{ $ultimoDocumento->id }}, '{{ $template->nome }}')">
                                                                <i class="fas fa-times"></i> Reprovar
                                                            </button>
                                                        @endif
                                                    </div>
                                                @else
                                                    <p class="text-muted mb-2">Nenhum documento enviado</p>
                                                @endif

                                                @if($permissoes['pode_enviar_documento'])
                                                    <div class="mt-2">
                                                        <button class="btn btn-primary btn-sm btn-block" 
                                                                onclick="enviarDocumento({{ $template->tipo_documento_id }})">
                                                            <i class="fas fa-upload"></i> 
                                                            {{ $ultimoDocumento ? 'Enviar Nova Versão' : 'Enviar Documento' }}
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
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
                                    <button class="btn btn-success btn-lg mr-2" onclick="alterarStatusEtapa()">
                                        <i class="fas fa-check-circle"></i> Concluir Etapa
                                    </button>
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
                            
                            <button class="btn btn-info" onclick="verHistorico()">
                                <i class="fas fa-history"></i> Ver Histórico
                            </button>
                            
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

@stop

@section('css')
    <style>
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