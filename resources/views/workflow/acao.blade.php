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

    <!-- Mapa do Fluxo Condicional -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-project-diagram"></i>
                Mapa do Fluxo Condicional
            </h3>
            <div class="card-tools">
                <button class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <!-- Instruções do Fluxograma -->
                    <div class="alert alert-info alert-sm mb-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Mapa do Fluxo:</strong> As setas mostram para onde cada etapa direciona baseado no status escolhido. 
                        Setas <span style="color: #2c3e50; background: #e8f5e8; padding: 2px 6px; border-radius: 4px; font-weight: 600;">destacadas</span> indicam o caminho ativo para o status atual.
                    </div>
                </div>
            </div>
            
            <div class="fluxo-condicional">
                <div class="row">
                    <!-- Coluna Principal - Fluxo das Etapas -->
                    <div class="col-lg-8">
                        @foreach($etapasFluxo as $loop => $etapa)
                            @php
                                $execucao = $execucoes->get($etapa->id);
                                $statusAtual = $execucao ? $execucao->status->codigo : null;
                                $transicoes = $etapa->transicoesOrigem->where('is_ativo', true);
                                $isEtapaAtual = $etapaAtual && $etapaAtual->id === $etapa->id;
                            @endphp
                            
                            <!-- Separador entre etapas -->
                            @if(!$loop->first)
                                <div class="etapa-separador">
                                    <div class="linha-separadora"></div>
                                    <div class="icone-separador">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="etapa-container" data-etapa-id="{{ $etapa->id }}" onclick="mostrarInfoEtapa({{ $etapa->id }})">
                                <!-- Card Principal da Etapa -->
                                <div class="etapa-principal">
                                    <div class="etapa-box {{ $statusAtual ? 'status-' . strtolower($statusAtual) : 'nao-iniciada' }} {{ $isEtapaAtual ? 'etapa-atual' : '' }}">
                                        <div class="etapa-header">
                                            <div class="etapa-numero">
                                                <span class="badge badge-primary">{{ $etapa->ordem_execucao ?? ($loop->iteration) }}</span>
                                            </div>
                                            @if($isEtapaAtual)
                                                <div class="etapa-atual-badge">
                                                    <span class="badge badge-success badge-atual">
                                                        <i class="fas fa-star mr-1"></i> ATUAL
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="etapa-nome">{{ $etapa->nome_etapa }}</div>
                                        
                                        <div class="etapa-organizacoes">
                                            <div class="org-fluxo">
                                                <span class="org-solicitante">{{ Str::limit($etapa->organizacaoSolicitante->nome, 15) }}</span>
                                                <i class="fas fa-arrow-right mx-2"></i>
                                                <span class="org-executora">{{ Str::limit($etapa->organizacaoExecutora->nome, 15) }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="etapa-status-badge">
                                            @if($execucao)
                                                <span class="badge badge-lg badge-{{ $statusAtual === 'APROVADO' ? 'success' : ($statusAtual === 'REPROVADO' ? 'danger' : 'warning') }}">
                                                    {{ $execucao->status->nome }}
                                                </span>
                                            @else
                                                <span class="badge badge-lg badge-secondary">Aguardando</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Ações da Etapa -->
                                        <div class="etapa-acoes">
                                            @php
                                                $podeAcessar = $etapasAcessiveis->get($etapa->id)['pode_acessar'] ?? false;
                                                $podeVerDetalhes = $etapasAcessiveis->get($etapa->id)['pode_ver_detalhes'] ?? false;
                                                $podeVerHistorico = $etapasAcessiveis->get($etapa->id)['pode_ver_historico'] ?? false;
                                            @endphp
                                            
                                            @if($podeVerDetalhes)
                                                <a href="{{ route('workflow.etapa-detalhada', [$acao, $etapa]) }}" 
                                                   class="btn btn-sm {{ $podeAcessar ? 'btn-primary' : 'btn-outline-info' }}">
                                                    <i class="fas fa-{{ $podeAcessar ? 'edit' : 'eye' }}"></i>
                                                    {{ $podeAcessar ? 'Acessar' : 'Visualizar' }}
                                                </a>
                                            @endif
                                            
                                            @if($execucao && $podeVerHistorico)
                                                <button type="button" class="btn btn-sm btn-outline-secondary historico-btn" 
                                                        data-execucao-id="{{ $execucao->id }}"
                                                        onclick="event.stopPropagation(); event.preventDefault(); mostrarHistorico({{ $execucao->id }}); return false;">
                                                    <i class="fas fa-history"></i>
                                                    Histórico
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cards de Opções com Recuo -->
                                @if($transicoes->count() > 0)
                                    <div class="opcoes-transicao">
                                        <div class="opcoes-titulo">
                                            <small class="text-muted">
                                                <i class="fas fa-route"></i> 
                                                Próximos passos baseados no status:
                                            </small>
                                        </div>
                                        
                                        @foreach($transicoes as $transicao)
                                            @php
                                                $etapaDestino = $transicao->etapaDestino;
                                                $statusCondicao = $transicao->statusCondicao;
                                                $execucaoDestino = $execucoes->get($etapaDestino->id);
                                                $isAtivo = $statusAtual === $statusCondicao->codigo;
                                            @endphp
                                            
                                            <div class="opcao-card {{ $isAtivo ? 'opcao-ativa' : '' }}">
                                                <div class="opcao-condicao">
                                                    <span class="condicao-label">Se for:</span>
                                                    <span class="badge badge-{{ $statusCondicao->codigo === 'APROVADO' ? 'success' : ($statusCondicao->codigo === 'REPROVADO' ? 'danger' : 'warning') }}">
                                                        {{ $statusCondicao->nome }}
                                                    </span>
                                                    @if($isAtivo)
                                                        <span class="badge badge-info badge-sm ml-2">
                                                            <i class="fas fa-arrow-right"></i> Ativo
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <div class="opcao-destino">
                                                    <div class="destino-info">
                                                        <i class="fas fa-long-arrow-alt-right text-muted mr-2"></i>
                                                        <span class="destino-nome">{{ $etapaDestino->nome_etapa }}</span>
                                                        <small class="destino-org text-muted">({{ $etapaDestino->organizacaoExecutora->nome }})</small>
                                                    </div>
                                                    
                                                    @if($execucaoDestino)
                                                        <span class="badge badge-xs badge-{{ $execucaoDestino->status->codigo === 'APROVADO' ? 'success' : 'warning' }}">
                                                            {{ $execucaoDestino->status->nome }}
                                                        </span>
                                                    @else
                                                        <span class="badge badge-xs badge-light">Não iniciada</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="opcoes-transicao">
                                        <div class="opcao-card opcao-final">
                                            <div class="opcao-condicao">
                                                <i class="fas fa-flag-checkered text-success mr-2"></i>
                                                <span class="text-muted">Etapa final do fluxo</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Caixa Lateral Direita - Informações Úteis -->
                    <div class="col-lg-4">
                        <div class="info-lateral">
                            <div class="info-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle text-info"></i>
                                    Informações da Etapa
                                </h6>
                            </div>
                            
                            <div class="info-content" id="infoLateralContent">
                                <!-- Conteúdo será atualizado via JavaScript -->
                                <div class="info-placeholder">
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-hand-pointer fa-2x mb-2"></i>
                                        <p>Clique em uma etapa para ver detalhes</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Legenda -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="legenda-fluxo">
                        <h6 class="text-muted">Legenda:</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="legenda-item">
                                    <span class="badge badge-primary"><i class="fas fa-star"></i> ATUAL</span>
                                    <small>Etapa em execução</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="legenda-item">
                                    <i class="fas fa-arrow-right text-success"></i>
                                    <small>Caminho ativo</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="legenda-item">
                                    <span class="badge badge-success">Aprovado</span>
                                    <small>Etapa concluída</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="legenda-item">
                                    <span class="badge badge-secondary">Aguardando</span>
                                    <small>Não iniciada</small>
                                </div>
                            </div>
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
        /* ===== LAYOUT COMPACTO PARA ETAPAS DO WORKFLOW ===== */
        
        .compact-timeline-item {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-radius: 8px;
            border: 1px solid #e3e6f0;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
        }
        
        .compact-timeline-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-color: #5a6c7d;
        }
        
        .etapa-card {
            margin-bottom: 1rem;
        }
        
        /* Header da Etapa */
        .etapa-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1rem;
            border-radius: 8px 8px 0 0;
        }
        
        .etapa-title {
            flex: 1;
        }
        
        .etapa-link {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            transition: color 0.3s ease;
        }
        
        .etapa-link:hover {
            color: #3498db;
            text-decoration: none;
        }
        
        .etapa-status .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        /* Body da Etapa */
        .etapa-body {
            padding: 0.75rem 1rem;
            background: #ffffff;
            border-radius: 0 0 8px 8px;
        }
        
        /* Informações Compactas */
        .info-compacta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .info-item i {
            width: 16px;
            margin-right: 0.25rem;
            font-size: 0.8rem;
        }
        
        .info-item strong {
            margin-right: 0.25rem;
            color: #495057;
        }
        
        /* Ações da Etapa */
        .etapa-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            justify-content: flex-end;
            align-items: center;
        }
        
        .etapa-actions .btn {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        
        /* Progress dos Documentos */
        .progress-documentos {
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid #f1f3f4;
        }
        
        .progress-documentos .progress {
            border-radius: 2px;
            background-color: #f1f3f4;
        }
        
        .progress-documentos .progress-bar {
            border-radius: 2px;
        }
        
        /* Timeline Icons - Mais Compactos */
        .timeline > .timeline-item > .fas,
        .timeline > .timeline-item > .far,
        .timeline > .timeline-item > .fab {
            width: 30px;
            height: 30px;
            font-size: 14px;
            line-height: 30px;
            border-radius: 50%;
            text-align: center;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .timeline::before {
            background-color: #e3e6f0;
            width: 2px;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .info-compacta {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .etapa-actions {
                justify-content: center;
                margin-top: 0.5rem;
            }
            
            .col-md-4.text-right {
                text-align: center !important;
            }
            
            .etapa-header .d-flex {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .etapa-status {
                text-align: center;
            }
        }
        
        /* Estados das Etapas */
        .etapa-card:has(.bg-success) .compact-timeline-item {
            border-left: 4px solid #28a745;
        }
        
        .etapa-card:has(.bg-primary) .compact-timeline-item {
            border-left: 4px solid #007bff;
            animation: pulse-border 2s infinite;
        }
        
        .etapa-card:has(.bg-info) .compact-timeline-item {
            border-left: 4px solid #17a2b8;
        }
        
        .etapa-card:has(.bg-secondary) .compact-timeline-item {
            border-left: 4px solid #6c757d;
        }
        
        @keyframes pulse-border {
            0% {
                border-left-color: #007bff;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            }
            50% {
                border-left-color: #0056b3;
                box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
            }
            100% {
                border-left-color: #007bff;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            }
        }
        
        /* ===== ANIMAÇÃO SUAVE DA ETAPA ATUAL ===== */
        @keyframes subtle-glow {
            0%, 100% {
                box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
            }
            50% {
                box-shadow: 0 3px 12px rgba(40, 167, 69, 0.5);
            }
        }
        
        /* Badges personalizados */
        .badge {
            font-weight: 500;
        }
        
        .badge-atual {
            font-weight: 700;
            font-size: 0.85rem !important;
            padding: 0.5rem 0.8rem !important;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
            animation: subtle-glow 3s ease-in-out infinite;
        }
        
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
            background-color: #17a2b8 !important;
        }
        
        .badge-secondary {
            background-color: #6c757d !important;
        }
        
        /* Botões compactos */
        .btn-sm {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            line-height: 1.5;
        }
        
        /* Alert compacto */
        .alert-sm {
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        /* Melhoria na timeline geral */
        .timeline {
            margin-bottom: 0;
        }
        
        .timeline-item {
            margin-bottom: 1rem;
        }
        
        /* Hover effects */
        .etapa-actions .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        /* Loading states */
        .btn:disabled {
            opacity: 0.6;
            transform: none !important;
        }
        
        /* Text utilities */
        .text-muted {
            color: #6c757d !important;
        }
        
        small.text-muted {
            font-size: 0.8rem;
        }
        
        /* ===== RESPONSIVIDADE PARA O MAPEAMENTO ===== */
        @media (max-width: 768px) {
            .etapa-box {
                max-width: 100%;
                margin: 0 0 1rem;
            }
            
            .etapa-info .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.2rem;
            }
            
            .info-label {
                min-width: auto;
            }
            
            .etapa-actions {
                flex-direction: column;
            }
            
            .fluxo-condicional {
                padding: 0.5rem;
            }
            
            .transicoes-etapa {
                grid-template-columns: 1fr;
            }
        }
        
        /* ===== ESTILOS DO NOVO LAYOUT FLUXOGRAMA ===== */
        .fluxo-condicional {
            padding: 1rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
        }
        
        /* ===== SEPARADORES ENTRE ETAPAS ===== */
        .etapa-separador {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1.5rem 0;
            position: relative;
        }
        
        .linha-separadora {
            height: 2px;
            background: linear-gradient(to right, transparent 0%, #dee2e6 20%, #dee2e6 80%, transparent 100%);
            width: 100%;
            position: absolute;
        }
        
        .icone-separador {
            background: #ffffff;
            border: 2px solid #dee2e6;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
            color: #6c757d;
        }
        
        /* ===== CONTAINER DA ETAPA ===== */
        .etapa-container {
            margin-bottom: 2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .etapa-container:hover {
            transform: translateX(5px);
        }
        
        .etapa-container.etapa-selecionada {
            transform: translateX(8px);
        }
        
        .etapa-container.etapa-selecionada .etapa-box {
            border-color: #007bff;
            box-shadow: 0 4px 16px rgba(0, 123, 255, 0.3);
            background: linear-gradient(135deg, #ffffff 0%, #f0f8ff 100%);
        }
        

        
        /* ===== CARD PRINCIPAL DA ETAPA ===== */
        .etapa-principal {
            margin-bottom: 1rem;
        }
        
        .etapa-box {
            background: #ffffff;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .etapa-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            border-color: #007bff;
        }
        
        .etapa-box.etapa-atual {
            border-color: #28a745;
            border-width: 3px;
            background: linear-gradient(135deg, #f8fff9 0%, #f0f9f0 100%);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
            transform: scale(1.02);
        }
        
        .etapa-box.etapa-atual::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, #28a745, #20c997);
            border-radius: 0 2px 2px 0;
        }
        
        .etapa-box.status-aprovado {
            border-color: #28a745;
            background: linear-gradient(135deg, #ffffff 0%, #f8fff9 100%);
        }
        
        .etapa-box.status-reprovado {
            border-color: #dc3545;
            background: linear-gradient(135deg, #ffffff 0%, #fff8f9 100%);
        }
        
        .etapa-box.status-em_analise,
        .etapa-box.status-pendente {
            border-color: #ffc107;
            background: linear-gradient(135deg, #ffffff 0%, #fffef8 100%);
        }
        
        .etapa-box.nao-iniciada {
            border-color: #6c757d;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            opacity: 0.8;
        }
        
        /* ===== ELEMENTOS DO CARD PRINCIPAL ===== */
        .etapa-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .etapa-numero .badge {
            font-size: 0.9rem;
            padding: 0.5rem 0.7rem;
            font-weight: 600;
        }
        
        .etapa-nome {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.75rem;
            text-align: center;
            line-height: 1.3;
        }
        
        .etapa-organizacoes {
            margin-bottom: 0.75rem;
            text-align: center;
        }
        
        .org-fluxo {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .org-solicitante, .org-executora {
            font-weight: 600;
        }
        
        .etapa-status-badge {
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .badge-lg {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            font-weight: 600;
        }
        
        .etapa-acoes {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }
        
        .etapa-acoes .btn {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-weight: 500;
        }
        
        /* ===== SEÇÃO DE OPÇÕES DE TRANSIÇÃO ===== */
        .opcoes-transicao {
            margin-left: 2rem;
            padding-left: 1rem;
            border-left: 3px solid #e9ecef;
            position: relative;
        }
        
        .opcoes-titulo {
            margin-bottom: 0.75rem;
            font-weight: 600;
        }
        
        .opcao-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .opcao-card:hover {
            background: #ffffff;
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        }
        
        .opcao-card.opcao-ativa {
            background: linear-gradient(135deg, #e8f5e8 0%, #f0f9f0 100%);
            border-color: #28a745;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
        }
        
        .opcao-card.opcao-ativa::before {
            content: "";
            position: absolute;
            left: -4px;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: #28a745;
            border-radius: 50%;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.3);
        }
        
        .opcao-card.opcao-final {
            background: linear-gradient(135deg, #fff3cd 0%, #fef7e0 100%);
            border-color: #ffc107;
        }
        
        .opcao-condicao {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }
        
        .condicao-label {
            font-weight: 600;
            color: #6c757d;
        }
        
        .opcao-destino {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.85rem;
        }
        
        .destino-info {
            display: flex;
            align-items: center;
            flex: 1;
        }
        
        .destino-nome {
            font-weight: 600;
            color: #495057;
            margin-right: 0.5rem;
        }
        
        .destino-org {
            font-size: 0.8rem;
        }
        
        /* ===== CAIXA LATERAL DIREITA ===== */
        .info-lateral {
            background: #ffffff;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }
        
        .info-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px 10px 0 0;
            margin: -2px -2px 0 -2px;
        }
        
        .info-content {
            padding: 1rem;
            min-height: 200px;
        }
        
        .info-placeholder {
            text-align: center;
            color: #6c757d;
        }
        
        .info-etapa-detalhada {
            display: none;
        }
        
        .info-etapa-detalhada.active {
            display: block;
        }
        
        .info-item-lateral {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .info-item-lateral i {
            width: 20px;
            margin-right: 0.75rem;
            color: #007bff;
        }
        
        .info-label-lateral {
            font-weight: 600;
            color: #495057;
            margin-right: 0.5rem;
        }
        
        .info-value-lateral {
            color: #6c757d;
        }
        
        .prazo-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .prazo-info.vencido {
            background: linear-gradient(135deg, #ffebee 0%, #fce4ec 100%);
            border-color: #f44336;
        }
        
        .docs-info {
            background: linear-gradient(135deg, #fff3e0 0%, #fef7e0 100%);
            border: 1px solid #ff9800;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
        }
        
        /* ===== RESPONSIVIDADE ===== */
        @media (max-width: 991px) {
            .opcoes-transicao {
                margin-left: 1rem;
                padding-left: 0.5rem;
            }
            
            .etapa-container:hover {
                transform: none;
            }
            
            .info-lateral {
                position: static;
                margin-top: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .fluxo-condicional {
                padding: 0.5rem;
            }
            
            .etapa-box {
                padding: 1rem;
            }
            
            .etapa-nome {
                font-size: 1rem;
            }
            
            .org-fluxo {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .org-fluxo i {
                transform: rotate(90deg);
            }
            
            .etapa-acoes {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .opcoes-transicao {
                margin-left: 0.5rem;
            }
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
            console.log('Carregando histórico para execução ID:', execucaoId);
            $('#historicoContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted">Carregando histórico...</p></div>');
            
            const url = `/workflow/execucao/${execucaoId}/historico`;
            console.log('URL da requisição:', url);
            
            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html, */*; q=0.01'
                },
                success: function(response) {
                    console.log('=== RESPOSTA AJAX ===');
                    console.log('Resposta recebida:', response);
                    console.log('Tipo de resposta:', typeof response);
                    console.log('Tamanho da resposta:', response.length);
                    console.log('Primeiros 200 caracteres:', response.substring(0, 200));
                    
                    if (response && response.trim().length > 0) {
                        console.log('Inserindo conteúdo no elemento #historicoContent...');
                        const $container = $('#historicoContent');
                        console.log('Container encontrado:', $container.length > 0);
                        
                        $container.html(response);
                        console.log('Conteúdo inserido. HTML atual:', $container.html().substring(0, 100));
                        console.log('Conteúdo carregado com sucesso');
                    } else {
                        console.warn('Resposta vazia recebida');
                        $('#historicoContent').html(`
                            <div class="text-center py-4">
                                <i class="fas fa-info-circle fa-2x text-info mb-3"></i>
                                <h5 class="text-muted">Resposta vazia</h5>
                                <p class="text-muted">O servidor retornou uma resposta vazia. Tente novamente.</p>
                                <button class="btn btn-sm btn-outline-primary" onclick="carregarHistorico(${execucaoId})">
                                    <i class="fas fa-redo"></i> Tentar novamente
                                </button>
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro ao carregar histórico:', {xhr, status, error});
                    console.error('Status HTTP:', xhr.status);
                    console.error('Response Text:', xhr.responseText);
                    
                    let errorMessage = 'Erro ao carregar histórico';
                    
                    if (xhr.status === 403) {
                        errorMessage = 'Acesso negado ao histórico desta etapa';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Histórico não encontrado';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Erro interno do servidor';
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    
                    $('#historicoContent').html(`
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                            <h5 class="text-muted">Oops!</h5>
                            <p class="text-muted">${errorMessage}</p>
                            <small class="text-muted">Status: ${xhr.status} | ${error}</small>
                            <br>
                            <button class="btn btn-sm btn-outline-primary mt-2" onclick="carregarHistorico(${execucaoId})">
                                <i class="fas fa-redo"></i> Tentar novamente
                            </button>
                        </div>
                    `);
                }
            });
        }

        // Função específica para mostrar histórico do fluxograma
        function mostrarHistorico(execucaoId) {
            console.log('Mostrar histórico da execução:', execucaoId);
            
            // Prevenir comportamento padrão
            try {
                event.stopPropagation();
                event.preventDefault();
            } catch(e) {
                console.log('Event handling not available');
            }
            
            // Garantir que o modal seja exibido
            setTimeout(() => {
                $('#modalHistoricoEtapa').modal('show');
                carregarHistorico(execucaoId);
            }, 50);
            
            return false;
        }

        // Função para mostrar informações da etapa na caixa lateral
        function mostrarInfoEtapa(etapaId) {
            console.log('Mostrar info da etapa:', etapaId);
            
            // Buscar dados da etapa via PHP embutido
            @php
                $etapasJson = $etapasFluxo->map(function($etapa) use ($execucoes) {
                    $execucao = $execucoes->get($etapa->id);
                    return [
                        'id' => $etapa->id,
                        'nome' => $etapa->nome_etapa,
                        'prazo_dias' => $etapa->prazo_dias,
                        'tipo_prazo' => $etapa->tipo_prazo,
                        'organizacao_solicitante' => $etapa->organizacaoSolicitante->nome,
                        'organizacao_executora' => $etapa->organizacaoExecutora->nome,
                        'docs_obrigatorios' => $etapa->grupoExigencia ? $etapa->grupoExigencia->templatesDocumento->where('is_obrigatorio', true)->count() : 0,
                        'docs_total' => $etapa->grupoExigencia ? $etapa->grupoExigencia->templatesDocumento->count() : 0,
                        'data_inicio' => $execucao ? $execucao->data_inicio->format('d/m/Y') : null,
                        'data_prazo' => $execucao && $execucao->data_prazo ? $execucao->data_prazo->format('d/m/Y') : null,
                        'status_nome' => $execucao ? $execucao->status->nome : 'Não iniciada',
                        'status_codigo' => $execucao ? $execucao->status->codigo : 'NAO_INICIADA',
                        'dias_restantes' => $execucao && $execucao->data_prazo ? intval(now()->diffInDays($execucao->data_prazo, false)) : null,
                        'prazo_vencido' => $execucao && $execucao->data_prazo ? $execucao->data_prazo->isPast() : false,
                    ];
                })->keyBy('id');
            @endphp
            
            const etapasData = @json($etapasJson);
            const etapa = etapasData[etapaId];
            
            if (!etapa) {
                console.error('Etapa não encontrada:', etapaId);
                return;
            }
            
            // Remover destaque de todas as etapas
            $('.etapa-container').removeClass('etapa-selecionada');
            
            // Destacar etapa selecionada
            $(`.etapa-container[data-etapa-id="${etapaId}"]`).addClass('etapa-selecionada');
            
            // Criar HTML das informações
            let infoHtml = `
                <div class="info-etapa-detalhada active">
                    <h6 class="mb-3 text-primary">${etapa.nome}</h6>
                    
                    <div class="info-item-lateral">
                        <i class="fas fa-users"></i>
                        <div>
                            <div class="info-label-lateral">Organizações:</div>
                            <div class="info-value-lateral">
                                ${etapa.organizacao_solicitante} → ${etapa.organizacao_executora}
                            </div>
                        </div>
                    </div>
                    
                    <div class="prazo-info ${etapa.prazo_vencido ? 'vencido' : ''}">
                        <div class="info-item-lateral">
                            <i class="fas fa-clock"></i>
                            <div>
                                <div class="info-label-lateral">Prazo:</div>
                                <div class="info-value-lateral">
                                    ${etapa.prazo_dias} ${etapa.tipo_prazo === 'UTEIS' ? 'dias úteis' : 'dias corridos'}
                                </div>
                            </div>
                        </div>
                        
                        ${etapa.data_prazo ? `
                            <div class="info-item-lateral">
                                <i class="fas fa-calendar-alt"></i>
                                <div>
                                    <div class="info-label-lateral">${etapa.prazo_vencido ? 'Venceu em:' : 'Vence em:'}</div>
                                    <div class="info-value-lateral">
                                        ${etapa.data_prazo}
                                                                                 ${etapa.dias_restantes !== null ? 
                                             (etapa.prazo_vencido ? 
                                                 `(${Math.abs(Math.floor(etapa.dias_restantes))} dias atrás)` : 
                                                 `(${Math.floor(etapa.dias_restantes)} dias restantes)`
                                             ) : ''
                                         }
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                    
                    ${etapa.docs_total > 0 ? `
                        <div class="docs-info">
                            <div class="info-item-lateral">
                                <i class="fas fa-file-alt"></i>
                                <div>
                                    <div class="info-label-lateral">Documentos:</div>
                                    <div class="info-value-lateral">
                                        ${etapa.docs_obrigatorios} obrigatórios / ${etapa.docs_total} total
                                    </div>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                    
                    <div class="info-item-lateral">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <div class="info-label-lateral">Status:</div>
                            <div class="info-value-lateral">
                                <span class="badge badge-${etapa.status_codigo === 'APROVADO' ? 'success' : (etapa.status_codigo === 'REPROVADO' ? 'danger' : 'warning')}">
                                    ${etapa.status_nome}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    ${etapa.data_inicio ? `
                        <div class="info-item-lateral">
                            <i class="fas fa-calendar-check"></i>
                            <div>
                                <div class="info-label-lateral">Iniciada em:</div>
                                <div class="info-value-lateral">${etapa.data_inicio}</div>
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
            
            // Atualizar conteúdo da caixa lateral
            $('#infoLateralContent').html(infoHtml);
        }

        // Event listener adicional para botões de histórico
        $(document).ready(function() {
            // Interceptar cliques nos botões de histórico
            $(document).on('click', '.historico-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const execucaoId = $(this).data('execucao-id');
                console.log('Clique interceptado no botão histórico, execução ID:', execucaoId);
                
                mostrarHistorico(execucaoId);
                return false;
            });

            // Garantir que o modal seja configurado corretamente
            $('#modalHistoricoEtapa').on('show.bs.modal', function() {
                console.log('Modal de histórico sendo exibido');
            });

            $('#modalHistoricoEtapa').on('shown.bs.modal', function() {
                console.log('Modal de histórico exibido com sucesso');
            });
        });
    </script>
@stop 