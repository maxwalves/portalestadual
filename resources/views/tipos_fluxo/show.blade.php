@extends('adminlte::page')

@section('title', 'Visualizar Tipo de Fluxo')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-eye text-info"></i>
                    Visualizar Tipo de Fluxo
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tipos-fluxo.index') }}">Tipos de Fluxo</a></li>
                    <li class="breadcrumb-item active">Visualizar</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Informações Básicas -->
            <div class="col-md-8">
                <div class="card card-info">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-info-circle"></i>
                            Informações do Tipo de Fluxo
                        </h3>
                        <div class="btn-group" role="group" aria-label="Ações do Tipo de Fluxo">
                            <a href="{{ route('etapas-fluxo.create', ['tipo_fluxo_id' => $tipoFluxo->id]) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Nova Etapa para este Fluxo
                            </a>
                            <a href="{{ route('tipos-fluxo.etapas', $tipoFluxo) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-stream"></i> Gerenciar Etapas
                            </a>
                            <a href="{{ route('tipos-fluxo.edit', $tipoFluxo) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 col-6 mb-3">
                                <label class="font-weight-bold"><i class="fas fa-hashtag"></i> ID:</label>
                                <div><span class="badge badge-secondary">{{ $tipoFluxo->id }}</span></div>
                            </div>
                            <div class="col-md-2 col-6 mb-3">
                                <label class="font-weight-bold"><i class="fas fa-toggle-on"></i> Status:</label>
                                <div>
                                    @if($tipoFluxo->is_ativo)
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Ativo</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-times"></i> Inativo</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <label class="font-weight-bold"><i class="fas fa-route"></i> Nome:</label>
                                <div><strong>{{ $tipoFluxo->nome }}</strong></div>
                            </div>
                            <div class="col-md-2 col-6 mb-3">
                                <label class="font-weight-bold"><i class="fas fa-code-branch"></i> Versão:</label>
                                <div><span class="badge badge-warning">v{{ $tipoFluxo->versao }}</span></div>
                            </div>
                            <div class="col-md-2 col-6 mb-3">
                                <label class="font-weight-bold"><i class="fas fa-tags"></i> Categoria:</label>
                                <div>
                                    @if($tipoFluxo->categoria)
                                        <span class="badge badge-info">{{ $tipoFluxo->categoria }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($tipoFluxo->descricao)
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label><i class="fas fa-align-left"></i> Descrição:</label>
                                        <p class="form-control-static">{{ $tipoFluxo->descricao }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-plus"></i> Criado em:</label>
                                    <p class="form-control-static">
                                        {{ $tipoFluxo->created_at->format('d/m/Y H:i:s') }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-edit"></i> Atualizado em:</label>
                                    <p class="form-control-static">
                                        {{ $tipoFluxo->updated_at->format('d/m/Y H:i:s') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="col-md-4">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar"></i>
                            Estatísticas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary">
                                <i class="fas fa-tasks"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Ações Associadas</span>
                                <span class="info-box-number">{{ $tipoFluxo->acoes->count() }}</span>
                            </div>
                        </div>

                        <div class="info-box">
                            <span class="info-box-icon bg-warning">
                                <i class="fas fa-project-diagram"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Etapas do Fluxo</span>
                                <span class="info-box-number">{{ $tipoFluxo->etapasFluxo->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ações Rápidas -->
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt"></i>
                            Ações Rápidas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('tipos-fluxo.edit', $tipoFluxo) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i>
                                Editar Tipo de Fluxo
                            </a>
                            <a href="{{ route('tipos-fluxo.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-list"></i>
                                Listar Todos
                            </a>
                            <a href="{{ route('tipos-fluxo.create') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i>
                                Novo Tipo de Fluxo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ações Associadas -->
        @if($tipoFluxo->acoes->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tasks"></i>
                                Ações Associadas ({{ $tipoFluxo->acoes->count() }})
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th><i class="fas fa-hashtag"></i> ID</th>
                                            <th><i class="fas fa-tasks"></i> Descrição</th>
                                            <th><i class="fas fa-dollar-sign"></i> Valor Estimado</th>
                                            <th><i class="fas fa-calendar"></i> Criado em</th>
                                            <th class="text-center"><i class="fas fa-cogs"></i> Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tipoFluxo->acoes as $acao)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-secondary">{{ $acao->id }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $acao->descricao }}</strong>
                                                    @if($acao->localizacao)
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-map-marker-alt"></i>
                                                            {{ Str::limit($acao->localizacao, 50) }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($acao->valor_estimado)
                                                        <span class="text-success font-weight-bold">
                                                            R$ {{ number_format($acao->valor_estimado, 2, ',', '.') }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $acao->created_at->format('d/m/Y') }}
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('acoes.show', $acao) }}" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Visualizar Ação">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($tipoFluxo->etapasFluxo && $tipoFluxo->etapasFluxo->count() > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-stream"></i>
                                Etapas Vinculadas a este Fluxo ({{ $tipoFluxo->etapasFluxo->count() }})
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th><i class="fas fa-hashtag"></i> ID</th>
                                            <th><i class="fas fa-stream"></i> Nome da Etapa</th>
                                            <th><i class="fas fa-cogs"></i> Módulo</th>
                                            <th><i class="fas fa-tags"></i> Grupo de Exigência</th>
                                            <th class="text-center"><i class="fas fa-sort-numeric-up"></i> Ordem</th>
                                            <th class="text-center"><i class="fas fa-toggle-on"></i> Obrigatória</th>
                                            <th class="text-center"><i class="fas fa-cogs"></i> Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tipoFluxo->etapasFluxo as $etapa)
                                            <tr>
                                                <td><span class="badge badge-secondary">{{ $etapa->id }}</span></td>
                                                <td><strong>{{ $etapa->nome_etapa }}</strong></td>
                                                <td>@if($etapa->modulo) <span class="badge badge-primary">{{ $etapa->modulo->nome }}</span> @else <span class="text-muted">-</span> @endif</td>
                                                <td>@if($etapa->grupoExigencia) <span class="badge badge-warning">{{ $etapa->grupoExigencia->nome }}</span> @else <span class="text-muted">-</span> @endif</td>
                                                <td class="text-center">{{ $etapa->ordem_execucao ?? '-' }}</td>
                                                <td class="text-center">@if($etapa->is_obrigatoria) <span class="badge badge-success"><i class="fas fa-check"></i></span> @else <span class="badge badge-danger"><i class="fas fa-times"></i></span> @endif</td>
                                                <td class="text-center">
                                                    <a href="{{ route('etapas-fluxo.show', $etapa) }}" class="btn btn-info btn-sm" title="Visualizar"><i class="fas fa-eye"></i></a>
                                                    <a href="{{ route('etapas-fluxo.edit', $etapa) }}" class="btn btn-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
<style>
    .form-control-static {
        padding-top: 7px;
        padding-bottom: 7px;
        margin-bottom: 0;
        min-height: 34px;
    }
    
    .info-box {
        margin-bottom: 15px;
    }
    
    .d-grid {
        display: grid;
    }
    
    .gap-2 {
        gap: 0.5rem;
    }
    
    .btn-block {
        display: block;
        width: 100%;
        margin-bottom: 10px;
    }
    
    .card-header .btn-group > .btn { margin-left: 0.5rem; }
    .card-header .btn-group > .btn:first-child { margin-left: 0; }
    @media (max-width: 575.98px) {
        .card-header.d-flex { flex-direction: column; align-items: stretch; }
        .card-header .btn-group { margin-top: 0.5rem; justify-content: flex-end; }
    }
</style>
@stop 