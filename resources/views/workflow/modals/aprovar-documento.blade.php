<!-- Modal Aprovar Documento -->
<div class="modal fade" id="modalAprovarDocumento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title text-white">
                    <i class="fas fa-check-circle"></i>
                    Aprovar Documento
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formAprovarDocumento">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="aprovarDocumentoId" name="documento_id">
                    
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Aprovar Documento</strong><br>
                        Ao aprovar, você confirma que o documento está correto e atende aos requisitos.
                    </div>
                    
                    <div class="form-group">
                        <label for="observacoesAprovacao">Observações da Aprovação</label>
                        <textarea class="form-control" id="observacoesAprovacao" name="observacoes" rows="3" 
                                  placeholder="Observações sobre a aprovação (opcional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Aprovar Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 