@extends('adminlte::page')

@section('title', 'Editar Documento')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-edit mr-2"></i>Editar Documento</h1>
        <a href="{{ route('documentos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Voltar
        </a>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $documento->nome_arquivo }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('documentos.update', $documento) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="observacoes">Observações:</label>
                    <textarea name="observacoes" 
                              id="observacoes" 
                              class="form-control @error('observacoes') is-invalid @enderror"
                              rows="4"
                              placeholder="Informações adicionais sobre o documento...">{{ old('observacoes', $documento->observacoes) }}</textarea>
                    @error('observacoes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="data_validade">Data de Validade:</label>
                    <input type="date" 
                           name="data_validade" 
                           id="data_validade"
                           class="form-control @error('data_validade') is-invalid @enderror"
                           value="{{ old('data_validade', $documento->data_validade?->format('Y-m-d')) }}">
                    @error('data_validade')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Data opcional de validade do documento.
                    </small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Atualizar Documento
                    </button>
                    <a href="{{ route('documentos.show', $documento) }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection 