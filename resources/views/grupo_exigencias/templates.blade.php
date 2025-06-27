@extends('adminlte::page')

@section('title', "Templates - {$grupoExigencia->nome}")

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>
                <i class="fas fa-link text-primary"></i>
                Gerenciar Templates
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('grupo-exigencias.index') }}">Grupos de Exigências</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('grupo-exigencias.show', $grupoExigencia) }}">{{ $grupoExigencia->nome }}</a></li>
                    <li class="breadcrumb-item active">Templates</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('grupo-exigencias.show', $grupoExigencia) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
@stop

@section('content')
    <!-- Informações do Grupo -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-layer-group text-primary"></i>
                {{ $grupoExigencia->nome }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    @if($grupoExigencia->descricao)
                        <p class="text-muted mb-0">{{ $grupoExigencia->descricao }}</p>
                    @else
                        <p class="text-muted mb-0"><em>Sem descrição</em></p>
                    @endif
                </div>
                <div class="col-md-4 text-right">
                    <span class="badge badge-{{ $grupoExigencia->getCorStatus() }}">
                        <i class="{{ $grupoExigencia->getIconeStatus() }}"></i>
                        {{ $grupoExigencia->getStatusFormatado() }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Templates Vinculados -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-paperclip text-success"></i>
                        Templates Vinculados
                        <span class="badge badge-info ml-2">{{ $templatesVinculados->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($templatesVinculados->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="40">Ordem</th>
                                        <th>Nome do Template</th>
                                        <th>Tipo</th>
                                        <th width="120">Status</th>
                                        <th width="150">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="templates-list">
                                    @foreach($templatesVinculados as $template)
                                        <tr data-template-id="{{ $template->id }}">
                                            <td>
                                                <span class="badge badge-secondary">{{ $template->pivot->ordem }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $template->nome }}</strong>
                                                @if($template->pivot->observacoes)
                                                    <br><small class="text-muted">{{ $template->pivot->observacoes }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">{{ $template->tipoDocumento->nome }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $template->pivot->is_obrigatorio ? 'danger' : 'info' }}">
                                                    <i class="fas fa-{{ $template->pivot->is_obrigatorio ? 'exclamation-triangle' : 'info-circle' }}"></i>
                                                    {{ $template->pivot->is_obrigatorio ? 'Obrigatório' : 'Opcional' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarVinculo({{ $template->id }}, '{{ $template->nome }}', {{ $template->pivot->is_obrigatorio ? 'true' : 'false' }}, {{ $template->pivot->ordem }}, '{{ $template->pivot->observacoes }}')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmarDesvincular({{ $template->id }}, '{{ $template->nome }}')">
                                                        <i class="fas fa-unlink"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum template vinculado</h5>
                            <p class="text-muted">Vincule templates na lista ao lado para começar.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Templates Disponíveis -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle text-info"></i>
                        Vincular Template
                    </h5>
                </div>
                <div class="card-body">
                    @if($templatesDisponiveis->count() > 0)
                        <form action="{{ route('grupo-exigencias.vincular-template', $grupoExigencia) }}" method="POST">
                            @csrf
                            
                            <div class="form-group">
                                <label for="template_documento_id">Template:</label>
                                <select name="template_documento_id" id="template_documento_id" class="form-control" required>
                                    <option value="">Selecione um template...</option>
                                    @foreach($templatesDisponiveis as $template)
                                        <option value="{{ $template->id }}">
                                            {{ $template->nome }} ({{ $template->tipoDocumento->nome }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_obrigatorio" name="is_obrigatorio" checked>
                                    <label class="custom-control-label" for="is_obrigatorio">
                                        Obrigatório
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="ordem">Ordem:</label>
                                <input type="number" 
                                       name="ordem" 
                                       id="ordem" 
                                       class="form-control" 
                                       min="0" 
                                       value="{{ ($templatesVinculados->max('pivot.ordem') ?? 0) + 1 }}"
                                       placeholder="Ordem de apresentação">
                            </div>

                            <div class="form-group">
                                <label for="observacoes">Observações:</label>
                                <textarea name="observacoes" 
                                          id="observacoes" 
                                          class="form-control" 
                                          rows="3" 
                                          maxlength="1000"
                                          placeholder="Observações específicas para este grupo..."></textarea>
                                <small class="form-text text-muted">
                                    <span id="contador-chars">0</span>/1000 caracteres
                                </small>
                            </div>

                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-link"></i> Vincular Template
                            </button>
                        </form>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="text-muted mb-0">Todos os templates disponíveis já estão vinculados a este grupo.</p>
                            <a href="{{ route('template-documentos.create') }}" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-plus"></i> Criar novo template
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar vínculo -->
    <div class="modal fade" id="modalEditarVinculo" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Configurações do Template</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="formEditarVinculo" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Template:</label>
                            <div class="form-control-plaintext font-weight-bold" id="template-nome-edit"></div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_obrigatorio_edit" name="is_obrigatorio">
                                <label class="custom-control-label" for="is_obrigatorio_edit">
                                    Obrigatório
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ordem_edit">Ordem:</label>
                            <input type="number" name="ordem" id="ordem_edit" class="form-control" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="observacoes_edit">Observações:</label>
                            <textarea name="observacoes" id="observacoes_edit" class="form-control" rows="3" maxlength="1000"></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação para desvincular -->
    <div class="modal fade" id="modalDesvincular" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Desvinculação</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja desvincular o template <strong id="template-nome-desvincular"></strong> deste grupo?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atenção:</strong> O template continuará existindo e poderá ser vinculado novamente.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form id="formDesvincular" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-unlink"></i> Desvincular
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table td {
            vertical-align: middle;
        }
        .btn-group .btn {
            border-radius: 0.25rem;
            margin-left: 2px;
        }
        .card-header h5 {
            margin-bottom: 0;
        }
        .breadcrumb {
            background: none;
            padding: 0;
            margin-bottom: 0;
        }
    </style>
@stop

@section('js')
    <script>
        // Contador de caracteres
        $('#observacoes').on('input', function() {
            const length = $(this).val().length;
            $('#contador-chars').text(length);
        });

        // Função para editar vínculo
        function editarVinculo(templateId, nomeTemplate, isObrigatorio, ordem, observacoes) {
            $('#template-nome-edit').text(nomeTemplate);
            $('#is_obrigatorio_edit').prop('checked', isObrigatorio);
            $('#ordem_edit').val(ordem);
            $('#observacoes_edit').val(observacoes || '');
            
            const url = "{{ route('grupo-exigencias.atualizar-vinculo', ['grupoExigencia' => $grupoExigencia->id, 'templateDocumento' => 'TEMPLATE_ID']) }}";
            $('#formEditarVinculo').attr('action', url.replace('TEMPLATE_ID', templateId));
            
            $('#modalEditarVinculo').modal('show');
        }

        // Função para confirmar desvinculação
        function confirmarDesvincular(templateId, nomeTemplate) {
            $('#template-nome-desvincular').text(nomeTemplate);
            
            const url = "{{ route('grupo-exigencias.desvincular-template', ['grupoExigencia' => $grupoExigencia->id, 'templateDocumento' => 'TEMPLATE_ID']) }}";
            $('#formDesvincular').attr('action', url.replace('TEMPLATE_ID', templateId));
            
            $('#modalDesvincular').modal('show');
        }

        // Validação do formulário
        $('#formEditarVinculo').on('submit', function(e) {
            const ordem = parseInt($('#ordem_edit').val());
            if (ordem < 0) {
                e.preventDefault();
                alert('A ordem deve ser um número não negativo.');
                return false;
            }
        });

        // Mensagens de sucesso/erro
        @if(session('success'))
            toastr.success('{{ session('success') }}');
        @endif

        @if(session('error'))
            toastr.error('{{ session('error') }}');
        @endif
    </script>
@stop 