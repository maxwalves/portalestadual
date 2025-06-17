<!-- Modal Concluir Etapa -->
<div class="modal fade" id="modalConcluirEtapa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title text-white">
                    <i class="fas fa-check-circle"></i>
                    Concluir Etapa
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formConcluirEtapa">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="concluirExecucaoId" name="execucao_id">
                    
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Concluir Etapa</strong><br>
                        Todos os documentos foram aprovados. Você pode agora concluir esta etapa do workflow.
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>O que acontece após a conclusão:</strong>
                        <ul class="mb-0 mt-2">
                            <li>A etapa será marcada como "Aprovada"</li>
                            <li>A próxima etapa do workflow poderá ser iniciada</li>
                            <li>Não será mais possível alterar documentos desta etapa</li>
                        </ul>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacoesConclusao">Observações da Conclusão</label>
                        <textarea class="form-control" id="observacoesConclusao" name="observacoes" rows="3" 
                                  placeholder="Observações sobre a conclusão da etapa (opcional)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="confirmarConclusao" required>
                            <label class="custom-control-label" for="confirmarConclusao">
                                Confirmo que todos os documentos foram revisados e a etapa pode ser concluída
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Concluir Etapa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Submit do formulário de conclusão
    $('#formConcluirEtapa').on('submit', function(e) {
        e.preventDefault();
        
        let execucaoId = $('#concluirExecucaoId').val();
        let observacoes = $('#observacoesConclusao').val();
        
        // Confirmação adicional
        Swal.fire({
            title: 'Confirmar Conclusão',
            text: 'Tem certeza que deseja concluir esta etapa? Esta ação não poderá ser desfeita.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, concluir',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(`/workflow/execucao/${execucaoId}/concluir`, {
                    _token: '{{ csrf_token() }}',
                    observacoes: observacoes
                })
                .done(function(response) {
                    Swal.fire('Etapa Concluída!', response.message, 'success')
                        .then(() => {
                            $('#modalConcluirEtapa').modal('hide');
                            location.reload();
                        });
                })
                .fail(function(xhr) {
                    let message = xhr.responseJSON?.error || 'Erro ao concluir etapa';
                    Swal.fire('Erro!', message, 'error');
                });
            }
        });
    });
    
    // Limpar formulário ao fechar modal
    $('#modalConcluirEtapa').on('hidden.bs.modal', function() {
        $('#formConcluirEtapa')[0].reset();
    });
});
</script> 