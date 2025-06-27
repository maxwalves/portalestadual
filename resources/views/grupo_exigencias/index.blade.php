@extends('adminlte::page')

@section('title', 'Grupos de Exigências')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">
                <i class="fas fa-layer-group text-primary mr-2"></i>
                Grupos de Exigências
            </h1>
            <small class="text-muted">Gerencie os grupos de exigências documentais</small>
        </div>
        <a href="{{ route('grupo-exigencias.create') }}" class="btn btn-success">
            <i class="fas fa-plus mr-1"></i>
            Novo Grupo
        </a>
    </div>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter mr-2"></i>
                Filtros
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('grupo-exigencias.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search">
                                <i class="fas fa-search mr-1"></i>
                                Buscar por nome ou descrição:
                            </label>
                            <input type="text" 
                                   name="search" 
                                   id="search" 
                                   class="form-control" 
                                   placeholder="Digite para buscar..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">
                                <i class="fas fa-toggle-on mr-1"></i>
                                Status:
                            </label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Todos</option>
                                <option value="ativo" {{ request('status') === 'ativo' ? 'selected' : '' }}>Ativo</option>
                                <option value="inativo" {{ request('status') === 'inativo' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-search mr-1"></i>Filtrar
                                </button>
                                <a href="{{ route('grupo-exigencias.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i>Limpar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Grupos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-2"></i>
                Lista de Grupos ({{ $gruposExigencia->total() }})
            </h3>
        </div>
        <div class="card-body p-0">
            @if($gruposExigencia->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 35%;">
                                    <i class="fas fa-tag mr-1"></i>
                                    Nome do Grupo
                                </th>
                                <th style="width: 30%;">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Descrição
                                </th>
                                <th style="width: 15%;" class="text-center">
                                    <i class="fas fa-file-alt mr-1"></i>
                                    Templates
                                </th>
                                <th style="width: 10%;" class="text-center">
                                    <i class="fas fa-toggle-on mr-1"></i>
                                    Status
                                </th>
                                <th style="width: 10%;" class="text-center">
                                    <i class="fas fa-cog mr-1"></i>
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gruposExigencia as $grupo)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle mr-3 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-layer-group"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $grupo->nome }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                    Criado em {{ $grupo->created_at->format('d/m/Y') }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($grupo->descricao)
                                            <span class="text-wrap">{{ Str::limit($grupo->descricao, 100) }}</span>
                                        @else
                                            <span class="text-muted font-italic">Sem descrição</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="badge badge-info badge-lg">
                                                {{ $grupo->templates_documento_count }}
                                            </span>
                                            @if($grupo->etapas_fluxo_count > 0)
                                                <small class="text-muted mt-1">
                                                    {{ $grupo->etapas_fluxo_count }} etapa(s)
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($grupo->is_ativo)
                                            <span class="badge badge-success">
                                                <i class="fas fa-check mr-1"></i>Ativo
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-times mr-1"></i>Inativo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('grupo-exigencias.show', $grupo) }}">
                                                    <i class="fas fa-eye mr-2"></i>Visualizar
                                                </a>
                                                <a class="dropdown-item" href="{{ route('grupo-exigencias.edit', $grupo) }}">
                                                    <i class="fas fa-edit mr-2"></i>Editar
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('grupo-exigencias.toggle-ativo', $grupo) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item">
                                                        @if($grupo->is_ativo)
                                                            <i class="fas fa-times mr-2"></i>Desativar
                                                        @else
                                                            <i class="fas fa-check mr-2"></i>Ativar
                                                        @endif
                                                    </button>
                                                </form>
                                                <button type="button" class="dropdown-item" 
                                                        onclick="duplicarGrupo({{ $grupo->id }}, '{{ $grupo->nome }}')">
                                                    <i class="fas fa-copy mr-2"></i>Duplicar
                                                </button>
                                                @if($grupo->templates_documento_count == 0 && $grupo->etapas_fluxo_count == 0)
                                                    <div class="dropdown-divider"></div>
                                                    <button type="button" class="dropdown-item text-danger" 
                                                            onclick="confirmarExclusao({{ $grupo->id }}, '{{ $grupo->nome }}')">
                                                        <i class="fas fa-trash mr-2"></i>Excluir
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum grupo de exigência encontrado</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'status']))
                            Tente ajustar os filtros ou 
                            <a href="{{ route('grupo-exigencias.index') }}">limpar a busca</a>.
                        @else
                            Comece criando seu primeiro grupo de exigência.
                        @endif
                    </p>
                    @if(!request()->hasAny(['search', 'status']))
                        <a href="{{ route('grupo-exigencias.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i>
                            Criar Primeiro Grupo
                        </a>
                    @endif
                </div>
            @endif
        </div>
        @if($gruposExigencia->hasPages())
            <div class="card-footer">
                {{ $gruposExigencia->links() }}
            </div>
        @endif
    </div>

    <!-- Modal para Duplicar Grupo -->
    <div class="modal fade" id="modalDuplicar" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formDuplicar" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-copy mr-2"></i>
                            Duplicar Grupo de Exigência
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Você está duplicando o grupo: <strong id="nomeGrupoOriginal"></strong></p>
                        <div class="form-group">
                            <label for="nomeNovo">Nome do novo grupo:</label>
                            <input type="text" class="form-control" id="nomeNovo" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="descricaoNova">Descrição (opcional):</label>
                            <textarea class="form-control" id="descricaoNova" name="descricao" rows="3"></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Todos os templates de documento serão copiados para o novo grupo.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-copy mr-1"></i>Duplicar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Excluir -->
    <div class="modal fade" id="modalExcluir" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formExcluir" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Confirmar Exclusão
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Tem certeza que deseja excluir o grupo: <strong id="nomeGrupoExcluir"></strong>?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Esta ação não pode ser desfeita!
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash mr-1"></i>Excluir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .avatar-sm {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }
    
    .badge-lg {
        font-size: 1em;
        padding: 0.5em 0.75em;
    }
    
    .card-header {
        border-bottom: 1px solid #dee2e6;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
    }
    
    .dropdown-menu {
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
    }
</style>
@stop

@section('js')
<script>
    function duplicarGrupo(id, nome) {
        document.getElementById('nomeGrupoOriginal').textContent = nome;
        document.getElementById('nomeNovo').value = nome + ' (Cópia)';
        document.getElementById('formDuplicar').action = `/grupo-exigencias/${id}/duplicar`;
        $('#modalDuplicar').modal('show');
    }
    
    function confirmarExclusao(id, nome) {
        document.getElementById('nomeGrupoExcluir').textContent = nome;
        document.getElementById('formExcluir').action = `/grupo-exigencias/${id}`;
        $('#modalExcluir').modal('show');
    }
    
    // Auto-submit do formulário de filtros com delay
    let timeoutId;
    document.getElementById('search').addEventListener('input', function() {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(function() {
            document.querySelector('form').submit();
        }, 500);
    });
</script>
@stop 