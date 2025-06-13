@extends('adminlte::page')

@section('title', 'Gestão de Documentos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-alt mr-2"></i>Gestão de Documentos</h1>
        <a href="{{ route('documentos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>Novo Documento
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
            <form method="GET" action="{{ route('documentos.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Buscar:</label>
                            <input type="text" 
                                   name="search" 
                                   id="search"
                                   class="form-control" 
                                   value="{{ request('search') }}" 
                                   placeholder="Nome do arquivo ou observações...">
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
                            <label for="status_documento">Status:</label>
                            <select name="status_documento" id="status_documento" class="form-control">
                                <option value="">Todos os status</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" 
                                            {{ request('status_documento') == $value ? 'selected' : '' }}>
                                        {{ $label }}
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
                                <a href="{{ route('documentos.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i>Limpar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Documentos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-1"></i>
                Lista de Documentos ({{ $documentos->total() }})
            </h3>
        </div>
        <div class="card-body p-0">
            @if($documentos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Arquivo</th>
                                <th>Tipo</th>
                                <th>Execução/Ação</th>
                                <th>Usuário</th>
                                <th>Tamanho</th>
                                <th>Status</th>
                                <th>Upload</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documentos as $documento)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file mr-2 text-primary"></i>
                                            <div>
                                                <strong>{{ Str::limit($documento->nome_arquivo, 30) }}</strong>
                                                @if($documento->versao > 1)
                                                    <span class="badge badge-info ml-1">v{{ $documento->versao }}</span>
                                                @endif
                                                @if($documento->observacoes)
                                                    <br><small class="text-muted">{{ Str::limit($documento->observacoes, 40) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $documento->tipoDocumento->nome }}</span>
                                    </td>
                                    <td>
                                        @if($documento->execucaoEtapa && $documento->execucaoEtapa->acao)
                                            <small>
                                                <strong>{{ Str::limit($documento->execucaoEtapa->acao->nome, 25) }}</strong>
                                            </small>
                                        @else
                                            <small class="text-muted">N/A</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $documento->usuarioUpload->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ number_format($documento->tamanho_bytes / 1024 / 1024, 2) }} MB</small>
                                    </td>
                                    <td>
                                        @php
                                            $statusColor = match($documento->status_documento) {
                                                'PENDENTE' => 'secondary',
                                                'EM_ANALISE' => 'warning',
                                                'APROVADO' => 'success',
                                                'REPROVADO' => 'danger',
                                                'EXPIRADO' => 'dark',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $statusColor }}">
                                            {{ $statusOptions[$documento->status_documento] ?? $documento->status_documento }}
                                        </span>
                                        @if($documento->data_validade && $documento->data_validade < now())
                                            <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Expirado</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $documento->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('documentos.download', $documento) }}" 
                                               class="btn btn-sm btn-primary" 
                                               title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            
                                            <a href="{{ route('documentos.show', $documento) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <a href="{{ route('documentos.edit', $documento) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('documentos.destroy', $documento) }}" 
                                                  method="POST" 
                                                  style="display: inline-block;"
                                                  onsubmit="return confirm('Tem certeza que deseja excluir este documento?')">
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
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum documento encontrado</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'tipo_documento_id', 'status_documento']))
                            Tente ajustar os filtros ou 
                        @endif
                        <a href="{{ route('documentos.create') }}">criar o primeiro documento</a>.
                    </p>
                </div>
            @endif
        </div>
        @if($documentos->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        Mostrando {{ $documentos->firstItem() }} até {{ $documentos->lastItem() }} 
                        de {{ $documentos->total() }} registros
                    </div>
                    {{ $documentos->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection 