@extends('adminlte::page')

@section('title', 'Ações')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-tasks text-primary"></i>
                    Gerenciamento de Ações
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Ações</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .icon-circle {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        
        .acao-workflow-link:hover {
            text-decoration: none !important;
        }
        
        .acao-workflow-link:hover strong {
            color: #007bff !important;
            text-decoration: underline;
        }
        
        .badge-sm {
            font-size: 0.75em;
            padding: 0.25em 0.4em;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .collapsed-card .card-body {
            display: none;
        }
        
        .card-tools .btn-tool {
            background: none;
            border: none;
            color: #fff;
        }
        
        .card-tools .btn-tool:hover {
            color: #ddd;
        }
    </style>
@stop

@section('js')
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Confirmar Exclusão',
                text: 'Tem certeza que deseja excluir esta ação? Esta ação não pode ser desfeita.',
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
        
        $(document).ready(function() {
            // Auto-expandir filtros se houver parâmetros de busca
            if (window.location.search.includes('busca=') || 
                window.location.search.includes('organizacao_solicitante=') ||
                window.location.search.includes('tipo_fluxo=') ||
                window.location.search.includes('status=')) {
                $('.collapsed-card').removeClass('collapsed-card');
                $('.card-body').show();
                $('.btn-tool i').removeClass('fas fa-plus').addClass('fas fa-minus');
            }
            
            // Tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-secondary collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter"></i>
                            Filtros de Pesquisa
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" style="display: none;">
                        <form method="GET" action="{{ route('acoes.index') }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="busca">
                                            <i class="fas fa-search"></i>
                                            Busca Geral
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="busca" 
                                               name="busca" 
                                               value="{{ request('busca') }}"
                                               placeholder="Nome, descrição ou projeto SAM...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="organizacao_solicitante">
                                            <i class="fas fa-building"></i>
                                            Organização Solicitante
                                        </label>
                                        <select class="form-control" id="organizacao_solicitante" name="organizacao_solicitante">
                                            <option value="">Todas as organizações</option>
                                            @foreach($organizacoes as $org)
                                                <option value="{{ $org->id }}" 
                                                        {{ request('organizacao_solicitante') == $org->id ? 'selected' : '' }}>
                                                    {{ $org->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="tipo_fluxo">
                                            <i class="fas fa-route"></i>
                                            Tipo de Fluxo
                                        </label>
                                        <select class="form-control" id="tipo_fluxo" name="tipo_fluxo">
                                            <option value="">Todos os tipos</option>
                                            @foreach($tiposFluxo as $tipo)
                                                <option value="{{ $tipo->id }}" 
                                                        {{ request('tipo_fluxo') == $tipo->id ? 'selected' : '' }}>
                                                    {{ $tipo->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="status">
                                            <i class="fas fa-flag"></i>
                                            Status
                                        </label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="">Todos os status</option>
                                            <option value="PLANEJAMENTO" {{ request('status') == 'PLANEJAMENTO' ? 'selected' : '' }}>
                                                Planejamento
                                            </option>
                                            <option value="EM_EXECUCAO" {{ request('status') == 'EM_EXECUCAO' ? 'selected' : '' }}>
                                                Em Execução
                                            </option>
                                            <option value="PARALISADA" {{ request('status') == 'PARALISADA' ? 'selected' : '' }}>
                                                Paralisada
                                            </option>
                                            <option value="CONCLUIDA" {{ request('status') == 'CONCLUIDA' ? 'selected' : '' }}>
                                                Concluída
                                            </option>
                                            <option value="CANCELADA" {{ request('status') == 'CANCELADA' ? 'selected' : '' }}>
                                                Cancelada
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="btn-group btn-block">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i>
                                                Filtrar
                                            </button>
                                            <a href="{{ route('acoes.index') }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i>
                                                Limpar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            Lista de Ações
                            @if($acoes->total() > 0)
                                <span class="badge badge-info">{{ $acoes->total() }} {{ $acoes->total() == 1 ? 'ação' : 'ações' }}</span>
                            @endif
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('acoes.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i>
                                Nova Ação
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

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible mx-3 mt-3">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <i class="icon fas fa-times"></i>
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center" style="width: 60px;">
                                            <i class="fas fa-hashtag"></i>
                                        </th>
                                        <th>
                                            <i class="fas fa-tasks"></i>
                                            Nome/Descrição
                                        </th>
                                        <th>
                                            <i class="fas fa-building"></i>
                                            Organizações
                                        </th>
                                        <th>
                                            <i class="fas fa-clipboard-list"></i>
                                            Demanda
                                        </th>
                                        <th>
                                            <i class="fas fa-project-diagram"></i>
                                            Projeto SAM
                                        </th>
                                        <th>
                                            <i class="fas fa-route"></i>
                                            Tipo Fluxo
                                        </th>
                                        <th class="text-right">
                                            <i class="fas fa-dollar-sign"></i>
                                            Valores
                                        </th>
                                        <th class="text-center" style="width: 160px;">
                                            <i class="fas fa-cogs"></i>
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($acoes as $acao)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge badge-secondary">{{ $acao->id }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-circle bg-primary text-white mr-3">
                                                        <i class="fas fa-tasks"></i>
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('workflow.acao', $acao) }}" 
                                                           class="text-decoration-none acao-workflow-link" 
                                                           title="Clique para ver o workflow desta ação">
                                                            <strong class="text-primary">{{ $acao->nome ?? $acao->descricao }}</strong>
                                                            <i class="fas fa-external-link-alt ml-1 text-muted" style="font-size: 0.8em;"></i>
                                                        </a>
                                                        @if($acao->descricao && $acao->nome !== $acao->descricao)
                                                            <br>
                                                            <small class="text-muted">{{ Str::limit($acao->descricao, 50) }}</small>
                                                        @endif
                                                        @if($acao->localizacao)
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="fas fa-map-marker-alt"></i>
                                                                {{ Str::limit($acao->localizacao, 40) }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($acao->demanda && $acao->demanda->termoAdesao && $acao->demanda->termoAdesao->organizacao)
                                                    <div class="mb-1">
                                                        <span class="badge badge-info badge-sm">
                                                            <i class="fas fa-paper-plane mr-1"></i>
                                                            Solicitante
                                                        </span>
                                                        <br>
                                                        <small class="font-weight-bold">{{ $acao->demanda->termoAdesao->organizacao->nome }}</small>
                                                    </div>
                                                @endif
                                                
                                                @if($acao->tipoFluxo && $acao->tipoFluxo->etapasFluxo->count() > 0)
                                                    @php
                                                        $organizacoesExecutoras = $acao->tipoFluxo->etapasFluxo
                                                            ->pluck('organizacaoExecutora')
                                                            ->filter()
                                                            ->unique('id');
                                                    @endphp
                                                    
                                                    @if($organizacoesExecutoras->count() > 0)
                                                        <div class="mt-1">
                                                            <span class="badge badge-success badge-sm">
                                                                <i class="fas fa-cogs mr-1"></i>
                                                                Executora{{ $organizacoesExecutoras->count() > 1 ? 's' : '' }}
                                                            </span>
                                                            <br>
                                                            @foreach($organizacoesExecutoras->take(2) as $org)
                                                                <small class="font-weight-bold d-block">{{ $org->nome }}</small>
                                                            @endforeach
                                                            @if($organizacoesExecutoras->count() > 2)
                                                                <small class="text-muted">
                                                                    +{{ $organizacoesExecutoras->count() - 2 }} outras
                                                                </small>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @if($acao->demanda)
                                                    <span class="badge badge-info">
                                                        {{ Str::limit($acao->demanda->descricao, 30) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($acao->projeto_sam)
                                                    <span class="badge badge-warning">
                                                        {{ $acao->projeto_sam }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($acao->tipoFluxo)
                                                    <span class="badge badge-success">
                                                        {{ $acao->tipoFluxo->nome }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($acao->valor_estimado)
                                                    <div>
                                                        <small class="text-muted">Estimado:</small>
                                                        <br>
                                                        <span class="text-success font-weight-bold">
                                                            R$ {{ number_format($acao->valor_estimado, 2, ',', '.') }}
                                                        </span>
                                                    </div>
                                                @endif
                                                @if($acao->valor_contratado)
                                                    <div class="mt-1">
                                                        <small class="text-muted">Contratado:</small>
                                                        <br>
                                                        <span class="text-primary font-weight-bold">
                                                            R$ {{ number_format($acao->valor_contratado, 2, ',', '.') }}
                                                        </span>
                                                    </div>
                                                @endif
                                                @if(!$acao->valor_estimado && !$acao->valor_contratado)
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('acoes.show', $acao) }}" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Visualizar Workflow">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('acoes.edit', $acao) }}" 
                                                       class="btn btn-warning btn-sm" 
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            title="Excluir"
                                                            onclick="confirmDelete({{ $acao->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <form id="delete-form-{{ $acao->id }}" 
                                                      action="{{ route('acoes.destroy', $acao) }}" 
                                                      method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-tasks fa-3x mb-3"></i>
                                                    <p class="h5">Nenhuma ação encontrada</p>
                                                    @if(request()->hasAny(['busca', 'organizacao_solicitante', 'tipo_fluxo', 'status']))
                                                        <p>Tente ajustar os filtros de pesquisa.</p>
                                                        <a href="{{ route('acoes.index') }}" class="btn btn-secondary">
                                                            <i class="fas fa-times"></i>
                                                            Limpar Filtros
                                                        </a>
                                                    @else
                                                        <p>Comece criando sua primeira ação.</p>
                                                        <a href="{{ route('acoes.create') }}" class="btn btn-primary">
                                                            <i class="fas fa-plus"></i>
                                                            Nova Ação
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    @if($acoes->hasPages())
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info">
                                        Mostrando {{ $acoes->firstItem() }} até {{ $acoes->lastItem() }} de {{ $acoes->total() }} resultados
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="float-right">
                                        {{ $acoes->appends(request()->query())->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .icon-circle {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        
        .acao-workflow-link:hover {
            text-decoration: none !important;
        }
        
        .acao-workflow-link:hover strong {
            color: #007bff !important;
            text-decoration: underline;
        }
        
        .badge-sm {
            font-size: 0.75em;
            padding: 0.25em 0.4em;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .collapsed-card .card-body {
            display: none;
        }
        
        .card-tools .btn-tool {
            background: none;
            border: none;
            color: #fff;
        }
        
        .card-tools .btn-tool:hover {
            color: #ddd;
        }
    </style>
@stop

@section('js')
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Confirmar Exclusão',
                text: 'Tem certeza que deseja excluir esta ação? Esta ação não pode ser desfeita.',
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
        
        $(document).ready(function() {
            // Auto-expandir filtros se houver parâmetros de busca
            if (window.location.search.includes('busca=') || 
                window.location.search.includes('organizacao_solicitante=') ||
                window.location.search.includes('tipo_fluxo=') ||
                window.location.search.includes('status=')) {
                $('.collapsed-card').removeClass('collapsed-card');
                $('.card-body').show();
                $('.btn-tool i').removeClass('fas fa-plus').addClass('fas fa-minus');
            }
            
            // Tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop 