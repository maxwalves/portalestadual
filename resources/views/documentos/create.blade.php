@extends('adminlte::page')

@section('title', 'Novo Documento')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-alt mr-2"></i>Novo Documento</h1>
        <a href="{{ route('documentos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Voltar
        </a>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('documentos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="execucao_etapa_id" class="required">Execução de Etapa:</label>
                            <select name="execucao_etapa_id" 
                                    id="execucao_etapa_id" 
                                    class="form-control @error('execucao_etapa_id') is-invalid @enderror"
                                    required>
                                <option value="">Selecione uma execução</option>
                                @foreach($execucoesEtapa as $execucao)
                                    <option value="{{ $execucao->id }}" 
                                            {{ old('execucao_etapa_id') == $execucao->id ? 'selected' : '' }}>
                                        {{ $execucao->acao->nome ?? 'N/A' }} - {{ $execucao->etapaFluxo->nome_etapa ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('execucao_etapa_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tipo_documento_id" class="required">Tipo de Documento:</label>
                            <select name="tipo_documento_id" 
                                    id="tipo_documento_id" 
                                    class="form-control @error('tipo_documento_id') is-invalid @enderror"
                                    required>
                                <option value="">Selecione um tipo</option>
                                @foreach($tiposDocumento as $tipo)
                                    <option value="{{ $tipo->id }}" 
                                            {{ old('tipo_documento_id') == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_documento_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="arquivo" class="required">Arquivo:</label>
                    <input type="file" 
                           name="arquivo" 
                           id="arquivo"
                           class="form-control-file @error('arquivo') is-invalid @enderror"
                           required>
                    <small class="form-text text-muted">
                        Máximo 500MB. Verifique as extensões permitidas para o tipo de documento selecionado.
                    </small>
                    @error('arquivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="observacoes">Observações:</label>
                    <textarea name="observacoes" 
                              id="observacoes" 
                              class="form-control @error('observacoes') is-invalid @enderror"
                              rows="3"
                              placeholder="Informações adicionais sobre o documento...">{{ old('observacoes') }}</textarea>
                    @error('observacoes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload mr-1"></i>Enviar Documento
                    </button>
                    <a href="{{ route('documentos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('css')
<style>
.required:after {
    content: ' *';
    color: red;
}
</style>
@endsection 