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

                <!-- Mapa do Fluxo Condicional -->
                @if($tipo_fluxo->etapasFluxo->count() > 0)
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
                                        <strong>Visualização do Fluxo:</strong> Este mapa mostra como as etapas se conectam através de condições.
                                        As setas indicam para onde cada etapa direciona baseado no status escolhido.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="fluxo-condicional">
                                <div class="row">
                                    <!-- Coluna Principal - Fluxo das Etapas -->
                                    <div class="col-lg-8">
                                        @php
                                            $etapasOrdenadas = $tipo_fluxo->etapasFluxo->sortBy('ordem_execucao');
                                        @endphp
                                        
                                        @foreach($etapasOrdenadas as $index => $etapa)
                                            @php
                                                $transicoes = $etapa->transicoesOrigem->where('is_ativo', true);
                                            @endphp
                                            
                                            <!-- Separador compacto entre etapas -->
                                            @if($index > 0)
                                                <div class="etapa-separador-compacto">
                                                    <div class="linha-separadora-compacta"></div>
                                                    <div class="icone-separador-compacto">
                                                        <i class="fas fa-chevron-down"></i>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <div class="etapa-container" data-etapa-id="{{ $etapa->id }}">
                                                <!-- Card Principal da Etapa - VERSÃO COMPACTA -->
                                                <div class="etapa-compacta">
                                                    <div class="card etapa-card-compacta">
                                                        <div class="card-body p-3">
                                                            <div class="row align-items-center">
                                                                <!-- Número e Status -->
                                                                <div class="col-2 text-center">
                                                                    <div class="etapa-numero-compacto">
                                                                        <span class="badge badge-circle badge-primary">{{ $etapa->ordem_execucao ?? ($index + 1) }}</span>
                                                                    </div>
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
                                                                
                                                                <!-- Badges e Informações -->
                                                                <div class="col-3 text-right">
                                                                    <div class="etapa-badges-compacto mb-2">
                                                                        @if($etapa->modulo)
                                                                            <span class="badge badge-info badge-sm">{{ Str::limit($etapa->modulo->nome, 8) }}</span>
                                                                        @endif
                                                                        @if($etapa->tipo_etapa == 'CONDICIONAL')
                                                                            <span class="badge badge-warning badge-sm">Cond.</span>
                                                                        @endif
                                                                        @if($etapa->is_obrigatoria)
                                                                            <span class="badge badge-danger badge-sm">Obrig.</span>
                                                                        @endif
                                                                    </div>
                                                                    
                                                                    <!-- Prazo -->
                                                                    <div class="prazo-info-compacto">
                                                                        <small class="text-muted">
                                                                            <i class="fas fa-clock"></i>
                                                                            {{ $etapa->prazo_dias }}{{ $etapa->tipo_prazo == 'UTEIS' ? 'du' : 'dc' }}
                                                                        </small>
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
                                                                @endphp
                                                                
                                                                <div class="opcao-card opcao-compacta">
                                                                    <div class="opcao-condicao">
                                                                        <span class="condicao-label">Se for:</span>
                                                                        <span class="badge badge-{{ $statusCondicao->codigo === 'APROVADO' ? 'success' : ($statusCondicao->codigo === 'REPROVADO' ? 'danger' : 'warning') }} badge-sm">
                                                                            {{ $statusCondicao->nome }}
                                                                        </span>
                                                                    </div>
                                                                    
                                                                    <div class="opcao-destino">
                                                                        <div class="destino-info">
                                                                            <i class="fas fa-long-arrow-alt-right text-muted mr-2"></i>
                                                                            <span class="destino-nome">{{ Str::limit($etapaDestino->nome_etapa, 20) }}</span>
                                                                            <small class="destino-org text-muted">({{ Str::limit($etapaDestino->organizacaoExecutora->nome, 10) }})</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="opcoes-transicao">
                                                        <div class="opcoes-toggle">
                                                            <div class="opcoes-titulo-toggle">
                                                                <small class="text-success">
                                                                    <i class="fas fa-flag-checkered"></i>
                                                                    Etapa final do fluxo
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <!-- Caixa Lateral Direita - Informações Úteis -->
                                    <div class="col-lg-4">
                                        <div class="info-lateral-static">
                                            <div class="info-header">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-info-circle text-white"></i>
                                                    Resumo do Fluxo
                                                </h6>
                                            </div>
                                            
                                            <div class="info-content">
                                                <div class="resumo-estatisticas">
                                                    <div class="stat-item">
                                                        <i class="fas fa-tasks text-primary"></i>
                                                        <span class="stat-label">Total de Etapas:</span>
                                                        <span class="stat-value">{{ $etapasOrdenadas->count() }}</span>
                                                    </div>
                                                    
                                                    <div class="stat-item">
                                                        <i class="fas fa-exclamation-triangle text-danger"></i>
                                                        <span class="stat-label">Obrigatórias:</span>
                                                        <span class="stat-value">{{ $etapasOrdenadas->where('is_obrigatoria', true)->count() }}</span>
                                                    </div>
                                                    
                                                    <div class="stat-item">
                                                        <i class="fas fa-random text-warning"></i>
                                                        <span class="stat-label">Condicionais:</span>
                                                        <span class="stat-value">{{ $etapasOrdenadas->where('tipo_etapa', 'CONDICIONAL')->count() }}</span>
                                                    </div>
                                                    
                                                    <div class="stat-item">
                                                        <i class="fas fa-clock text-info"></i>
                                                        <span class="stat-label">Prazo Total:</span>
                                                        <span class="stat-value">{{ $etapasOrdenadas->sum('prazo_dias') }} dias</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="organizacoes-envolvidas">
                                                    <h6 class="text-muted">
                                                        <i class="fas fa-building mr-1"></i>
                                                        Organizações Envolvidas:
                                                    </h6>
                                                    @php
                                                        $organizacoes = collect();
                                                        foreach($etapasOrdenadas as $etapa) {
                                                            if($etapa->organizacaoSolicitante) {
                                                                $organizacoes->push($etapa->organizacaoSolicitante->nome);
                                                            }
                                                            if($etapa->organizacaoExecutora) {
                                                                $organizacoes->push($etapa->organizacaoExecutora->nome);
                                                            }
                                                        }
                                                        $organizacoes = $organizacoes->unique();
                                                    @endphp
                                                    
                                                    @foreach($organizacoes as $org)
                                                        <span class="badge badge-secondary mb-1">{{ $org }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

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

    // ===== SISTEMA DE EXPANSÃO/COLAPSO DAS OPÇÕES =====
    function toggleOpcoes(etapaId) {
        const opcoes = document.getElementById('opcoes-' + etapaId);
        const arrow = document.getElementById('arrow-' + etapaId);
        
        if (opcoes.style.display === 'none' || opcoes.style.display === '') {
            opcoes.style.display = 'block';
            arrow.classList.add('expanded');
        } else {
            opcoes.style.display = 'none';
            arrow.classList.remove('expanded');
        }
    }

    // ===== INICIALIZAÇÃO ===== 
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Mapa de fluxo condicional carregado!');
        
        // Todas as opções começam colapsadas
        document.querySelectorAll('.opcoes-container').forEach(function(container) {
            container.style.display = 'none';
        });
    });
