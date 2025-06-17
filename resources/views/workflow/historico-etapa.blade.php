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
    <!-- Informações da Etapa -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i>
                Informações da Etapa
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Ação:</strong> {{ $execucao->acao->nome }}<br>
                    <strong>Etapa:</strong> {{ $execucao->etapaFluxo->nome_etapa }}<br>
                    <strong>Status Atual:</strong> 
                    <span class="badge badge-{{ $execucao->status_cor }}">
                        {{ $execucao->status->nome }}
                    </span>
                </div>
                <div class="col-md-6">
                    <strong>Responsável:</strong> {{ $execucao->usuarioResponsavel->name ?? 'N/A' }}<br>
                    <strong>Data Início:</strong> {{ $execucao->data_inicio->format('d/m/Y H:i') }}<br>
                    @if($execucao->data_conclusao)
                        <strong>Data Conclusão:</strong> {{ $execucao->data_conclusao->format('d/m/Y H:i') }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Histórico -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-clock"></i>
                Histórico de Ações
            </h3>
            <div class="card-tools">
                <a href="{{ route('workflow.acao', $execucao->acao) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar ao Workflow
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="timeline">
                @forelse($historicos as $historico)
                    <div class="time-label">
                        <span class="bg-{{ $historico->cor_acao ?? 'primary' }}">
                            {{ $historico->data_acao->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    <div>
                        <i class="{{ $historico->icone_acao ?? 'fas fa-circle' }} bg-{{ $historico->cor_acao ?? 'primary' }}"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="fas fa-user"></i>
                                {{ $historico->usuario->name }}
                            </span>
                            <h3 class="timeline-header">
                                {{ $historico->descricao_acao }}
                            </h3>
                            <div class="timeline-body">
                                @if($historico->observacao)
                                    <p><strong>Observações:</strong> {{ $historico->observacao }}</p>
                                @endif
                                
                                @if($historico->status_anterior_id && $historico->status_novo_id)
                                    <p>
                                        <strong>Status alterado:</strong>
                                        <span class="badge badge-secondary">{{ $historico->statusAnterior->nome }}</span>
                                        <i class="fas fa-arrow-right mx-2"></i>
                                        <span class="badge badge-primary">{{ $historico->statusNovo->nome }}</span>
                                    </p>
                                @endif
                                
                                @if($historico->dados_alterados)
                                    @php
                                        $dados = json_decode($historico->dados_alterados, true);
                                    @endphp
                                    @if(is_array($dados) && count($dados) > 0)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <strong>Detalhes:</strong>
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
                                    @endif
                                @endif

                                @if($historico->ip_usuario)
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-globe"></i> IP: {{ $historico->ip_usuario }}
                                            @if($historico->user_agent)
                                                <br><i class="fas fa-desktop"></i> {{ $historico->user_agent }}
                                            @endif
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-history fa-3x mb-3"></i>
                        <h5>Nenhum histórico encontrado</h5>
                        <p>Esta etapa ainda não possui registros de histórico.</p>
                    </div>
                @endforelse
                
                <div>
                    <i class="fas fa-clock bg-gray"></i>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .timeline-item {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 0.375rem;
        }
        
        .timeline-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .badge {
            font-size: 0.85em;
        }
    </style>
@stop 