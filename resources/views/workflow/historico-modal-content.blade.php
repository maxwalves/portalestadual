<!-- Conteúdo do Modal de Histórico (para AJAX) -->
@if($historicos->count() > 0)
    <div class="timeline">
        @foreach($historicos as $historico)
            <div class="timeline-item {{ $loop->first ? 'timeline-item-first' : '' }}">
                <div class="timeline-marker">
                    @php
                        $icone = 'fas fa-circle';
                        $cor = 'primary';
                        
                        switch($historico->acao) {
                            case 'ETAPA_INICIADA':
                                $icone = 'fas fa-play';
                                $cor = 'info';
                                break;
                            case 'DOCUMENTO_ENVIADO':
                                $icone = 'fas fa-upload';
                                $cor = 'success';
                                break;
                            case 'DOCUMENTO_APROVADO':
                                $icone = 'fas fa-check';
                                $cor = 'success';
                                break;
                            case 'DOCUMENTO_REPROVADO':
                                $icone = 'fas fa-times';
                                $cor = 'danger';
                                break;
                            case 'STATUS_ALTERADO':
                                $icone = 'fas fa-exchange-alt';
                                $cor = 'warning';
                                break;
                            case 'ETAPA_CONCLUIDA':
                                $icone = 'fas fa-flag-checkered';
                                $cor = 'success';
                                break;
                            default:
                                $icone = 'fas fa-info-circle';
                                $cor = 'secondary';
                        }
                    @endphp
                    
                    <div class="timeline-icon bg-{{ $cor }}">
                        <i class="{{ $icone }} text-white"></i>
                    </div>
                </div>
                
                <div class="timeline-content">
                    <div class="timeline-header">
                        <h6 class="timeline-title">{{ $historico->descricao_acao }}</h6>
                        <span class="timeline-date">
                            {{ $historico->data_acao->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    
                    <div class="timeline-body">
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-user mr-1"></i>
                                {{ $historico->usuario->name ?? 'Sistema' }}
                                @if($historico->usuario && $historico->usuario->organizacao)
                                    ({{ $historico->usuario->organizacao->nome }})
                                @endif
                            </small>
                        </div>
                        
                        @if($historico->observacao)
                            <div class="alert alert-light border-0 bg-light py-2 px-3 mb-2">
                                <small>
                                    <i class="fas fa-comment-alt mr-1"></i>
                                    <strong>Observação:</strong> {{ $historico->observacao }}
                                </small>
                            </div>
                        @endif
                        
                        @if($historico->status_anterior_id && $historico->status_novo_id)
                            <div class="status-transition mb-2">
                                <small class="text-muted">Mudança de status:</small>
                                <div class="d-flex align-items-center mt-1">
                                    <span class="badge badge-secondary badge-sm">
                                        {{ $historico->statusAnterior->nome }}
                                    </span>
                                    <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                    <span class="badge badge-{{ $historico->statusNovo->codigo === 'APROVADO' ? 'success' : ($historico->statusNovo->codigo === 'REPROVADO' ? 'danger' : 'warning') }} badge-sm">
                                        {{ $historico->statusNovo->nome }}
                                    </span>
                                </div>
                            </div>
                        @endif
                        
                        @if($historico->dados_alterados)
                            @php
                                $dados = json_decode($historico->dados_alterados, true);
                            @endphp
                            @if(is_array($dados) && count($dados) > 0)
                                <div class="mt-2">
                                    <a href="#" class="btn btn-link btn-sm p-0" onclick="toggleDetalhes({{ $historico->id }})">
                                        <small><i class="fas fa-info-circle"></i> Ver detalhes</small>
                                    </a>
                                    
                                    <div id="detalhes{{ $historico->id }}" class="detalhes-historico" style="display: none;">
                                        <div class="border rounded p-2 mt-2 bg-light">
                                            <small class="text-muted">
                                                @foreach($dados as $chave => $valor)
                                                    @if($chave === 'documento_id')
                                                        <div>• Documento ID: {{ $valor }}</div>
                                                    @elseif($chave === 'tipo_documento')
                                                        <div>• Tipo: {{ $valor }}</div>
                                                    @elseif($chave === 'nome_arquivo')
                                                        <div>• Arquivo: {{ $valor }}</div>
                                                    @elseif($chave === 'motivo')
                                                        <div>• Motivo: {{ $valor }}</div>
                                                    @elseif($chave !== 'status_anterior' && $chave !== 'status_novo')
                                                        <div>• {{ ucfirst(str_replace('_', ' ', $chave)) }}: {{ $valor }}</div>
                                                    @endif
                                                @endforeach
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-4">
        <i class="fas fa-history fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Nenhum histórico encontrado</h5>
        <p class="text-muted">Esta etapa ainda não possui registros de histórico.</p>
    </div>
@endif

<style>
/* Timeline para Modal - Corrigido para evitar sobreposição */
.timeline {
    position: relative;
    padding-left: 45px; /* Aumentado para dar mais espaço aos ícones */
}

.timeline::before {
    content: '';
    position: absolute;
    left: 20px; /* Ajustado para centralizar com os ícones */
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
    z-index: 1; /* Garantir que fique atrás dos ícones */
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
    min-height: 60px; /* Altura mínima para evitar sobreposição */
}

.timeline-item-first .timeline-marker {
    margin-top: 0;
}

.timeline-marker {
    position: absolute;
    left: -38px; /* Ajustado para o novo padding */
    top: 5px;
    z-index: 10; /* Z-index alto para ficar sempre visível */
}

.timeline-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    border: 3px solid #fff;
    box-shadow: 0 3px 6px rgba(0,0,0,0.15);
    position: relative;
    z-index: 15; /* Z-index ainda maior para garantir visibilidade */
}

.timeline-content {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    position: relative;
    margin-left: 5px; /* Pequena margem para evitar toque com os ícones */
    z-index: 5; /* Z-index menor que os ícones */
}

.timeline-content::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 15px;
    border: 8px solid transparent;
    border-right-color: #e9ecef;
    z-index: 6;
}