</script>
@stop

@section('css')
<style>
    /* ===== ESTILOS DO MAPA DE FLUXO CONDICIONAL - VERSÃO COMPACTA ===== */
    .fluxo-condicional {
        padding: 1rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
    }
    
    /* ===== CONTAINER DA ETAPA ===== */
    .etapa-container {
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
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
        background: #ffffff;
    }
    
    .etapa-card-compacta:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 12px rgba(0,0,0,0.15);
        border-color: #007bff;
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
    
    .etapa-badges-compacto .badge {
        font-size: 0.6rem;
        padding: 0.15rem 0.3rem;
        font-weight: 500;
        margin: 0 1px;
    }
    
    .prazo-info-compacto {
        font-size: 0.7rem;
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
        padding: 0.3rem 0.5rem;
        border-radius: 4px;
        transition: all 0.2s ease;
        background: rgba(108, 117, 125, 0.1);
        margin-bottom: 0.3rem;
    }
    
    .opcoes-toggle:hover {
        background: rgba(0, 123, 255, 0.1);
        border-color: #007bff;
    }
    
    .opcoes-titulo-toggle {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
    }
    
    .opcoes-toggle-area {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    
    .toggle-hint {
        font-size: 0.65rem;
        opacity: 0.7;
    }
    
    .opcoes-arrow {
        transition: transform 0.3s ease;
        background: rgba(0, 123, 255, 0.15);
        padding: 0.2rem;
        border-radius: 50%;
        color: #007bff;
        font-size: 0.6rem;
    }
    
    .opcoes-arrow.expanded {
        transform: rotate(180deg);
    }
    
    /* ===== CONTAINER DAS OPÇÕES ===== */
    .opcoes-container {
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .opcao-card.opcao-compacta {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 0.5rem;
        margin-bottom: 0.3rem;
        transition: all 0.3s ease;
        position: relative;
        font-size: 0.8rem;
    }
    
    .opcao-card.opcao-compacta:hover {
        background: #ffffff;
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
    }
    
    .opcao-condicao {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.3rem;
        font-size: 0.75rem;
    }
    
    .condicao-label {
        font-weight: 600;
        color: #6c757d;
    }
    
    .opcao-destino {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.75rem;
    }
    
    .destino-info {
        display: flex;
        align-items: center;
        flex: 1;
    }
    
    .destino-nome {
        font-weight: 600;
        color: #495057;
        margin-right: 0.3rem;
    }
    
    .destino-org {
        font-size: 0.7rem;
    }
    
    /* ===== CAIXA LATERAL DIREITA ===== */
    .info-lateral-static {
        background: #ffffff;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        position: sticky;
        top: 20px;
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
    }
    
    .resumo-estatisticas {
        margin-bottom: 1.5rem;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.75rem;
        padding: 0.5rem;
        background: #f8f9fa;
        border-radius: 6px;
    }
    
    .stat-item i {
        width: 20px;
        margin-right: 0.75rem;
    }
    
    .stat-label {
        font-weight: 600;
        color: #495057;
        margin-right: 0.5rem;
        flex: 1;
    }
    
    .stat-value {
        color: #007bff;
        font-weight: 700;
    }
    
    .organizacoes-envolvidas h6 {
        margin-bottom: 0.75rem;
        font-weight: 600;
    }
    
    .organizacoes-envolvidas .badge {
        margin: 2px;
        font-size: 0.75rem;
    }
    
    /* ===== RESPONSIVIDADE ===== */
    @media (max-width: 991px) {
        .opcoes-transicao {
            margin-left: 1rem;
            padding-left: 0.5rem;
        }
        
        .info-lateral-static {
            position: static;
            margin-top: 2rem;
        }
    }
    
    @media (max-width: 768px) {
        .fluxo-condicional {
            padding: 0.5rem;
        }
        
        .etapa-card-compacta {
            padding: 0.5rem;
        }
        
        .etapa-nome-compacta {
            font-size: 0.9rem;
        }
        
        .org-fluxo-compacto {
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .org-fluxo-compacto i {
            transform: rotate(90deg);
        }
        
        .opcoes-transicao {
            margin-left: 0.5rem;
        }
    }
</style>
@stop 