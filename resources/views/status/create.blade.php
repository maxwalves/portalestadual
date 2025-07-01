@extends('adminlte::page')

@section('title', 'Novo Status')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-plus"></i> Novo Status</h1>
        <a href="{{ route('status.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Informações do Status
                </h3>
            </div>
            
            <form action="{{ route('status.store') }}" method="POST">
                @csrf
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="codigo">Código <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('codigo') is-invalid @enderror" 
                                       id="codigo" 
                                       name="codigo" 
                                       value="{{ old('codigo') }}" 
                                       maxlength="50"
                                       placeholder="Ex: NOVO_STATUS"
                                       required>
                                @error('codigo')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">
                                    Código único em MAIÚSCULAS, sem espaços (use _ ou -)
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome">Nome <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('nome') is-invalid @enderror" 
                                       id="nome" 
                                       name="nome" 
                                       value="{{ old('nome') }}" 
                                       maxlength="100"
                                       placeholder="Ex: Novo Status"
                                       required>
                                @error('nome')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="categoria">Categoria <span class="text-danger">*</span></label>
                                <select class="form-control @error('categoria') is-invalid @enderror" 
                                        id="categoria" 
                                        name="categoria" 
                                        required>
                                    <option value="">Selecione uma categoria</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat }}" {{ old('categoria') == $cat ? 'selected' : '' }}>
                                            {{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categoria')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cor">Cor</label>
                                <input type="color" 
                                       class="form-control @error('cor') is-invalid @enderror" 
                                       id="cor" 
                                       name="cor" 
                                       value="{{ old('cor', '#6c757d') }}"
                                       style="height: 38px;">
                                @error('cor')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">
                                    Cor para exibição do status
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ordem">Ordem <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('ordem') is-invalid @enderror" 
                                       id="ordem" 
                                       name="ordem" 
                                       value="{{ old('ordem', 0) }}" 
                                       min="0"
                                       required>
                                @error('ordem')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">
                                    Ordem de exibição (menor aparece primeiro)
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="icone">Ícone (FontAwesome)</label>
                                <input type="text" 
                                       class="form-control @error('icone') is-invalid @enderror" 
                                       id="icone" 
                                       name="icone" 
                                       value="{{ old('icone') }}" 
                                       maxlength="50"
                                       placeholder="Ex: fa-check, fa-times, fa-clock">
                                @error('icone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">
                                    Ícone FontAwesome (ex: fa-check, fa-times, fa-clock)
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           id="is_ativo" 
                                           name="is_ativo" 
                                           value="1" 
                                           {{ old('is_ativo', '1') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_ativo">
                                        Status Ativo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea class="form-control @error('descricao') is-invalid @enderror" 
                                  id="descricao" 
                                  name="descricao" 
                                  rows="3"
                                  placeholder="Descrição detalhada do status...">{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Status
                    </button>
                    <a href="{{ route('status.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.form-group label span.text-danger {
    font-size: 0.8em;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Gerar código automaticamente baseado no nome
    $('#nome').on('input', function() {
        var nome = $(this).val();
        var codigo = nome.toUpperCase()
                         .replace(/[^A-Z0-9\s]/g, '')
                         .replace(/\s+/g, '_');
        $('#codigo').val(codigo);
    });
    
    // Preview da cor
    $('#cor').on('change', function() {
        var cor = $(this).val();
        $(this).css('border-color', cor);
    });
});
</script>
@stop 