.timeline-content::after {
    content: '';
    position: absolute;
    left: -7px;
    top: 16px;
    border: 7px solid transparent;
    border-right-color: #f8f9fa;
    z-index: 7;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.timeline-title {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #495057;
    flex: 1;
    padding-right: 10px; /* Evitar colisão com a data */
}

.timeline-date {
    font-size: 12px;
    color: #6c757d;
    font-weight: 500;
    white-space: nowrap; /* Evitar quebra de linha na data */
}

.timeline-body {
    font-size: 13px;
    clear: both; /* Limpar floats se houver */
}

.status-transition .badge {
    font-size: 11px;
}

.detalhes-historico {
    animation: slideDown 0.3s ease;
    margin-top: 8px;
    z-index: 1; /* Z-index baixo para não interferir */
}

/* Cores específicas para cada tipo de ação */
.bg-info { background-color: #17a2b8 !important; }
.bg-success { background-color: #28a745 !important; }
.bg-danger { background-color: #dc3545 !important; }
.bg-warning { background-color: #ffc107 !important; color: #212529 !important; }
.bg-secondary { background-color: #6c757d !important; }
.bg-primary { background-color: #007bff !important; }

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
    }
    to {
        opacity: 1;
        max-height: 200px;
        padding-top: 8px;
        padding-bottom: 8px;
    }
}

/* Melhorias responsivas */
@media (max-width: 576px) {
    .timeline {
        padding-left: 35px;
    }
    
    .timeline-marker {
        left: -30px;
    }
    
    .timeline-icon {
        width: 28px;
        height: 28px;
        font-size: 11px;
    }
    
    .timeline-content {
        margin-left: 0;
        padding: 12px;
    }
    
    .timeline-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .timeline-title {
        padding-right: 0;
    }
}
</style>

<script>
function toggleDetalhes(id) {
    const detalhes = document.getElementById('detalhes' + id);
    if (detalhes.style.display === 'none') {
        detalhes.style.display = 'block';
    } else {
        detalhes.style.display = 'none';
    }
}
</script> 