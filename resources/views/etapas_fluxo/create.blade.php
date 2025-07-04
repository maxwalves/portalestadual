@extends('adminlte::page')

@section('title', 'Nova Etapa de Fluxo')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-plus text-primary"></i>
                    Nova Etapa de Fluxo
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('etapas-fluxo.index') }}">Etapas de Fluxo</a></li>
                    <li class="breadcrumb-item active">Nova</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-plus"></i>
                            Criar Nova Etapa de Fluxo
                        </h3>
                    </div>
                    <form action="{{ route('etapas-fluxo.store') }}" method="POST">
                        @csrf
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
                                        <label for="nome_etapa">
                                            <i class="fas fa-stream"></i>
                                            Nome da Etapa <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('nome_etapa') is-invalid @enderror" id="nome_etapa" name="nome_etapa" value="{{ old('nome_etapa') }}" placeholder="Digite o nome da etapa" required>
                                        @error('nome_etapa')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tipo_fluxo_id">
                                            <i class="fas fa-route"></i>
                                            Tipo de Fluxo
                                        </label>
                                        <select class="form-control select2 @error('tipo_fluxo_id') is-invalid @enderror" id="tipo_fluxo_id" name="tipo_fluxo_id">
                                            <option value="">Genérica (não vinculada)</option>
                                            @foreach($tiposFluxo as $tipo)
                                                <option value="{{ $tipo->id }}" 
                                                    {{ (old('tipo_fluxo_id', $tipoFluxoPreSelecionado) == $tipo->id) ? 'selected' : '' }}>
                                                    {{ $tipo->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('tipo_fluxo_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <!-- Campo ordem_execucao será preenchido automaticamente -->
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="modulo_id">
                                            <i class="fas fa-cogs"></i>
                                            Módulo <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control select2 @error('modulo_id') is-invalid @enderror" id="modulo_id" name="modulo_id" required>
                                            <option value="">Selecione o módulo</option>
                                            @foreach($modulos as $modulo)
                                                <option value="{{ $modulo->id }}" {{ old('modulo_id') == $modulo->id ? 'selected' : '' }}>{{ $modulo->nome }}</option>
                                            @endforeach
                                        </select>
                                        @error('modulo_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="grupo_exigencia_id">
                                            <i class="fas fa-tags"></i>
                                            Grupo de Exigência
                                        </label>
                                        <select class="form-control select2 @error('grupo_exigencia_id') is-invalid @enderror" id="grupo_exigencia_id" name="grupo_exigencia_id">
                                            <option value="">Nenhum</option>
                                            @foreach($gruposExigencia as $grupo)
                                                <option value="{{ $grupo->id }}" {{ old('grupo_exigencia_id') == $grupo->id ? 'selected' : '' }}>{{ $grupo->nome }}</option>
                                            @endforeach
                                        </select>
                                        @error('grupo_exigencia_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="organizacao_solicitante_id">
                                            <i class="fas fa-user-tie"></i>
                                            Organização Solicitante <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control select2 @error('organizacao_solicitante_id') is-invalid @enderror" id="organizacao_solicitante_id" name="organizacao_solicitante_id" required>
                                            <option value="">Selecione a organização solicitante</option>
                                            @foreach($organizacoes as $organizacao)
                                                <option value="{{ $organizacao->id }}" {{ old('organizacao_solicitante_id') == $organizacao->id ? 'selected' : '' }}>{{ $organizacao->nome }}</option>
                                            @endforeach
                                        </select>
                                        @error('organizacao_solicitante_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="organizacao_executora_id">
                                            <i class="fas fa-users-cog"></i>
                                            Organização Executora <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control select2 @error('organizacao_executora_id') is-invalid @enderror" id="organizacao_executora_id" name="organizacao_executora_id" required>
                                            <option value="">Selecione a organização executora</option>
                                            @foreach($organizacoes as $organizacao)
                                                <option value="{{ $organizacao->id }}" {{ old('organizacao_executora_id') == $organizacao->id ? 'selected' : '' }}>{{ $organizacao->nome }}</option>
                                            @endforeach
                                        </select>
                                        @error('organizacao_executora_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="prazo_dias">
                                            <i class="fas fa-clock"></i>
                                            Prazo (dias) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control @error('prazo_dias') is-invalid @enderror" id="prazo_dias" name="prazo_dias" value="{{ old('prazo_dias', 5) }}" min="1" required>
                                        @error('prazo_dias')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tipo_prazo">
                                            <i class="fas fa-calendar-alt"></i>
                                            Tipo de Prazo <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control @error('tipo_prazo') is-invalid @enderror" id="tipo_prazo" name="tipo_prazo" required>
                                            <option value="UTEIS" {{ old('tipo_prazo', 'UTEIS') == 'UTEIS' ? 'selected' : '' }}>Dias Úteis</option>
                                            <option value="CORRIDOS" {{ old('tipo_prazo') == 'CORRIDOS' ? 'selected' : '' }}>Dias Corridos</option>
                                        </select>
                                        @error('tipo_prazo')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <!-- Espaço reservado para futuras expansões -->
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="descricao_customizada">
                                            <i class="fas fa-align-left"></i>
                                            Descrição da Etapa
                                        </label>
                                        <textarea class="form-control @error('descricao_customizada') is-invalid @enderror" id="descricao_customizada" name="descricao_customizada" rows="3" placeholder="Descreva detalhes ou instruções da etapa">{{ old('descricao_customizada') }}</textarea>
                                        @error('descricao_customizada')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        Salvar Etapa
                                    </button>
                                    <a href="{{ $tipoFluxoPreSelecionado ? route('tipos-fluxo.etapas', $tipoFluxoPreSelecionado) : route('etapas-fluxo.index') }}" class="btn btn-secondary ml-2">
                                        <i class="fas fa-times"></i>
                                        Cancelar
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
@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    });
</script>
@stop 