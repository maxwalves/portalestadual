<!-- Modal Upload Documento -->
<div class="modal fade" id="modalUploadDocumento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-upload"></i>
                    Enviar Documento
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formUploadDocumento" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="uploadExecucaoId" name="execucao_id">
                    <input type="hidden" id="uploadTipoDocumentoId" name="tipo_documento_id">
                    
                    <div class="form-group">
                        <label for="arquivo">Arquivo *</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="arquivo" name="arquivo" required>
                            <label class="custom-file-label" for="arquivo">Escolher arquivo...</label>
                        </div>
                        <small class="form-text text-muted">
                            Tamanho máximo: 50MB. Formatos aceitos: PDF, DOC, DOCX, XLS, XLSX
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacoes">Observações</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3" 
                                  placeholder="Observações sobre o documento (opcional)"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Importante:</strong> Após o envio, o documento será analisado pela organização responsável. 
                        Certifique-se de que o arquivo está correto antes de enviar.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Enviar Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 