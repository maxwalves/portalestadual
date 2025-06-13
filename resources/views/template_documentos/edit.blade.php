@extends('adminlte::page')

@section('title', 'Editar Template de Documento')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-edit mr-2"></i>Editar Template de Documento</h1>
        <a href="{{ route('template-documentos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Voltar
        </a>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $templateDocumento->nome }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('template-documentos.update', $templateDocumento) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome" class="required">Nome do Template:</label>
                            <input type="text" 
                                   name="nome" 
                                   id="nome"
                                   class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome', $templateDocumento->nome) }}"
                                   required>
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ordem">Ordem:</label>
                            <input type="number" 
                                   name="ordem" 
                                   id="ordem"
                                   class="form-control @error('ordem') is-invalid @enderror"
                                   value="{{ old('ordem', $templateDocumento->ordem) }}"
                                   min="0">
                            @error('ordem')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="grupo_exigencia_id" class="required">Grupo de Exigência:</label>
                            <select name="grupo_exigencia_id" 
                                    id="grupo_exigencia_id" 
                                    class="form-control @error('grupo_exigencia_id') is-invalid @enderror"
                                    required>
                                <option value="">Selecione um grupo</option>
                                @foreach($gruposExigencia as $grupo)
                                    <option value="{{ $grupo->id }}" 
                                            {{ old('grupo_exigencia_id', $templateDocumento->grupo_exigencia_id) == $grupo->id ? 'selected' : '' }}>
                                        {{ $grupo->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grupo_exigencia_id')
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
                                            {{ old('tipo_documento_id', $templateDocumento->tipo_documento_id) == $tipo->id ? 'selected' : '' }}>
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
                    <label for="descricao">Descrição:</label>
                    <textarea name="descricao" 
                              id="descricao" 
                              class="form-control @error('descricao') is-invalid @enderror"
                              rows="3">{{ old('descricao', $templateDocumento->descricao) }}</textarea>
                    @error('descricao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" 
                               name="is_obrigatorio" 
                               id="is_obrigatorio" 
                               class="custom-control-input"
                               value="1"
                               {{ old('is_obrigatorio', $templateDocumento->is_obrigatorio) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_obrigatorio">
                            Template obrigatório
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="instrucoes_preenchimento">Instruções de Preenchimento:</label>
                    <textarea name="instrucoes_preenchimento" 
                              id="instrucoes_preenchimento" 
                              class="form-control @error('instrucoes_preenchimento') is-invalid @enderror"
                              rows="4">{{ old('instrucoes_preenchimento', $templateDocumento->instrucoes_preenchimento) }}</textarea>
                    @error('instrucoes_preenchimento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Atualizar Template
                    </button>
                    <a href="{{ route('template-documentos.show', $templateDocumento) }}" class="btn btn-secondary">
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