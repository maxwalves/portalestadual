<!-- Modal Reprovar Documento -->
<div class="modal fade" id="modalReprovarDocumento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title text-white">
                    <i class="fas fa-times-circle"></i>
                    Reprovar Documento
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formReprovarDocumento">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="reprovarDocumentoId" name="documento_id">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atenção!</strong><br>
                        Ao reprovar, o documento será devolvido para correção e a etapa voltará para o status "Devolvido".
                    </div>
                    
                    <div class="form-group">
                        <label for="motivoReprovacao">Motivo da Reprovação *</label>
                        <textarea class="form-control" id="motivoReprovacao" name="motivo_reprovacao" rows="4" 
                                  placeholder="Descreva detalhadamente o motivo da reprovação e as correções necessárias..." 
                                  required></textarea>
                        <small class="form-text text-muted">
                            Seja específico sobre o que precisa ser corrigido para facilitar a correção.
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="confirmarReprovacao" required>
                            <label class="custom-control-label" for="confirmarReprovacao">
                                Confirmo que revisei o documento e as correções solicitadas são necessárias
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times-circle"></i> Reprovar Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 