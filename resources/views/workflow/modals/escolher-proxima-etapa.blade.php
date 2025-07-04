<!-- Modal Escolher Próxima Etapa -->
<div class="modal fade" id="modalEscolherProximaEtapa" tabindex="-1" role="dialog" aria-labelledby="modalEscolherProximaEtapaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h4 class="modal-title font-weight-bold" id="modalEscolherProximaEtapaLabel">
                    <i class="fas fa-route mr-3"></i>
                    Escolher Direcionamento do Fluxo
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                    <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <!-- Header informativo -->
                <div class="p-4 bg-light border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-white rounded-circle p-2 mr-3 shadow-sm">
                                    <i class="fas fa-flag-checkered text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted">Etapa Atual</h6>
                                    <h5 class="mb-0 font-weight-bold text-dark" id="etapaAtualNome"></h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="alert alert-info mb-0 py-2 px-3">
                                <i class="fas fa-info-circle mr-2"></i>
                                <small><strong>Fluxo Condicional</strong><br>
                                Escolha o direcionamento do processo</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Área principal -->
                <div class="p-4">

                    <!-- Loading -->
                    <div id="loadingOpcoes" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">Carregando opções...</span>
                        </div>
                        <p class="mt-3 text-muted font-weight-bold">Carregando opções de transição...</p>
                    </div>

                    <!-- Mensagem de erro -->
                    <div id="erroOpcoes" class="alert alert-danger border-0 shadow-sm" style="display: none;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span id="mensagemErro"></span>
                    </div>

                    <!-- Opções de transição -->
                    <div id="opcoesTransicao" style="display: none;">
                        <div class="mb-4">
                            <h5 class="text-dark font-weight-bold mb-2">
                                <i class="fas fa-route text-primary mr-2"></i>
                                Opções de Direcionamento
                            </h5>
                            <p class="text-muted mb-0">Selecione uma das opções abaixo para continuar o processo</p>
                        </div>
                        
                        <div id="listaOpcoes" class="row">
                            <!-- Opções serão carregadas via JavaScript -->
                        </div>

                        <!-- Observações opcionais -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <label for="observacoesTransicao" class="font-weight-bold text-dark mb-2">
                                <i class="fas fa-comment-alt text-primary mr-2"></i>
                                Observações sobre esta decisão
                            </label>
                            <textarea class="form-control border-0 shadow-sm" id="observacoesTransicao" rows="3" 
                                      placeholder="Adicione observações que justifiquem ou expliquem esta decisão (opcional)"></textarea>
                            <small class="text-muted mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Estas observações serão registradas no histórico da etapa para auditoria.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 p-4">
                <button type="button" class="btn btn-outline-secondary px-4 py-2" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i> Cancelar
                </button>
                <button type="button" class="btn px-4 py-2 font-weight-bold" id="btnConfirmarTransicao" disabled
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                    <i class="fas fa-play mr-2"></i> Executar Direcionamento
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos para os cards de opção */
.opcao-card {
    border: 2px solid #e9ecef;
    border-radius: 16px;
    transition: all 0.3s ease;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: visible;
}

.opcao-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #667eea;
}

.opcao-card.selecionada {
    border-color: #667eea;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    background: linear-gradient(135deg, #f8f9ff 0%, #e8ecff 100%);
}

.opcao-finalizar {
    border: 2px solid #dc3545;
    background: linear-gradient(135deg, #fff5f5 0%, #ffe6e6 100%);
}

.opcao-finalizar:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(220, 53, 69, 0.2);
    border-color: #c82333;
}

