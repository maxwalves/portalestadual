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
                    <strong>Organização:</strong> {{ $acao->demanda->termoAdesao->organizacao->nome }}
                </div>
                <div class="col-md-6">
                    <strong>Valor Estimado:</strong> R$ {{ number_format($acao->valor_estimado ?? 0, 2, ',', '.') }}<br>
                    <strong>Execução:</strong> {{ number_format($acao->percentual_execucao ?? 0, 1) }}%
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <strong>Status da Ação:</strong>
                    @php
                        $statusAcao = $acao->status ?? 'PLANEJAMENTO';
                        $badgeClasses = [
                            'PLANEJAMENTO' => 'badge-warning',
                            'EM_EXECUCAO' => 'badge-primary', 
                            'PARALISADA' => 'badge-danger',
                            'CONCLUIDA' => 'badge-success',
                            'CANCELADA' => 'badge-dark',
                            'FINALIZADO' => 'badge-success'
                        ];
                        $statusLabels = [
                            'PLANEJAMENTO' => 'Planejamento',
                            'EM_EXECUCAO' => 'Em Execução',
                            'PARALISADA' => 'Paralisada',
                            'CONCLUIDA' => 'Concluída',
                            'CANCELADA' => 'Cancelada',
                            'FINALIZADO' => 'Finalizado'
                        ];
                        $badgeClass = $badgeClasses[$statusAcao] ?? 'badge-secondary';
                        $statusLabel = $statusLabels[$statusAcao] ?? $statusAcao;
                    @endphp
                    <span class="badge {{ $badgeClass }} badge-lg ml-2">
                        <i class="fas fa-{{ $statusAcao === 'CONCLUIDA' || $statusAcao === 'FINALIZADO' ? 'check-circle' : ($statusAcao === 'EM_EXECUCAO' ? 'play-circle' : ($statusAcao === 'PARALISADA' ? 'pause-circle' : ($statusAcao === 'CANCELADA' ? 'times-circle' : 'clock'))) }}"></i>
                        {{ $statusLabel }}
                    </span>
                    @if($acao->is_finalizado)
                        <span class="badge badge-info badge-sm ml-2">
                            <i class="fas fa-flag-checkered"></i>
                            Projeto Finalizado
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Mapa do Fluxo Condicional -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-project-diagram"></i>
                Mapa do Fluxo da Ação
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
                                $isEtapaAtual = !$acao->is_finalizado && $etapaAtual && $etapaAtual->id === $etapa->id;
                                
                                // LÓGICA CORRIGIDA: Determinar se a etapa foi executada ou pulada
                                $isEtapaExecutada = ($statusAtual !== null && $statusAtual !== 'NAO_APLICAVEL');
                                $isEtapaPulada = false;
                                
                                // Se tem status NAO_APLICAVEL, é considerada pulada
                                if ($statusAtual === 'NAO_APLICAVEL') {
                                    $isEtapaPulada = true;
                                }
                                
                                // DEBUG: Adicionar informações temporárias
                                $debugInfo = [
                                    'etapa_id' => $etapa->id,
                                    'etapa_nome' => $etapa->nome_etapa,
                                    'ordem_execucao' => $etapa->ordem_execucao,
                                    'tem_execucao' => $statusAtual !== null,
                                    'status_atual' => $statusAtual,
                                    'etapa_atual_id' => $etapaAtual ? $etapaAtual->id : null,
                                    'etapa_atual_ordem' => $etapaAtual ? $etapaAtual->ordem_execucao : null,
                                    'projeto_finalizado' => $acao->is_finalizado,
                                    'is_executada' => $isEtapaExecutada
                                ];
                                
                                // Se não foi executada OU tem status NAO_APLICAVEL, verificar se foi pulada
                                if (!$isEtapaExecutada || $statusAtual === 'NAO_APLICAVEL') {
                                    if ($statusAtual === 'NAO_APLICAVEL') {
                                        $isEtapaPulada = true;
                                        $debugInfo['logica_aplicada'] = 'status_nao_aplicavel';
                                    } elseif ($acao->is_finalizado) {
                                        $isEtapaPulada = true;
                                        $debugInfo['logica_aplicada'] = 'projeto_finalizado';
                                    } elseif ($etapaAtual && !$acao->is_finalizado) {
                                        // Se a etapa atual tem ordem maior que esta etapa, foi pulada
                                        $isEtapaPulada = ($etapaAtual->ordem_execucao > $etapa->ordem_execucao);
                                        $debugInfo['logica_aplicada'] = 'durante_processo';
                                        $debugInfo['condicao'] = $etapaAtual->ordem_execucao . ' > ' . $etapa->ordem_execucao;
                                    } else {
                                        $debugInfo['logica_aplicada'] = 'nenhuma';
                                    }
                                } else {
                                    $debugInfo['logica_aplicada'] = 'etapa_executada';
                                }
                                
                                $debugInfo['is_pulada'] = $isEtapaPulada;
                                
                            @endphp
                            
                            <!-- Separador compacto entre etapas -->
                            @if(!$loop->first)
                                <div class="etapa-separador-compacto">
                                    <div class="linha-separadora-compacta"></div>
                                    <div class="icone-separador-compacto">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- DEBUG ETAPA: {{ json_encode($debugInfo) }} -->
                            <div class="etapa-container" data-etapa-id="{{ $etapa->id }}" onclick="mostrarInfoEtapa({{ $etapa->id }})">
                                <!-- Card Principal da Etapa - VERSÃO COMPACTA -->
                                <div class="etapa-compacta">
                                    @php
                                        $classes = ['card', 'etapa-card-compacta'];
                                        
                                        if ($isEtapaPulada) {
                                            $classes[] = 'etapa-pulada';
                                        } elseif ($isEtapaExecutada) {
                                            $classes[] = 'etapa-executada';
                                        } elseif ($isEtapaAtual) {
                                            $classes[] = 'etapa-atual';
                                        } else {
                                            $classes[] = 'nao-iniciada';
                                        }
                                        
                                        if ($statusAtual) {
                                            $classes[] = 'status-' . strtolower($statusAtual);
                                        }
                                    @endphp
                                    <div class="{{ implode(' ', $classes) }}">
                                        <div class="card-body p-3">
                                            <div class="row align-items-center">
                                                <!-- Número e Status -->
                                                <div class="col-2 text-center">
                                                    <div class="etapa-numero-compacto">
                                                        <span class="badge badge-circle badge-primary">{{ $etapa->ordem_execucao ?? ($loop->iteration) }}</span>
                                                    </div>
                                                    @if($isEtapaAtual)
                                                        <div class="mt-1">
                                                            <span class="badge badge-success badge-sm">
                                                                <i class="fas fa-star"></i> ATUAL
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <!-- Informações Principais -->
                                                <div class="col-7">
                                                    <div class="etapa-info-compacta">
                                                        <h6 class="etapa-nome-compacta mb-1">{{ $etapa->nome_etapa }}</h6>
                                                        <div class="org-fluxo-compacto text-muted small">
                                                            <span class="org-solicitante">{{ Str::limit($etapa->organizacaoSolicitante->nome, 12) }}</span>
                                                            <i class="fas fa-arrow-right mx-1"></i>
                                                            <span class="org-executora">{{ Str::limit($etapa->organizacaoExecutora->nome, 12) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Status e Ações -->
                                                <div class="col-3 text-right">
                                                    <div class="etapa-status-compacto mb-2">
                                                        @if($execucao)
                                                            <span class="badge badge-{{ $statusAtual === 'APROVADO' ? 'success' : ($statusAtual === 'REPROVADO' ? 'danger' : 'warning') }}">
                                                                {{ $execucao->status->nome }}
                                                            </span>
                                                        @elseif($isEtapaPulada)
                                                            <span class="badge badge-secondary">Não Aplicável</span>
                                                        @else
                                                            <span class="badge badge-secondary">Aguardando</span>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Ações Compactas -->
                                                    <div class="etapa-acoes-compactas">
                                                        @php
                                                            $podeAcessar = $etapasAcessiveis->get($etapa->id)['pode_acessar'] ?? false;
                                                            $podeVerDetalhes = $etapasAcessiveis->get($etapa->id)['pode_ver_detalhes'] ?? false;
                                                            $podeVerHistorico = $etapasAcessiveis->get($etapa->id)['pode_ver_historico'] ?? false;
                                                        @endphp
                                                        
                                                        <div class="btn-group-sm">
                                                            @if($podeVerDetalhes)
                                                                <a href="{{ route('workflow.etapa-detalhada', [$acao, $etapa]) }}" 
                                                                   class="btn btn-xs {{ $podeAcessar && !$acao->is_finalizado ? 'btn-primary' : 'btn-outline-info' }}"
                                                                   title="{{ $podeAcessar && !$acao->is_finalizado ? 'Acessar Etapa' : 'Visualizar Etapa' }}">
                                                                    <i class="fas fa-{{ $podeAcessar && !$acao->is_finalizado ? 'edit' : 'eye' }}"></i>
                                                                </a>
                                                            @endif
                                                            
                                                            @if($execucao && $podeVerHistorico)
                                                                <button type="button" class="btn btn-xs btn-outline-secondary historico-btn" 
                                                                        data-execucao-id="{{ $execucao->id }}"
                                                                        title="Ver Histórico"
                                                                        onclick="event.stopPropagation(); event.preventDefault(); mostrarHistorico({{ $execucao->id }}); return false;">
                                                                    <i class="fas fa-history"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cards de Opções com Recuo -->
                                @if($transicoes->count() > 0)
                                    <div class="opcoes-transicao">
                                        <!-- Botão para expandir/colapsar opções -->
                                        <div class="opcoes-toggle" onclick="toggleOpcoes({{ $etapa->id }})">
                                            <div class="opcoes-titulo-toggle">
                                                <small class="text-muted">
                                                    <i class="fas fa-route"></i> 
                                                    Próximos passos ({{ $transicoes->count() }} {{ $transicoes->count() > 1 ? 'opções' : 'opção' }})
                                                </small>
                                                <div class="opcoes-toggle-area">
                                                    <small class="toggle-hint text-muted">clique</small>
                                                    <i class="fas fa-chevron-down opcoes-arrow" id="arrow-{{ $etapa->id }}"></i>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Container das opções (inicialmente oculto) -->
                                        <div class="opcoes-container" id="opcoes-{{ $etapa->id }}" style="display: none;">
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
                                    </div>
                                @else
                                    @php
                                        // CORREÇÃO DO BUG: Verificar se é realmente a última etapa baseado na ordem_execucao
                                        $ultimaEtapaDoFluxo = $etapasFluxo->sortByDesc('ordem_execucao')->first();
                                        $isRealmenteUltimaEtapa = $ultimaEtapaDoFluxo && $ultimaEtapaDoFluxo->id === $etapa->id;
                                        $podeAcessarEtapa = $etapasAcessiveis->get($etapa->id)['pode_acessar'] ?? false;
                                    @endphp
                                    
                                    @if($isRealmenteUltimaEtapa)
                                        <div class="opcoes-transicao">
                                            <div class="opcao-card opcao-final">
                                                <div class="opcao-condicao">
                                                    <i class="fas fa-flag-checkered text-success mr-2"></i>
                                                    <span class="text-muted">Etapa final do fluxo</span>
                                                </div>
                                                
                                                @if(!$acao->is_finalizado && $podeAcessarEtapa && $statusAtual === 'APROVADO')
                                                    <div class="mt-3">
                                                        <button type="button" class="btn btn-success btn-sm btn-block" 
                                                                onclick="finalizarProjetoCompleto({{ $acao->id }})"
                                                                title="Finalizar projeto independente de outras etapas">
                                                            <i class="fas fa-flag-checkered mr-2"></i>
                                                            Finalizar Projeto
                                                        </button>
                                                        <small class="text-muted d-block mt-1 text-center">
                                                            <i class="fas fa-info-circle mr-1"></i>
                                                            Finaliza o projeto mesmo com etapas pendentes
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="opcoes-transicao">
                                            <div class="opcao-card opcao-sem-transicoes">
                                                <div class="opcao-condicao">
                                                    <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                                                    <span class="text-muted">Transições não configuradas</span>
                                                    <small class="d-block text-muted mt-1">
                                                        Esta etapa não possui transições configuradas. Configure as transições na gestão do fluxo.
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
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
                        <h6 class="text-muted mb-3">Legenda:</h6>
                        <div class="row">
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-2">
                                <div class="legenda-item">
                                    <span class="badge badge-success"><i class="fas fa-star"></i> ATUAL</span>
                                    <small>Etapa ativa - pode editar</small>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-2">
                                <div class="legenda-item">
                                    <span class="badge badge-success">Aprovado</span>
                                    <small>Etapa concluída</small>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-2">
                                <div class="legenda-item">
                                    <div style="border: 2px solid #28a745; background: #f8fff9; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; position: relative;">
                                        <div style="position: absolute; left: 0; top: 0; width: 4px; height: 100%; background: #28a745; border-radius: 0 2px 2px 0;"></div>
                                        <span style="margin-left: 8px;">Executada</span>
                                    </div>
                                    <small>Etapa foi executada</small>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-2">
                                <div class="legenda-item">
                                    <div style="border: 2px solid #6c757d; background: #f8f9fa; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; position: relative; opacity: 0.7;">
                                        <div style="position: absolute; left: 0; top: 0; width: 4px; height: 100%; background: #6c757d; border-radius: 0 2px 2px 0;"></div>
                                        <span style="margin-left: 8px; text-decoration: line-through; color: #6c757d;">Pulada</span>
                                    </div>
                                    <small>Etapa foi pulada/não aplicável</small>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-2">
                                <div class="legenda-item">
                                    <span class="badge badge-warning">Em Análise</span>
                                    <small>Em processamento</small>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-2">
                                <div class="legenda-item">
                                    <span class="badge badge-secondary">Aguardando</span>
                                    <small>Não iniciada</small>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-2">
                                <div class="legenda-item">
                                    <i class="fas fa-edit text-primary"></i>
                                    <small>Pode editar/interagir</small>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-2">
                                <div class="legenda-item">
                                    <i class="fas fa-eye text-info"></i>
                                    <small>Apenas visualizar</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="alert alert-info alert-sm">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Regra de segurança:</strong> Apenas a etapa atual do fluxo permite edição e upload de documentos. 
                                    As demais etapas ficam em modo visualização (cores neutras) para consulta e histórico.
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
        
        /* Responsividade para Cards Compactos */
        @media (max-width: 768px) {
            .etapa-card-compacta .row {
                margin: 0;
            }
            
            .etapa-card-compacta .col-2,
            .etapa-card-compacta .col-7,
            .etapa-card-compacta .col-3 {
                padding: 0.25rem;
            }
            
            .etapa-nome-compacta {
                font-size: 0.9rem;
            }
            
            .org-fluxo-compacto {
                font-size: 0.7rem;
            }
            
            .etapa-acoes-compactas .btn-xs {
                padding: 0.15rem 0.3rem;
                font-size: 0.65rem;
            }
            
            .badge-circle {
                width: 24px !important;
                height: 24px !important;
                font-size: 0.7rem !important;
            }
        }
        
        @media (max-width: 576px) {
            .etapa-card-compacta .col-2 {
                flex: 0 0 20%;
                max-width: 20%;
            }
            
            .etapa-card-compacta .col-7 {
                flex: 0 0 55%;
                max-width: 55%;
            }
            
            .etapa-card-compacta .col-3 {
                flex: 0 0 25%;
                max-width: 25%;
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
            margin-bottom: 0.75rem;
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
        

        
        /* ===== CARD COMPACTO DA ETAPA ===== */
        .etapa-compacta {
            margin-bottom: 0.5rem;
        }
        
        .etapa-card-compacta {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            transition: all 0.2s ease;
            cursor: pointer;
            background: #ffffff;
        }
        
        .etapa-card-compacta:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 12px rgba(0,0,0,0.15);
            border-color: #007bff;
        }
        
        .etapa-card-compacta.etapa-atual {
            border-color: #28a745;
            border-width: 2px;
            background: linear-gradient(135deg, #f8fff9 0%, #f0f9f0 100%);
            box-shadow: 0 3px 12px rgba(40, 167, 69, 0.2);
        }
        
        .etapa-card-compacta.etapa-atual::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #28a745, #20c997);
            border-radius: 0 2px 2px 0;
        }
        
        .etapa-card-compacta.status-aprovado {
            border-color: #28a745;
            background: linear-gradient(135deg, #ffffff 0%, #f8fff9 100%);
        }
        
        /* ===== ESTILO PARA ETAPAS EXECUTADAS ===== */
        .etapa-card-compacta.etapa-executada {
            border-color: #28a745 !important;
            background: linear-gradient(135deg, #ffffff 0%, #f8fff9 100%) !important;
            position: relative;
        }
        
        .etapa-card-compacta.etapa-executada::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #28a745, #20c997);
            border-radius: 0 2px 2px 0;
        }
        
        /* ===== ESTILO PARA ETAPAS PULADAS/NÃO APLICÁVEIS ===== */
        .card.etapa-card-compacta.etapa-pulada,
        .etapa-card-compacta.etapa-pulada {
            border: 2px solid #6c757d !important;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            position: relative !important;
            opacity: 0.7 !important;
        }
        
        .etapa-card-compacta.etapa-pulada::before {
            content: "" !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 4px !important;
            height: 100% !important;
            background: linear-gradient(to bottom, #6c757d, #495057) !important;
            border-radius: 0 2px 2px 0 !important;
            z-index: 1 !important;
        }
        
        .etapa-card-compacta.etapa-pulada .etapa-nome-compacta {
            color: #6c757d !important;
            text-decoration: line-through !important;
            font-style: italic !important;
        }
        
        .etapa-card-compacta.etapa-pulada .org-fluxo-compacto {
            color: #adb5bd !important;
        }
        
        .etapa-card-compacta.etapa-pulada .badge {
            background-color: #6c757d !important;
            color: #ffffff !important;
        }
        
        /* Garantir que etapas puladas sempre tenham precedência visual */
        .etapa-card-compacta.etapa-pulada.nao-iniciada {
            border-color: #6c757d !important;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
        }
        
        .etapa-card-compacta.status-reprovado {
            border-color: #dc3545;
            background: linear-gradient(135deg, #ffffff 0%, #fff8f9 100%);
        }
        
        .etapa-card-compacta.status-em_analise,
        .etapa-card-compacta.status-pendente {
            border-color: #ffc107;
            background: linear-gradient(135deg, #ffffff 0%, #fffef8 100%);
        }
        
        .etapa-card-compacta.nao-iniciada {
            border-color: #6c757d;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            opacity: 0.85;
        }
        
        /* ===== ETAPAS INATIVAS (não são a atual do fluxo) ===== */
        .etapa-card-compacta.etapa-inativa {
            background: #ffffff;
            border-color: #e9ecef;
            position: relative;
        }
        
        .etapa-card-compacta.etapa-inativa:hover {
            border-color: #adb5bd;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .etapa-card-compacta.etapa-inativa .btn {
        }
        
        .etapa-card-compacta.etapa-inativa .etapa-nome-compacta {
            color: #495057;
        }
        
        .etapa-card-compacta.etapa-inativa .org-fluxo-compacto {
            color: #6c757d;
        }
        
        /* ===== ELEMENTOS DO CARD COMPACTO ===== */
        .etapa-numero-compacto .badge-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .etapa-nome-compacta {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2c3e50;
            line-height: 1.2;
            margin-bottom: 0;
        }
        
        .org-fluxo-compacto {
            font-size: 0.75rem;
            color: #6c757d;
            line-height: 1.1;
        }
        
        .org-fluxo-compacto .org-solicitante, 
        .org-fluxo-compacto .org-executora {
            font-weight: 500;
        }
        
        .etapa-status-compacto .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            font-weight: 500;
        }
        
        .etapa-acoes-compactas .btn-xs {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
            border-radius: 3px;
            margin-left: 2px;
        }
        
        .etapa-acoes-compactas .btn-group-sm {
            display: flex;
            gap: 2px;
        }
        
        /* Badge "ATUAL" compacto */
        .badge-sm {
            font-size: 0.65rem;
            padding: 0.2rem 0.4rem;
        }
        
        /* ===== SEPARADORES COMPACTOS ===== */
        .etapa-separador-compacto {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0.1rem 0;
            position: relative;
        }
        
        .linha-separadora-compacta {
            width: 2px;
            height: 8px;
            background: linear-gradient(to bottom, #dee2e6, #6c757d);
            border-radius: 1px;
        }
        
        .icone-separador-compacto {
            position: absolute;
            background: #ffffff;
            color: #6c757d;
            font-size: 0.6rem;
            padding: 1px;
            border-radius: 50%;
            border: 1px solid #dee2e6;
        }
        
        /* ===== SEÇÃO DE OPÇÕES DE TRANSIÇÃO ===== */
        .opcoes-transicao {
            margin-left: 1.2rem;
            padding-left: 0.5rem;
            border-left: 2px solid #e9ecef;
            position: relative;
            margin-top: 0.15rem;
            margin-bottom: 0.15rem;
        }
        
        /* ===== SISTEMA DE EXPANSÃO/COLAPSO ===== */
        .opcoes-toggle {
            cursor: pointer;
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            margin-bottom: 0.3rem;
            border: 1px solid transparent;
        }
        
        .opcoes-toggle:hover {
            background: #e9ecef;
            border-color: #007bff;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .opcoes-titulo-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
        }
        
        .opcoes-toggle-area {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .toggle-hint {
            font-size: 0.7rem;
            opacity: 0.7;
            font-style: italic;
            transition: opacity 0.3s ease;
        }
        
        .opcoes-toggle:hover .toggle-hint {
            opacity: 1;
            color: #007bff;
        }
        
        .opcoes-arrow {
            transition: transform 0.3s ease;
            color: #495057;
            font-size: 0.9rem;
            padding: 0.2rem;
            border-radius: 3px;
            background: rgba(0,123,255,0.1);
        }
        
        .opcoes-toggle:hover .opcoes-arrow {
            color: #007bff;
            background: rgba(0,123,255,0.2);
        }
        
        .opcoes-arrow.rotated {
            transform: rotate(180deg);
        }
        
        /* Container das opções */
        .opcoes-container {
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        /* Indicador visual quando há opções ativas */
        .opcoes-toggle.tem-ativa {
            background: linear-gradient(135deg, #e8f5e8 0%, #f0f9f0 100%);
            border-left: 3px solid #28a745;
        }
        
        .opcoes-toggle.tem-ativa .opcoes-arrow {
            color: #28a745;
            background: rgba(40,167,69,0.1);
        }
        
        .opcoes-toggle.tem-ativa:hover .opcoes-arrow {
            background: rgba(40,167,69,0.2);
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
        
        /* ===== BOTÃO DE ACESSO/EDIÇÃO DA ETAPA ===== */
        .botao-acesso-etapa {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 1rem;
            margin-bottom: 1rem !important;
        }
        
        .btn-acesso-etapa {
            font-size: 1rem;
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.15);
        }
        
        .btn-acesso-etapa.btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            color: white;
        }
        
        .btn-acesso-etapa.btn-primary:hover {
            background: linear-gradient(135deg, #0056b3 0%, #003d82 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }
        
        .btn-acesso-etapa.btn-outline-info {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid #17a2b8;
            color: #17a2b8;
        }
        
        .btn-acesso-etapa.btn-outline-info:hover {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border-color: #138496;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(23, 162, 184, 0.4);
        }
        
        .btn-acesso-etapa:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s;
        }
        
        .btn-acesso-etapa:hover:before {
            left: 100%;
        }
        
        .btn-acesso-etapa .fas {
            transition: transform 0.3s ease;
        }
        
        .btn-acesso-etapa:hover .fa-arrow-right {
            transform: translateX(3px);
        }
        
        .btn-acesso-etapa:hover .fa-edit,
        .btn-acesso-etapa:hover .fa-eye {
            transform: scale(1.1);
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
        
        /* ===== CARDS DAS OPÇÕES ===== */
        .opcao-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 0.6rem;
            margin-bottom: 0.4rem;
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
            left: -3px;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            background: #28a745;
            border-radius: 50%;
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.3);
        }
        
        .opcao-card.opcao-final {
            background: linear-gradient(135deg, #fff3cd 0%, #fef7e0 100%);
            border-color: #ffc107;
        }
        
        .opcao-card.opcao-sem-transicoes {
            background: linear-gradient(135deg, #f8d7da 0%, #fce6e7 100%);
            border-color: #f5c6cb;
            border-style: dashed;
        }
        
        .opcao-condicao {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.4rem;
            font-size: 0.8rem;
        }
        
        .condicao-label {
            font-weight: 600;
            color: #6c757d;
        }
        
        .opcao-destino {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8rem;
        }
        
        .destino-info {
            display: flex;
            align-items: center;
            flex: 1;
        }
        
        .destino-nome {
            font-weight: 600;
            color: #495057;
            margin-right: 0.4rem;
        }
        
        .destino-org {
            font-size: 0.75rem;
        }
        
        /* ===== ESTILOS PARA BADGE DE STATUS DA AÇÃO ===== */
        .badge-lg {
            font-size: 0.95rem;
            padding: 0.5rem 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border-radius: 0.375rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .badge-warning {
            background-color: #ffc107 !important;
            color: #856404 !important;
        }
        
        .badge-primary {
            background-color: #007bff !important;
            color: #ffffff !important;
        }
        
        .badge-danger {
            background-color: #dc3545 !important;
            color: #ffffff !important;
        }
        
        .badge-success {
            background-color: #28a745 !important;
            color: #ffffff !important;
        }
        
        .badge-dark {
            background-color: #343a40 !important;
            color: #ffffff !important;
        }
        
        .badge-info {
            background-color: #17a2b8 !important;
            color: #ffffff !important;
        }
        
        .badge-secondary {
            background-color: #6c757d !important;
            color: #ffffff !important;
        }
        
        /* Animação sutil para status da ação */
        .badge-lg:hover {
            transform: scale(1.05);
            transition: transform 0.2s ease;
        }
        
        /* Status especial para projeto finalizado */
        .badge-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        /* Modal mais largo para finalização */
        .swal-wide {
            width: 600px !important;
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
                $etapasJson = $etapasFluxo->map(function($etapa) use ($execucoes, $etapasAcessiveis, $acao) {
                    $execucao = $execucoes->get($etapa->id);
                    $acessibilidade = $etapasAcessiveis->get($etapa->id, []);
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
                        'pode_acessar' => $acessibilidade['pode_acessar'] ?? false,
                        'pode_ver_detalhes' => $acessibilidade['pode_ver_detalhes'] ?? false,
                        'url_detalhada' => route('workflow.etapa-detalhada', [$acao->id, $etapa->id]),
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
                    
                    <!-- Botão de Acesso/Edição Evidente - TOPO -->
                    ${etapa.pode_ver_detalhes ? `
                        <div class="botao-acesso-etapa mb-4">
                            <a href="${etapa.url_detalhada}" class="btn btn-acesso-etapa ${etapa.pode_acessar ? 'btn-primary' : 'btn-outline-info'} btn-block">
                                <i class="fas fa-${etapa.pode_acessar ? 'edit' : 'eye'} mr-2"></i>
                                ${etapa.pode_acessar ? 'Editar Etapa' : 'Acessar Etapa'}
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                            ${etapa.pode_acessar ? `
                                <small class="text-success d-block text-center mt-2">
                                    <i class="fas fa-check-circle"></i>
                                    Você pode editar esta etapa
                                </small>
                            ` : `
                                <small class="text-muted d-block text-center mt-2">
                                    <i class="fas fa-info-circle"></i>
                                    Modo visualização apenas
                                </small>
                            `}
                        </div>
                    ` : ''}
                    
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

        // ===== SISTEMA DE EXPANSÃO/COLAPSO DAS OPÇÕES =====
        function toggleOpcoes(etapaId) {
            const container = document.getElementById(`opcoes-${etapaId}`);
            const arrow = document.getElementById(`arrow-${etapaId}`);
            const toggle = arrow.closest('.opcoes-toggle');
            
            if (container.style.display === 'none' || container.style.display === '') {
                // Expandir
                container.style.display = 'block';
                arrow.classList.add('rotated');
                
                // Animar a expansão
                container.style.maxHeight = '0px';
                container.style.opacity = '0';
                setTimeout(() => {
                    container.style.maxHeight = container.scrollHeight + 'px';
                    container.style.opacity = '1';
                }, 10);
                
            } else {
                // Colapsar
                container.style.maxHeight = '0px';
                container.style.opacity = '0';
                arrow.classList.remove('rotated');
                
                setTimeout(() => {
                    container.style.display = 'none';
                    container.style.maxHeight = '';
                }, 300);
            }
        }
        
        // ===== DESTACAR TOGGLES COM OPÇÕES ATIVAS =====
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar cada etapa se tem opções ativas
            document.querySelectorAll('.opcoes-transicao').forEach(function(opcaoTransicao) {
                const hasOpcaoAtiva = opcaoTransicao.querySelector('.opcao-ativa');
                const toggle = opcaoTransicao.querySelector('.opcoes-toggle');
                
                if (hasOpcaoAtiva && toggle) {
                    toggle.classList.add('tem-ativa');
                }
            });
        });
        
        // ===== MELHORAR A VISUALIZAÇÃO DOS ALERTAS DE ETAPA =====
        
        // Função para finalizar projeto completo (independente de transições)
        function finalizarProjetoCompleto(acaoId) {
            console.log('Finalizar projeto completo:', acaoId);
            
            Swal.fire({
                title: 'Finalizar Projeto Completo',
                html: `
                    <div class="text-left">
                        <p><strong>Atenção:</strong> Esta ação irá:</p>
                        <ul style="text-align: left; display: inline-block;">
                            <li>Finalizar o projeto independente de etapas pendentes</li>
                            <li>Marcar todas as etapas não executadas como "Não Aplicável"</li>
                            <li>Definir o status final do projeto como "FINALIZADO"</li>
                        </ul>
                        <div class="mt-3">
                            <label for="observacaoFinalizacao" class="form-label">Observação da finalização:</label>
                            <textarea id="observacaoFinalizacao" class="form-control" rows="3" 
                                      placeholder="Digite o motivo da finalização (opcional)"></textarea>
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, finalizar projeto',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                customClass: {
                    popup: 'swal-wide'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const observacao = document.getElementById('observacaoFinalizacao').value;
                    
                    // Mostrar loading
                    Swal.fire({
                        title: 'Finalizando Projeto...',
                        text: 'Aguarde enquanto o projeto é finalizado',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    $.post(`/workflow/acao/${acaoId}/finalizar-completo`, {
                        _token: '{{ csrf_token() }}',
                        observacao: observacao
                    })
                    .done(function(response) {
                        console.log('Projeto finalizado:', response);
                        Swal.fire({
                            title: 'Projeto Finalizado!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    })
                    .fail(function(xhr) {
                        console.error('Erro ao finalizar projeto:', xhr);
                        let message = xhr.responseJSON?.error || 'Erro ao finalizar projeto';
                        Swal.fire('Erro!', message, 'error');
                    });
                }
            });
        }
    </script>
@stop 