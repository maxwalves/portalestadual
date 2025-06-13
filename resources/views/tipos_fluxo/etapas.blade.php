@extends('adminlte::page')

@section('title', 'Etapas do Fluxo - ' . $tipo_fluxo->nome)

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-stream text-primary"></i>
                    Etapas do Fluxo: {{ $tipo_fluxo->nome }}
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tipos-fluxo.index') }}">Tipos de Fluxo</a></li>
                    <li class="breadcrumb-item active">Etapas</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Informações do Tipo de Fluxo -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            Informações do Tipo de Fluxo
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-tag mr-1"></i> Nome:</strong>
                                <p class="text-muted">{{ $tipo_fluxo->nome }}</p>
                                
                                <strong><i class="fas fa-layer-group mr-1"></i> Categoria:</strong>
                                <p class="text-muted">{{ $tipo_fluxo->categoria ?? 'Não definida' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-code-branch mr-1"></i> Versão:</strong>
                                <p class="text-muted">{{ $tipo_fluxo->versao }}</p>
                                
                                <strong><i class="fas fa-toggle-on mr-1"></i> Status:</strong>
                                <p class="text-muted">
                                    @if($tipo_fluxo->is_ativo)
                                        <span class="badge badge-success">Ativo</span>
                                    @else
                                        <span class="badge badge-danger">Inativo</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($tipo_fluxo->descricao)
                            <strong><i class="fas fa-align-left mr-1"></i> Descrição:</strong>
                            <p class="text-muted">{{ $tipo_fluxo->descricao }}</p>
                        @endif
                    </div>
                </div>

                <!-- Lista de Etapas -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list-ol"></i>
                            Etapas do Fluxo ({{ $tipo_fluxo->etapasFluxo->count() }} etapas)
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('etapas-fluxo.create') }}?tipo_fluxo_id={{ $tipo_fluxo->id }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i>
                                Nova Etapa
                            </a>
                            <a href="{{ route('tipos-fluxo.show', $tipo_fluxo) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                                Ver Detalhes
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible mx-3 mt-3">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <i class="icon fas fa-check"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($tipo_fluxo->etapasFluxo->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center" style="width: 80px;">
                                                <i class="fas fa-sort-numeric-up"></i> Ordem
                                            </th>
                                            <th><i class="fas fa-tasks"></i> Nome da Etapa</th>
                                            <th><i class="fas fa-cogs"></i> Módulo</th>
                                            <th><i class="fas fa-users"></i> Organizações</th>
                                            <th class="text-center"><i class="fas fa-clock"></i> Prazo</th>
                                            <th class="text-center"><i class="fas fa-exclamation-triangle"></i> Obrigatória</th>
                                            <th class="text-center" style="width: 150px;"><i class="fas fa-cogs"></i> Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tipo_fluxo->etapasFluxo->sortBy('ordem_execucao') as $etapa)
                                            <tr>
                                                <td class="text-center">
                                                    @if($etapa->ordem_execucao)
                                                        <span class="badge badge-primary">{{ $etapa->ordem_execucao }}</span>
                                                    @else
                                                        <span class="badge badge-secondary">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $etapa->nome_etapa }}</strong>
                                                    @if($etapa->descricao_customizada)
                                                        <br>
                                                        <small class="text-muted">{{ Str::limit($etapa->descricao_customizada, 60) }}</small>
                                                    @endif
                                                    @if($etapa->tipo_etapa == 'CONDICIONAL')
                                                        <br>
                                                        <span class="badge badge-warning">Condicional</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($etapa->modulo)
                                                        <span class="badge badge-info">{{ $etapa->modulo->nome }}</span>
                                                        <br>
                                                        <small class="text-muted">{{ $etapa->modulo->tipo }}</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>
                                                        <small><strong>Solicitante:</strong></small>
                                                        @if($etapa->organizacaoSolicitante)
                                                            <span class="badge badge-primary">{{ $etapa->organizacaoSolicitante->nome }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </div>
                                                    <div class="mt-1">
                                                        <small><strong>Executora:</strong></small>
                                                        @if($etapa->organizacaoExecutora)
                                                            <span class="badge badge-success">{{ $etapa->organizacaoExecutora->nome }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-secondary">{{ $etapa->prazo_dias }} {{ $etapa->tipo_prazo == 'UTEIS' ? 'dias úteis' : 'dias corridos' }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if($etapa->is_obrigatoria)
                                                        <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Sim</span>
                                                    @else
                                                        <span class="badge badge-success"><i class="fas fa-check"></i> Não</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('etapas-fluxo.show', $etapa) }}" class="btn btn-info btn-sm" title="Visualizar">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('etapas-fluxo.edit', $etapa) }}" class="btn btn-warning btn-sm" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-danger btn-sm" title="Excluir" onclick="confirmDelete({{ $etapa->id }})">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    <form id="delete-form-{{ $etapa->id }}" action="{{ route('etapas-fluxo.destroy', $etapa) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-stream fa-3x mb-3"></i>
                                    <h5>Nenhuma etapa cadastrada</h5>
                                    <p>Este tipo de fluxo ainda não possui etapas definidas.</p>
                                    <a href="{{ route('etapas-fluxo.create') }}?tipo_fluxo_id={{ $tipo_fluxo->id }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Criar primeira etapa
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Diagrama do Fluxo (Opcional - para futuras implementações) -->
                @if($tipo_fluxo->etapasFluxo->count() > 0)
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-project-diagram"></i>
                                Visualização do Fluxo
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                @foreach($tipo_fluxo->etapasFluxo->sortBy('ordem_execucao') as $index => $etapa)
                                    <div class="time-label">
                                        <span class="bg-primary">Etapa {{ $etapa->ordem_execucao ?? ($index + 1) }}</span>
                                    </div>
                                    <div>
                                        <i class="fas fa-{{ $etapa->modulo && $etapa->modulo->icone ? $etapa->modulo->icone : 'circle' }} bg-blue"></i>
                                        <div class="timeline-item">
                                            <span class="time">
                                                <i class="fas fa-clock"></i> {{ $etapa->prazo_dias }} {{ $etapa->tipo_prazo == 'UTEIS' ? 'dias úteis' : 'dias corridos' }}
                                            </span>
                                            <h3 class="timeline-header">{{ $etapa->nome_etapa }}</h3>
                                            <div class="timeline-body">
                                                @if($etapa->descricao_customizada)
                                                    <p>{{ $etapa->descricao_customizada }}</p>
                                                @endif
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <strong>Módulo:</strong> {{ $etapa->modulo->nome ?? '-' }}<br>
                                                        <strong>Solicitante:</strong> {{ $etapa->organizacaoSolicitante->nome ?? '-' }}
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>Executora:</strong> {{ $etapa->organizacaoExecutora->nome ?? '-' }}<br>
                                                        @if($etapa->grupoExigencia)
                                                            <strong>Exigências:</strong> {{ $etapa->grupoExigencia->nome }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div>
                                    <i class="fas fa-flag bg-gray"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Tem certeza?',
            text: "Esta ação não pode ser desfeita!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@stop

@section('css')
<style>
    .timeline > div > .timeline-item {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
    }
    
    .timeline > div > .timeline-item > .timeline-header {
        border-bottom: 1px solid #f4f4f4;
        color: #555;
        margin: 0;
        padding: 10px;
    }
    
    .timeline > div > .timeline-item > .timeline-body {
        padding: 10px;
    }
</style>
@stop 