.opcao-finalizar.selecionada {
    border-color: #dc3545;
    box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
    background: linear-gradient(135deg, #fff5f5 0%, #ffe6e6 100%);
}

/* Ícone container */
.icon-container {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.opcao-card.selecionada .icon-container {
    background: #e8ecff;
}

/* Indicador de seleção */
.selection-indicator {
    width: 24px;
    height: 24px;
    color: #dee2e6;
    font-size: 20px;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.opcao-card.selecionada .selection-indicator {
    color: #667eea;
}



/* Cores específicas por tipo de operação - removidas pois agora usamos cor do status */

/* Badges com cores específicas */
.badge-nova-etapa {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge-alterar-status {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: #212529;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge-manter-status {
    background: linear-gradient(135deg, #17a2b8, #20c997);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge-finalizar {
    background: linear-gradient(135deg, #dc3545, #e91e63);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

.status-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 25px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: white;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.organizacao-badge {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    color: #495057;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.75rem;
    border: 1px solid #dee2e6;
    font-weight: 500;
}

.prioridade-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: linear-gradient(135deg, #ffc107, #ff8c00);
    color: #212529;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.65rem;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
}



/* Ícones agora usam a cor dinâmica do status */

/* Animações */
@keyframes pulse-success {
    0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
}

@keyframes pulse-warning {
    0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}

@keyframes pulse-info {
    0% { box-shadow: 0 0 0 0 rgba(23, 162, 184, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(23, 162, 184, 0); }
    100% { box-shadow: 0 0 0 0 rgba(23, 162, 184, 0); }
}

.opcao-card.selecionada {
    animation: pulse-primary 2s infinite;
}

@keyframes pulse-primary {
    0% { box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3); }
    70% { box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6), 0 0 0 10px rgba(102, 126, 234, 0); }
    100% { box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3); }
}
</style>

<script>
let globalExecucaoId = null;

// Função para abrir o modal e carregar opções
function abrirModalEscolherEtapa(execucaoId) {
    globalExecucaoId = execucaoId;
    
    // Resetar modal
    document.getElementById('loadingOpcoes').style.display = 'block';
    document.getElementById('erroOpcoes').style.display = 'none';
    document.getElementById('opcoesTransicao').style.display = 'none';
    document.getElementById('btnConfirmarTransicao').disabled = true;
    document.getElementById('observacoesTransicao').value = '';
    
    // Abrir modal
    $('#modalEscolherProximaEtapa').modal('show');
    
    // Carregar opções
    carregarOpcoesTransicao(execucaoId);
}

// Carregar opções de transição via AJAX
function carregarOpcoesTransicao(execucaoId) {
    fetch(`/workflow/execucao/${execucaoId}/opcoes-transicao`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('loadingOpcoes').style.display = 'none';
            
            if (data.success) {
                // Exibir etapa atual
                document.getElementById('etapaAtualNome').textContent = data.etapa_atual.nome;
                
                // Renderizar opções
                renderizarOpcoes(data.opcoes);
                document.getElementById('opcoesTransicao').style.display = 'block';
            } else {
                // Mostrar erro
                document.getElementById('mensagemErro').textContent = data.message;
                document.getElementById('erroOpcoes').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar opções:', error);
            document.getElementById('loadingOpcoes').style.display = 'none';
            document.getElementById('mensagemErro').textContent = 'Erro de comunicação com o servidor';
            document.getElementById('erroOpcoes').style.display = 'block';
        });
}

// Renderizar opções de transição
function renderizarOpcoes(opcoes) {
    const container = document.getElementById('listaOpcoes');
    container.innerHTML = '';
    
    opcoes.forEach((opcao, index) => {
        // Determinar classes CSS baseadas no tipo de operação
        const classeOperacao = opcao.tipo_operacao === 'finalizar_projeto' ? 'opcao-finalizar' :
                               opcao.tipo_operacao === 'iniciar_etapa' ? 'opcao-nova-etapa' :
                               opcao.tipo_operacao === 'manter_status' ? 'opcao-manter-status' : 'opcao-alterar-status';
        
        const classeBadge = opcao.tipo_operacao === 'finalizar_projeto' ? 'badge-finalizar' :
                           opcao.tipo_operacao === 'iniciar_etapa' ? 'badge-nova-etapa' :
                           opcao.tipo_operacao === 'manter_status' ? 'badge-manter-status' : 'badge-alterar-status';
        
        const classeIcone = opcao.tipo_operacao === 'iniciar_etapa' ? 'icone-nova-etapa' :
                           opcao.tipo_operacao === 'manter_status' ? 'icone-manter-status' : 'icone-alterar-status';
        
        const icone = opcao.tipo_operacao === 'finalizar_projeto' ? 'fa-flag-checkered' :
                     opcao.tipo_operacao === 'iniciar_etapa' ? 'fa-arrow-right' :
                     opcao.tipo_operacao === 'manter_status' ? 'fa-redo' : 'fa-edit';
        
        const textoBadge = opcao.tipo_operacao === 'finalizar_projeto' ? 'Finalizar Projeto' :
                          opcao.tipo_operacao === 'iniciar_etapa' ? 'Nova Etapa' :
                          opcao.tipo_operacao === 'manter_status' ? 'Manter Status' : 'Alterar Status';
        
        // Determinar cor do status
        const corStatus = opcao.status_cor || '#6c757d';
        
        const opcaoHtml = `
            <div class="col-md-6 mb-4">
                <div class="card opcao-card ${classeOperacao} h-100" onclick="selecionarOpcao(${index})" 
                     style="border-left: 5px solid ${corStatus};"
                     data-transicao-id="${opcao.transicao_id || ''}"
                     data-status-id="${opcao.status_id || ''}"
                     data-etapa-destino-id="${opcao.etapa_destino_id || ''}"
                     data-tipo-operacao="${opcao.tipo_operacao || ''}"
                     data-etapa-destino-nome="${opcao.etapa_destino_nome || ''}"
                     data-status-nome="${opcao.status_nome || ''}">
                    <input type="radio" name="opcaoTransicao" value="${index}" 
                           data-opcao='${JSON.stringify(opcao)}' onchange="habilitarBotao()" style="display: none;">
                    
                    <div class="card-header bg-transparent border-0 pb-0">
                        <div class="d-flex align-items-center">
                            <div class="icon-container mr-3" style="background-color: ${corStatus}20;">
                                <i class="fas ${icone}" style="color: ${corStatus};"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-1 font-weight-bold">${opcao.etapa_destino_nome}</h6>
                                <span class="${classeBadge}">${textoBadge}</span>
                            </div>
                            <div class="selection-indicator">
                                <i class="fas fa-circle-check"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-3">
                        <div class="status-info mb-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted small">Status:</span>
                                <span class="status-badge" style="background-color: ${corStatus}; color: white;">
                                    ${opcao.status_nome}
                                </span>
                            </div>
                        </div>
                        
                        <p class="card-text text-muted small mb-3">${opcao.descricao}</p>
                        
                        <div class="card-footer-info pt-2 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="organizacao-badge">
                                    <i class="fas fa-building mr-1"></i>
                                    ${opcao.organizacao_executora}
                                </span>
                                <small class="text-success">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    ${opcao.status_condicao}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.innerHTML += opcaoHtml;
    });
}

// Selecionar uma opção
function selecionarOpcao(index) {
    // Remover seleção anterior
    document.querySelectorAll('.opcao-card').forEach(el => {
        el.classList.remove('selecionada');
    });
    
    // Selecionar nova opção
    const opcaoElement = document.querySelectorAll('.opcao-card')[index];
    opcaoElement.classList.add('selecionada');
    
    // Marcar radio button
    const radioButton = opcaoElement.querySelector('input[type="radio"]');
    radioButton.checked = true;
    
    // Habilitar botão
    habilitarBotao();
}

// Habilitar botão de confirmação
function habilitarBotao() {
    const opcaoSelecionada = document.querySelector('input[name="opcaoTransicao"]:checked');
    document.getElementById('btnConfirmarTransicao').disabled = !opcaoSelecionada;
}

// Executar transição
function executarTransicao() {
    const $selectedCard = $('.opcao-card.selecionada');
    if ($selectedCard.length === 0) {
        Swal.fire('Atenção', 'Por favor, selecione uma opção de direcionamento.', 'warning');
        return;
    }

    const transicaoId = $selectedCard.data('transicao-id');
    const statusId = $selectedCard.data('status-id');
    const etapaDestinoId = $selectedCard.data('etapa-destino-id');
    let tipoOperacao = $selectedCard.data('tipo-operacao'); 
    const observacoes = $('#observacoesTransicao').val();

    if (transicaoId === 'finalizar') {
        tipoOperacao = 'finalizar_projeto';
    }

    console.log("Dados para envio:", { transicaoId, statusId, etapaDestinoId, tipoOperacao });

    // Exibe um alerta de confirmação customizado
    Swal.fire({
        title: 'Confirmar Direcionamento',
        text: `Deseja direcionar o processo para a etapa "${$selectedCard.data('etapa-destino-nome')}" com status "${$selectedCard.data('status-nome')}" e tipo de operação "${tipoOperacao}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '✓ Confirmar',
        cancelButtonText: '✕ Cancelar',
        buttonsStyling: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Executando...',
                text: 'Por favor aguarde',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Executar transição
            fetch(`/workflow/execucao/${globalExecucaoId}/executar-transicao`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    transicao_id: transicaoId === 'finalizar' ? null : transicaoId,
                    status_id: statusId,
                    etapa_destino_id: etapaDestinoId,
                    tipo_operacao: tipoOperacao,
                    observacoes: observacoes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Fechar modal e recarregar página
                        $('#modalEscolherProximaEtapa').modal('hide');
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Erro', data.message || 'Erro ao executar transição', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire('Erro', 'Erro de comunicação com o servidor', 'error');
            });
        }
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Botão de confirmar transição
    document.getElementById('btnConfirmarTransicao').addEventListener('click', executarTransicao);
});
</script>
