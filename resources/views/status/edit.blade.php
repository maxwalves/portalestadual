@extends('adminlte::page')

@section('title', 'Editar Status')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-edit"></i> Editar Status - {{ $status->nome }}</h1>
        <div>
            <a href="{{ route('status.show', $status) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Visualizar
            </a>
            <a href="{{ route('status.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Informações do Status
                </h3>
            </div>
            
            <form action="{{ route('status.update', $status) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="codigo">Código <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('codigo') is-invalid @enderror" 
                                       id="codigo" 
                                       name="codigo" 
                                       value="{{ old('codigo', $status->codigo) }}" 
                                       maxlength="50"
                                       required>
                                @error('codigo')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome">Nome <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('nome') is-invalid @enderror" 
                                       id="nome" 
                                       name="nome" 
                                       value="{{ old('nome', $status->nome) }}" 
                                       maxlength="100"
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
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat }}" {{ old('categoria', $status->categoria) == $cat ? 'selected' : '' }}>
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
                                       value="{{ old('cor', $status->cor ?: '#6c757d') }}"
                                       style="height: 38px;">
                                @error('cor')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ordem">Ordem <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('ordem') is-invalid @enderror" 
                                       id="ordem" 
                                       name="ordem" 
                                       value="{{ old('ordem', $status->ordem) }}" 
                                       min="0"
                                       required>
                                @error('ordem')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
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
                                       value="{{ old('icone', $status->icone) }}" 
                                       maxlength="50"
                                       placeholder="Ex: fa-check, fa-times, fa-clock">
                                @error('icone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
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
                                           {{ old('is_ativo', $status->is_ativo) ? 'checked' : '' }}>
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
                                  rows="3">{{ old('descricao', $status->descricao) }}</textarea>
                        @error('descricao')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Atualizar Status
                    </button>
                    <a href="{{ route('status.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Card de informações de uso -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar"></i> Informações de Uso
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6 col-md-3">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-play"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Execuções Ativas</span>
                                <span class="info-box-number">{{ $status->execucaoEtapas()->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-route"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Transições</span>
                                <span class="info-box-number">{{ $status->transicoesCondicao()->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-history"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Históricos</span>
                                <span class="info-box-number">{{ $status->historicosStatusNovo()->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-cogs"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Etapas Vinculadas</span>
                                <span class="info-box-number">{{ $status->etapaStatusOpcoes()->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($status->execucaoEtapas()->count() > 0 || $status->transicoesCondicao()->count() > 0)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Atenção:</strong> Este status está sendo usado no sistema. 
                    Cuidado ao fazer alterações que possam afetar o funcionamento.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.form-group label span.text-danger {
    font-size: 0.8em;
}
.info-box {
    margin-bottom: 15px;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Preview da cor
    $('#cor').on('change', function() {
        var cor = $(this).val();
        $(this).css('border-color', cor);
    });
    
    // Aplicar cor atual na borda do campo
    var corAtual = $('#cor').val();
    if (corAtual) {
        $('#cor').css('border-color', corAtual);
    }
});
</script>
@stop 