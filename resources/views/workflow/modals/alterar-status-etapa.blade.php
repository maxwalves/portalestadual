<!-- Modal Alterar Status da Etapa -->
<div class="modal fade" id="modalAlterarStatusEtapa" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white">
                    <i class="fas fa-exchange-alt"></i>
                    Alterar Status da Etapa
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formAlterarStatusEtapa">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="alterarStatusExecucaoId" name="execucao_id">
                    
                    <div class="form-group">
                        <label for="novoStatus">Novo Status *</label>
                        <select class="form-control" id="novoStatus" name="status_id" required>
                            <option value="">Carregando opções...</option>
                        </select>
                        <small class="form-text text-muted">
                            Selecione o novo status para a etapa
                        </small>
                    </div>
                    
                    <div class="form-group" id="grupoJustificativa" style="display: none;">
                        <label for="justificativaStatus">Justificativa *</label>
                        <textarea class="form-control" id="justificativaStatus" name="justificativa" 
                                  rows="3" placeholder="Digite a justificativa para esta alteração..."></textarea>
                        <small class="form-text text-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Justificativa obrigatória para este status
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacoesStatus">Observações</label>
                        <textarea class="form-control" id="observacoesStatus" name="observacoes" 
                                  rows="3" placeholder="Observações adicionais (opcional)..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Importante:</strong> A alteração de status será registrada no histórico da etapa.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Alterar Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Script específico do modal de alterar status
$(document).ready(function() {
    // Quando o modal for aberto, carregar as opções de status
    $('#modalAlterarStatusEtapa').on('show.bs.modal', function(e) {
        const execucaoId = $('#alterarStatusExecucaoId').val();
        if (!execucaoId) {
            alert('Erro: ID da execução não encontrado');
            e.preventDefault();
            return;
        }
        
        carregarOpcoesStatus(execucaoId);
    });
    
    // Quando mudar o status, verificar se requer justificativa
    $(document).on('change', '#novoStatus', function() {
        const statusId = $(this).val();
        const opcaoSelecionada = $(this).find('option:selected');
        const requerJustificativa = opcaoSelecionada.data('requer-justificativa');
        
        // Mostrar/esconder campo de justificativa
        if (requerJustificativa) {
            $('#grupoJustificativa').show();
            $('#justificativaStatus').prop('required', true);
        } else {
            $('#grupoJustificativa').hide();
            $('#justificativaStatus').prop('required', false);
            $('#justificativaStatus').val('');
        }
        
        // Habilitar/desabilitar botão de submit
        $('#btnAlterarStatus').prop('disabled', !statusId);
    });
    
    // Reset do modal quando fechar
    $('#modalAlterarStatusEtapa').on('hidden.bs.modal', function() {
        $('#formAlterarStatusEtapa')[0].reset();
        $('#containerStatusOpcoes').hide();
        $('#loadingStatusOpcoes').show();
        $('#grupoJustificativa').hide();
        $('#btnAlterarStatus').prop('disabled', true);
        $('#novoStatus').empty().append('<option value="">Selecione o novo status</option>');
    });
});

function carregarOpcoesStatus(execucaoId) {
    $.ajax({
        url: `/workflow/execucao/${execucaoId}/opcoes-status`,
        type: 'GET',
        success: function(response) {
            console.log('Opções de status carregadas:', response);
            
            const select = $('#novoStatus');
            select.empty().append('<option value="">Selecione o novo status</option>');
            
            if (response.opcoes && response.opcoes.length > 0) {
                response.opcoes.forEach(function(opcao) {
                    const option = $('<option></option>')
                        .val(opcao.id)
                        .text(opcao.nome)
                        .data('requer-justificativa', opcao.requer_justificativa)
                        .css('color', opcao.cor || '#000');
                    
                    select.append(option);
                });
                
                $('#loadingStatusOpcoes').hide();
                $('#containerStatusOpcoes').show();
            } else {
                $('#loadingStatusOpcoes').html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Nenhuma opção de status disponível para esta etapa.
                    </div>
                `);
            }
        },
        error: function(xhr) {
            console.error('Erro ao carregar opções de status:', xhr);
            $('#loadingStatusOpcoes').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Erro ao carregar opções de status: ${xhr.responseJSON?.error || 'Erro desconhecido'}
                </div>
            `);
        }
    });
}
</script>