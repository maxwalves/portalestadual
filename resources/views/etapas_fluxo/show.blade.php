@extends('adminlte::page')

@section('title', $etapaFluxo->nome_etapa . ' - Detalhes da Etapa')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-eye text-primary"></i>
                    Detalhes da Etapa de Fluxo
                </h1>
                <p class="text-muted mt-1">{{ $etapaFluxo->nome_etapa }}</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('etapas-fluxo.index') }}">Etapas de Fluxo</a></li>
                    <li class="breadcrumb-item active">{{ $etapaFluxo->nome_etapa }}</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Cabeçalho da Etapa -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="mb-1">
                                    <i class="fas fa-stream text-primary mr-2"></i>
                                    {{ $etapaFluxo->nome_etapa }}
                                </h3>
                                <div class="mt-2">
                                    @if($etapaFluxo->tipoFluxo)
                                        <span class="badge badge-primary mr-2">
                                            <i class="fas fa-route mr-1"></i>
                                            {{ $etapaFluxo->tipoFluxo->nome }}
                                        </span>
                                    @endif
                                    @if($etapaFluxo->ordem_execucao)
                                        <span class="badge badge-info mr-2">
                                            <i class="fas fa-sort-numeric-up mr-1"></i>
                                            Ordem: {{ $etapaFluxo->ordem_execucao }}
                                        </span>
                                    @endif
                                    <span class="badge badge-{{ $etapaFluxo->tipo_etapa == 'SEQUENCIAL' ? 'success' : 'warning' }} mr-2">
                                        <i class="fas fa-{{ $etapaFluxo->tipo_etapa == 'SEQUENCIAL' ? 'list-ol' : 'random' }} mr-1"></i>
                                        {{ $etapaFluxo->tipo_etapa }}
                                    </span>
                                    @if($etapaFluxo->is_obrigatoria)
                                        <span class="badge badge-danger mr-2">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Obrigatória
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('etapas-fluxo.edit', $etapaFluxo) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="{{ route('etapas-fluxo.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-list"></i> Listar Todas
                                    </a>
                                    @if($etapaFluxo->tipoFluxo)
                                        <a href="{{ route('tipos-fluxo.etapas', $etapaFluxo->tipoFluxo) }}" class="btn btn-info">
                                            <i class="fas fa-sitemap"></i> Ver Fluxo
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Informações Principais -->
            <div class="col-md-8">
                <!-- Configurações da Etapa -->
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cogs"></i>
                            Configurações da Etapa
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-primary">
                                        <i class="fas fa-puzzle-piece"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Módulo</span>
                                        <span class="info-box-number">
                                            @if($etapaFluxo->modulo)
                                                {{ $etapaFluxo->modulo->nome }}
                                                <small class="d-block text-muted">{{ $etapaFluxo->modulo->tipo }}</small>
                                            @else
                                                <span class="text-muted">Não definido</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-warning">
                                        <i class="fas fa-tags"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Grupo de Exigência</span>
                                        <span class="info-box-number">
                                            @if($etapaFluxo->grupoExigencia)
                                                {{ $etapaFluxo->grupoExigencia->nome }}
                                                @if($etapaFluxo->grupoExigencia->descricao)
                                                    <small class="d-block text-muted">{{ Str::limit($etapaFluxo->grupoExigencia->descricao, 50) }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">Nenhum</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-success">
                                        <i class="fas fa-clock"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Prazo</span>
                                        <span class="info-box-number">
                                            {{ $etapaFluxo->prazo_dias }}
                                            <small class="d-block text-muted">
                                                {{ $etapaFluxo->tipo_prazo == 'UTEIS' ? 'dias úteis' : 'dias corridos' }}
                                            </small>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-{{ $etapaFluxo->permite_pular ? 'success' : 'danger' }}">
                                        <i class="fas fa-{{ $etapaFluxo->permite_pular ? 'fast-forward' : 'ban' }}"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Permite Pular</span>
                                        <span class="info-box-number">
                                            {{ $etapaFluxo->permite_pular ? 'Sim' : 'Não' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-{{ $etapaFluxo->permite_retorno ? 'success' : 'danger' }}">
                                        <i class="fas fa-{{ $etapaFluxo->permite_retorno ? 'undo' : 'ban' }}"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Permite Retorno</span>
                                        <span class="info-box-number">
                                            {{ $etapaFluxo->permite_retorno ? 'Sim' : 'Não' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Organizações Responsáveis -->
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users"></i>
                            Organizações Responsáveis
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <i class="fas fa-user-tie fa-2x"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">Organização Solicitante</h5>
                                                <h6 class="card-subtitle mb-0">
                                                    @if($etapaFluxo->organizacaoSolicitante)
                                                        {{ $etapaFluxo->organizacaoSolicitante->nome }}
                                                        <small class="d-block opacity-75">{{ $etapaFluxo->organizacaoSolicitante->tipo }}</small>
                                                    @else
                                                        <span class="opacity-75">Não definida</span>
                                                    @endif
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <i class="fas fa-users-cog fa-2x"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">Organização Executora</h5>
                                                <h6 class="card-subtitle mb-0">
                                                    @if($etapaFluxo->organizacaoExecutora)
                                                        {{ $etapaFluxo->organizacaoExecutora->nome }}
                                                        <small class="d-block opacity-75">{{ $etapaFluxo->organizacaoExecutora->tipo }}</small>
                                                    @else
                                                        <span class="opacity-75">Não definida</span>
                                                    @endif
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($etapaFluxo->organizacaoSolicitante && $etapaFluxo->organizacaoExecutora)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Fluxo:</strong> 
                                        A <strong>{{ $etapaFluxo->organizacaoSolicitante->nome }}</strong> solicita a ação para 
                                        a <strong>{{ $etapaFluxo->organizacaoExecutora->nome }}</strong> executar.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Descrição Detalhada -->
                @if($etapaFluxo->descricao_customizada)
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-align-left"></i>
                            Descrição Detalhada
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $etapaFluxo->descricao_customizada }}</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar de Informações -->
            <div class="col-md-4">
                <!-- Estatísticas Rápidas -->
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Informações Rápidas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-primary">
                                        <i class="fas fa-hashtag"></i>
                                    </span>
                                    <h5 class="description-header">{{ $etapaFluxo->id }}</h5>
                                    <span class="description-text">ID da Etapa</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="description-block">
                                    <span class="description-percentage text-success">
                                        <i class="fas fa-clock"></i>
                                    </span>
                                    <h5 class="description-header">{{ $etapaFluxo->prazo_dias }}</h5>
                                    <span class="description-text">Dias de Prazo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ações Disponíveis -->
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt"></i>
                            Ações Disponíveis
                        </h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('etapas-fluxo.edit', $etapaFluxo) }}" class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-edit"></i> Editar Etapa
                        </a>
                        @if($etapaFluxo->tipoFluxo)
                            <a href="{{ route('tipos-fluxo.show', $etapaFluxo->tipoFluxo) }}" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-route"></i> Ver Tipo de Fluxo
                            </a>
                            <a href="{{ route('tipos-fluxo.etapas', $etapaFluxo->tipoFluxo) }}" class="btn btn-info btn-block mb-2">
                                <i class="fas fa-sitemap"></i> Gerenciar Fluxo
                            </a>
                        @endif
                        <a href="{{ route('etapas-fluxo.create') }}" class="btn btn-success btn-block mb-2">
                            <i class="fas fa-plus"></i> Nova Etapa
                        </a>
                        <a href="{{ route('etapas-fluxo.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-list"></i> Listar Todas
                        </a>
                    </div>
                </div>

                <!-- Informações do Sistema -->
                <div class="card card-outline card-dark">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            Informações do Sistema
                        </h3>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <div class="mb-2">
                                <strong><i class="fas fa-calendar-plus mr-1"></i> Criado em:</strong><br>
                                {{ $etapaFluxo->created_at ? $etapaFluxo->created_at->format('d/m/Y H:i:s') : '-' }}
                            </div>
                            <div class="mb-2">
                                <strong><i class="fas fa-calendar-edit mr-1"></i> Atualizado em:</strong><br>
                                {{ $etapaFluxo->updated_at ? $etapaFluxo->updated_at->format('d/m/Y H:i:s') : '-' }}
                            </div>
                            @if($etapaFluxo->created_by)
                                <div class="mb-2">
                                    <strong><i class="fas fa-user-plus mr-1"></i> Criado por:</strong><br>
                                    Usuário ID: {{ $etapaFluxo->created_by }}
                                </div>
                            @endif
                            @if($etapaFluxo->updated_by)
                                <div>
                                    <strong><i class="fas fa-user-edit mr-1"></i> Atualizado por:</strong><br>
                                    Usuário ID: {{ $etapaFluxo->updated_by }}
                                </div>
                            @endif
                        </small>
                    </div>
                </div>

                <!-- Configurações JSON (se existir) -->
                @if($etapaFluxo->configuracoes)
                <div class="card card-outline card-dark">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-code"></i>
                            Configurações Específicas
                        </h3>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-2 rounded"><code>{{ json_encode($etapaFluxo->configuracoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .info-box {
        margin-bottom: 15px;
    }
    
    .info-box-content {
        padding: 5px 10px;
    }
    
    .info-box-number {
        font-size: 16px;
        font-weight: 600;
    }
    
    .info-box-text {
        font-size: 12px;
        text-transform: uppercase;
        font-weight: 600;
    }
    
    .description-block {
        padding: 15px 0;
    }
    
    .description-header {
        margin: 0;
        padding: 0;
        font-weight: 600;
        font-size: 18px;
        color: #333;
    }
    
    .description-text {
        font-size: 12px;
        color: #999;
        text-transform: uppercase;
    }
    
    .opacity-75 {
        opacity: 0.75;
    }
    
    .bg-primary .card-subtitle {
        color: rgba(255,255,255,0.9) !important;
    }
    
    .bg-success .card-subtitle {
        color: rgba(255,255,255,0.9) !important;
    }
    
    pre code {
        font-size: 11px;
    }
    
    .border-right {
        border-right: 1px solid #f4f4f4;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Adicionar tooltips aos badges
        $('[data-toggle="tooltip"]').tooltip();
        
        // Destacar informações importantes
        $('.info-box').hover(
            function() {
                $(this).addClass('shadow-sm');
            },
            function() {
                $(this).removeClass('shadow-sm');
            }
        );
    });
</script>
@stop 