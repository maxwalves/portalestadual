@extends('adminlte::page')

@section('title', 'Histórico da Etapa')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>
                <i class="fas fa-history text-primary"></i>
                Histórico da Etapa
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acoes.index') }}">Ações</a></li>
                <li class="breadcrumb-item"><a href="{{ route('workflow.acao', $execucao->acao) }}">Workflow</a></li>
                <li class="breadcrumb-item active">Histórico</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Informações da Etapa - Layout Compacto -->
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="info-icon mr-3">
                            <i class="fas fa-tasks fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 text-dark">{{ $execucao->etapaFluxo->nome_etapa }}</h5>
                            <p class="mb-0 text-muted">{{ $execucao->acao->nome }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <span class="badge badge-{{ $execucao->status->codigo === 'APROVADO' ? 'success' : ($execucao->status->codigo === 'REPROVADO' ? 'danger' : 'warning') }} badge-lg">
                        {{ $execucao->status->nome }}
                    </span>
                    <div class="mt-1">
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> {{ $execucao->data_inicio->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Histórico - Timeline Moderna -->
    <div class="card card-outline shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark">
                    <i class="fas fa-history text-info mr-2"></i>
                    Histórico de Ações
                </h5>
                <a href="{{ route('workflow.acao', $execucao->acao) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @forelse($historicos as $historico)
                <div class="historico-item {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex p-3">
                        <!-- Ícone e Linha do Tempo -->
                        <div class="historico-icon mr-3">
                            <div class="icon-circle bg-{{ $historico->cor_acao ?? 'primary' }}">
                                <i class="{{ $historico->icone_acao ?? 'fas fa-circle' }} text-white"></i>
                            </div>
                            @if(!$loop->last)
                                <div class="timeline-line"></div>
                            @endif
                        </div>
                        
                        <!-- Conteúdo -->
                        <div class="historico-content flex-grow-1">
                            <!-- Header da Ação -->
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1 text-dark">{{ $historico->descricao_acao }}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-user mr-1"></i>{{ $historico->usuario->name ?? 'Sistema' }}
                                    </small>
                                </div>
                                <div class="text-right">
                                    <small class="text-muted font-weight-bold">
                                        {{ $historico->data_acao->format('d/m/Y') }}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        {{ $historico->data_acao->format('H:i') }}
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Conteúdo Principal -->
                            <div class="historico-details">
                                @if($historico->observacao)
                                    <div class="alert alert-light border-left border-info py-2 px-3 mb-2">
                                        <small><strong>Observações:</strong> {{ $historico->observacao }}</small>
                                    </div>
                                @endif
                                
                                @if($historico->status_anterior_id && $historico->status_novo_id)
                                    <div class="status-change mb-2">
                                        <small class="text-muted">Status alterado:</small>
                                        <div class="d-flex align-items-center mt-1">
                                            <span class="badge badge-secondary badge-sm">{{ $historico->statusAnterior->nome }}</span>
                                            <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                            <span class="badge badge-primary badge-sm">{{ $historico->statusNovo->nome }}</span>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($historico->dados_alterados)
                                    @php
                                        $dados = json_decode($historico->dados_alterados, true);
                                    @endphp
                                    @if(is_array($dados) && count($dados) > 0)
                                        <div class="collapse" id="detalhes{{ $historico->id }}">
                                            <div class="border border-light rounded p-2 mt-2">
                                                <small class="text-muted">
                                                    <strong>Detalhes técnicos:</strong>
                                                    @foreach($dados as $chave => $valor)
                                                        @if($chave === 'documento_id')
                                                            <br>• Documento ID: {{ $valor }}
                                                        @elseif($chave === 'tipo_documento')
                                                            <br>• Tipo: {{ $valor }}
                                                        @elseif($chave === 'nome_arquivo')
                                                            <br>• Arquivo: {{ $valor }}
                                                        @elseif($chave === 'motivo')
                                                            <br>• Motivo: {{ $valor }}
                                                        @else
                                                            <br>• {{ ucfirst(str_replace('_', ' ', $chave)) }}: {{ $valor }}
                                                        @endif
                                                    @endforeach
                                                </small>
                                            </div>
                                        </div>
                                        <button class="btn btn-link btn-sm p-0 mt-1" type="button" data-toggle="collapse" data-target="#detalhes{{ $historico->id }}">
                                            <small><i class="fas fa-eye"></i> Ver detalhes técnicos</small>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="fas fa-history fa-3x mb-3 text-light"></i>
                    <h5 class="text-muted">Nenhum histórico encontrado</h5>
                    <p class="text-muted">Esta etapa ainda não possui registros de histórico.</p>
                </div>
            @endforelse
        </div>
    </div>
@stop

@section('css')
    <style>
        /* ===== LAYOUT CLEAN DO HISTÓRICO ===== */
        
        /* Card principal */
        .card-outline {
            border: 1px solid #e3e6f0;
            border-radius: 8px;
        }
        
        .shadow-sm {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
        }
        
        /* Header do card */
        .card-header.bg-white {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            border-bottom: 1px solid #e3e6f0 !important;
            padding: 1rem 1.25rem;
        }
        
        /* Badge melhorado */
        .badge-lg {
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
            border-radius: 0.375rem;
        }
        
        .badge-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        /* ===== TIMELINE MODERNA ===== */
        
        .historico-item {
            transition: background-color 0.2s ease;
        }
        
        .historico-item:hover {
            background-color: #f8f9fa;
        }
        
        .historico-item.border-bottom {
            border-bottom: 1px solid #e9ecef !important;
        }
        
        /* Ícone circular */
        .historico-icon {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .icon-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 3px solid #ffffff;
        }
        
        /* Linha de conexão */
        .timeline-line {
            width: 2px;
            height: calc(100% + 1rem);
            background: linear-gradient(to bottom, #e9ecef 0%, transparent 100%);
            margin-top: 0.5rem;
            position: absolute;
            top: 36px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        /* Conteúdo */
        .historico-content h6 {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }
        
        /* Alert customizado para observações */
        .alert-light {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-left: 4px solid #17a2b8;
            border-radius: 0.375rem;
        }
        
        /* Status change */
        .status-change {
            background-color: #f1f3f4;
            border-radius: 0.375rem;
            padding: 0.5rem;
        }
        
        /* Botão de detalhes */
        .btn-link {
            color: #6c757d;
            text-decoration: none;
        }
        
        .btn-link:hover {
            color: #495057;
            text-decoration: underline;
        }
        
        /* Cores dos ícones */
        .bg-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
        }
        
        .bg-success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
        }
        
        .bg-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
        }
        
        .bg-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        }
        
        .bg-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
        }
        
        .bg-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
        }
        
        /* Info icon */
        .info-icon {
            min-width: 48px;
            text-align: center;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .card-body.p-3 {
                padding: 1rem !important;
            }
            
            .historico-item .d-flex {
                padding: 1rem !important;
            }
            
            .historico-icon {
                margin-right: 1rem !important;
            }
            
            .icon-circle {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }
            
            .badge-lg {
                font-size: 0.8rem;
                padding: 0.3rem 0.6rem;
            }
        }
        
        /* Smooth transitions */
        .card, .historico-item, .icon-circle, .badge {
            transition: all 0.2s ease;
        }
        
        /* Focus states */
        .btn:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
    </style>
@stop