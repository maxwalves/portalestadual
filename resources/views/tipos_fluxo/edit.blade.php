@extends('adminlte::page')

@section('title', 'Editar Tipo de Fluxo')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-edit text-warning"></i>
                    Editar Tipo de Fluxo
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tipos-fluxo.index') }}">Tipos de Fluxo</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-edit"></i>
                            Editar Tipo de Fluxo: {{ $tipoFluxo->nome }}
                        </h3>
                    </div>
                    <form action="{{ route('tipos-fluxo.update', $tipoFluxo) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <h5><i class="icon fas fa-ban"></i> Erro!</h5>
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nome">
                                            <i class="fas fa-route"></i>
                                            Nome <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('nome') is-invalid @enderror" 
                                               id="nome" 
                                               name="nome" 
                                               value="{{ old('nome', $tipoFluxo->nome) }}" 
                                               placeholder="Digite o nome do tipo de fluxo"
                                               required>
                                        @error('nome')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="categoria">
                                            <i class="fas fa-tags"></i>
                                            Categoria
                                        </label>
                                        <select class="form-control @error('categoria') is-invalid @enderror" 
                                                id="categoria" 
                                                name="categoria">
                                            <option value="">Selecione uma categoria</option>
                                            <option value="ESCOLA" {{ old('categoria', $tipoFluxo->categoria) == 'ESCOLA' ? 'selected' : '' }}>Escola</option>
                                            <option value="SAUDE" {{ old('categoria', $tipoFluxo->categoria) == 'SAUDE' ? 'selected' : '' }}>Saúde</option>
                                            <option value="SEGURANCA" {{ old('categoria', $tipoFluxo->categoria) == 'SEGURANCA' ? 'selected' : '' }}>Segurança</option>
                                            <option value="INFRAESTRUTURA" {{ old('categoria', $tipoFluxo->categoria) == 'INFRAESTRUTURA' ? 'selected' : '' }}>Infraestrutura</option>
                                            <option value="HABITACAO" {{ old('categoria', $tipoFluxo->categoria) == 'HABITACAO' ? 'selected' : '' }}>Habitação</option>
                                            <option value="ESPORTE" {{ old('categoria', $tipoFluxo->categoria) == 'ESPORTE' ? 'selected' : '' }}>Esporte</option>
                                            <option value="CULTURA" {{ old('categoria', $tipoFluxo->categoria) == 'CULTURA' ? 'selected' : '' }}>Cultura</option>
                                            <option value="OUTRO" {{ old('categoria', $tipoFluxo->categoria) == 'OUTRO' ? 'selected' : '' }}>Outro</option>
                                        </select>
                                        @error('categoria')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="versao">
                                            <i class="fas fa-code-branch"></i>
                                            Versão <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('versao') is-invalid @enderror" 
                                               id="versao" 
                                               name="versao" 
                                               value="{{ old('versao', $tipoFluxo->versao) }}" 
                                               placeholder="Ex: 1.0"
                                               required>
                                        @error('versao')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="descricao">
                                            <i class="fas fa-align-left"></i>
                                            Descrição
                                        </label>
                                        <textarea class="form-control @error('descricao') is-invalid @enderror" 
                                                  id="descricao" 
                                                  name="descricao" 
                                                  rows="4" 
                                                  placeholder="Digite uma descrição detalhada do tipo de fluxo">{{ old('descricao', $tipoFluxo->descricao) }}</textarea>
                                        @error('descricao')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" 
                                                   class="custom-control-input" 
                                                   id="ativo" 
                                                   name="ativo" 
                                                   value="1" 
                                                   {{ old('ativo', $tipoFluxo->is_ativo) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="ativo">
                                                <i class="fas fa-toggle-on"></i>
                                                Tipo de fluxo ativo
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Apenas tipos de fluxo ativos podem ser associados a novas ações
                                        </small>
                                    </div>
                                </div>
                            </div>

                            @if($tipoFluxo->acoes()->count() > 0)
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            <h5><i class="icon fas fa-info"></i> Informação!</h5>
                                            Este tipo de fluxo possui <strong>{{ $tipoFluxo->acoes()->count() }}</strong> ação(ões) associada(s).
                                            Desativar este tipo de fluxo não afetará as ações já criadas.
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save"></i>
                                        Atualizar Tipo de Fluxo
                                    </button>
                                    <a href="{{ route('tipos-fluxo.index') }}" class="btn btn-secondary ml-2">
                                        <i class="fas fa-times"></i>
                                        Cancelar
                                    </a>
                                    <a href="{{ route('tipos-fluxo.show', $tipoFluxo) }}" class="btn btn-info ml-2">
                                        <i class="fas fa-eye"></i>
                                        Visualizar
                                    </a>
                                </div>
                                <div class="col-md-6 text-right">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Campos marcados com <span class="text-danger">*</span> são obrigatórios
                                    </small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
</style>
@stop 