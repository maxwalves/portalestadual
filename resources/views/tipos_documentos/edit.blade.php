@extends('adminlte::page')

@section('title', 'Editar Tipo de Documento')

@section('content_header')
    <h1>Editar Tipo de Documento</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar: {{ $tipoDocumento->nome }}</h3>
            <div class="card-tools">
                <a href="{{ route('tipos-documento.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
        
        <form action="{{ route('tipos-documento.update', $tipoDocumento) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="codigo">Código <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('codigo') is-invalid @enderror" 
                                   id="codigo" 
                                   name="codigo" 
                                   value="{{ old('codigo', $tipoDocumento->codigo) }}" 
                                   placeholder="Ex: PROJ_BASICO"
                                   maxlength="50"
                                   required>
                            @error('codigo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Código único do tipo de documento (será convertido para maiúsculas)</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="categoria">Categoria</label>
                            <select class="form-control @error('categoria') is-invalid @enderror" 
                                    id="categoria" 
                                    name="categoria">
                                <option value="">Selecione uma categoria</option>
                                @foreach($categorias as $value => $label)
                                    <option value="{{ $value }}" {{ old('categoria', $tipoDocumento->categoria) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('categoria')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nome">Nome <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('nome') is-invalid @enderror" 
                           id="nome" 
                           name="nome" 
                           value="{{ old('nome', $tipoDocumento->nome) }}" 
                           placeholder="Ex: Projeto Básico"
                           maxlength="255"
                           required>
                    @error('nome')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea class="form-control @error('descricao') is-invalid @enderror" 
                              id="descricao" 
                              name="descricao" 
                              rows="3" 
                              placeholder="Descrição detalhada do tipo de documento">{{ old('descricao', $tipoDocumento->descricao) }}</textarea>
                    @error('descricao')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="extensoes_permitidas">Extensões Permitidas</label>
                            <input type="text" 
                                   class="form-control @error('extensoes_permitidas') is-invalid @enderror" 
                                   id="extensoes_permitidas" 
                                   name="extensoes_permitidas" 
                                   value="{{ old('extensoes_permitidas', $tipoDocumento->extensoes_permitidas) }}" 
                                   placeholder="Ex: pdf,doc,docx">
                            @error('extensoes_permitidas')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Separar extensões por vírgula (deixe em branco para permitir todas)</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tamanho_maximo_mb">Tamanho Máximo (MB) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('tamanho_maximo_mb') is-invalid @enderror" 
                                   id="tamanho_maximo_mb" 
                                   name="tamanho_maximo_mb" 
                                   value="{{ old('tamanho_maximo_mb', $tipoDocumento->tamanho_maximo_mb) }}" 
                                   min="1" 
                                   max="1024"
                                   required>
                            @error('tamanho_maximo_mb')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="requer_assinatura" 
                                       name="requer_assinatura" 
                                       value="1" 
                                       {{ old('requer_assinatura', $tipoDocumento->requer_assinatura) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="requer_assinatura">
                                    Requer Assinatura Digital
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_ativo" 
                                       name="is_ativo" 
                                       value="1" 
                                       {{ old('is_ativo', $tipoDocumento->is_ativo) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_ativo">
                                    Ativo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                @if($tipoDocumento->documentos()->count() > 0 || $tipoDocumento->templatesDocumento()->count() > 0)
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Informação:</h5>
                        Este tipo de documento possui:
                        @if($tipoDocumento->documentos()->count() > 0)
                            <br>• {{ $tipoDocumento->documentos()->count() }} documento(s) vinculado(s)
                        @endif
                        @if($tipoDocumento->templatesDocumento()->count() > 0)
                            <br>• {{ $tipoDocumento->templatesDocumento()->count() }} template(s) vinculado(s)
                        @endif
                        <br><small>Algumas alterações podem afetar os documentos existentes.</small>
                    </div>
                @endif
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Atualizar
                </button>
                <a href="{{ route('tipos-documento.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <a href="{{ route('tipos-documento.show', $tipoDocumento) }}" class="btn btn-info">
                    <i class="fas fa-eye"></i> Visualizar
                </a>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Converter código para maiúsculas automaticamente
        const codigoInput = document.getElementById('codigo');
        codigoInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Validar extensões permitidas
        const extensoesInput = document.getElementById('extensoes_permitidas');
        extensoesInput.addEventListener('blur', function() {
            if (this.value) {
                // Remove espaços e converte para minúsculas
                const extensoes = this.value.split(',').map(ext => ext.trim().toLowerCase());
                this.value = extensoes.join(',');
            }
        });
    });
</script>
@stop 