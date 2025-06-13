@extends('adminlte::page')

@section('title', 'Templates de Documentos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-invoice mr-2"></i>Templates de Documentos</h1>
        <a href="{{ route('template-documentos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>Novo Template
        </a>
    </div>
@endsection

@section('content')
    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i>Filtros</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('template-documentos.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Buscar:</label>
                            <input type="text" 
                                   name="search" 
                                   id="search"
                                   class="form-control" 
                                   value="{{ request('search') }}" 
                                   placeholder="Nome ou descrição...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_documento_id">Tipo de Documento:</label>
                            <select name="tipo_documento_id" id="tipo_documento_id" class="form-control">
                                <option value="">Todos os tipos</option>
                                @foreach($tiposDocumento as $tipo)
                                    <option value="{{ $tipo->id }}" 
                                            {{ request('tipo_documento_id') == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="grupo_exigencia_id">Grupo de Exigência:</label>
                            <select name="grupo_exigencia_id" id="grupo_exigencia_id" class="form-control">
                                <option value="">Todos os grupos</option>
                                @foreach($gruposExigencia as $grupo)
                                    <option value="{{ $grupo->id }}" 
                                            {{ request('grupo_exigencia_id') == $grupo->id ? 'selected' : '' }}>
                                        {{ $grupo->nome }}
                                    </option>
                                @endforeach
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
                                <a href="{{ route('template-documentos.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i>Limpar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Templates -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-1"></i>
                Lista de Templates ({{ $templates->total() }})
            </h3>
        </div>
        <div class="card-body p-0">
            @if($templates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Tipo de Documento</th>
                                <th>Grupo de Exigência</th>
                                <th>Obrigatório</th>
                                <th>Ordem</th>
                                <th>Criado</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $template->nome }}</strong>
                                            @if($template->descricao)
                                                <br><small class="text-muted">{{ Str::limit($template->descricao, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $template->tipoDocumento->nome }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $template->grupoExigencia->nome }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($template->is_obrigatorio)
                                            <span class="badge badge-danger">Obrigatório</span>
                                        @else
                                            <span class="badge badge-secondary">Opcional</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-primary">{{ $template->ordem }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $template->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('template-documentos.show', $template) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <a href="{{ route('template-documentos.edit', $template) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('template-documentos.destroy', $template) }}" 
                                                  method="POST" 
                                                  style="display: inline-block;"
                                                  onsubmit="return confirm('Tem certeza que deseja excluir este template?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger" 
                                                        title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum template encontrado</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'tipo_documento_id', 'grupo_exigencia_id']))
                            Tente ajustar os filtros ou 
                        @endif
                        <a href="{{ route('template-documentos.create') }}">Criar o primeiro template</a>.
                    </p>
                </div>
            @endif
        </div>
        @if($templates->hasPages())
            <div class="card-footer">
                {{ $templates->links() }}
            </div>
        @endif
    </div>
@endsection 