@extends('adminlte::page')

@section('title', 'Criar Termo de Adesão')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-plus text-success"></i>
                    Criar Termo de Adesão
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('termos-adesao.index') }}">Termos de Adesão</a></li>
                    <li class="breadcrumb-item active">Criar</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-edit"></i>
                            Dados do Termo de Adesão
                        </h3>
                    </div>
                    <form action="{{ route('termos-adesao.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Erro!</strong> Verifique os campos abaixo:
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="descricao" class="form-label">
                                            <i class="fas fa-file-contract text-primary"></i>
                                            Descrição *
                                        </label>
                                        <input type="text" 
                                               name="descricao" 
                                               id="descricao" 
                                               class="form-control @error('descricao') is-invalid @enderror" 
                                               value="{{ old('descricao') }}"
                                               placeholder="Descreva o termo de adesão..."
                                               required>
                                        @error('descricao')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="data_criacao" class="form-label">
                                            <i class="fas fa-calendar text-info"></i>
                                            Data de Criação *
                                        </label>
                                        <input type="date" 
                                               name="data_criacao" 
                                               id="data_criacao" 
                                               class="form-control @error('data_criacao') is-invalid @enderror" 
                                               value="{{ old('data_criacao') }}"
                                               required>
                                        @error('data_criacao')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="organizacao_id" class="form-label">
                                            <i class="fas fa-building text-warning"></i>
                                            Organização *
                                        </label>
                                        <select name="organizacao_id" 
                                                id="organizacao_id" 
                                                class="form-control @error('organizacao_id') is-invalid @enderror" 
                                                required>
                                            <option value="">Selecione uma organização</option>
                                            @foreach($organizacoes as $organizacao)
                                                <option value="{{ $organizacao->id }}" {{ old('organizacao_id') == $organizacao->id ? 'selected' : '' }}>
                                                    {{ $organizacao->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('organizacao_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="arquivo" class="form-label">
                                            <i class="fas fa-paperclip text-success"></i>
                                            Arquivo do Termo
                                        </label>
                                        <div class="custom-file">
                                            <input type="file" 
                                                   name="arquivo" 
                                                   id="arquivo" 
                                                   class="custom-file-input @error('arquivo') is-invalid @enderror" 
                                                   accept=".pdf,.doc,.docx">
                                            <label class="custom-file-label" for="arquivo">Escolher arquivo...</label>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Formatos aceitos: PDF, DOC, DOCX (máx. 10MB)
                                        </small>
                                        @error('arquivo')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i>
                                        Salvar Termo
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary ml-2">
                                        <i class="fas fa-undo"></i>
                                        Limpar
                                    </button>
                                </div>
                                <div class="col-md-6 text-right">
                                    <a href="{{ route('termos-adesao.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i>
                                        Voltar à Lista
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card de Ajuda -->
            <div class="col-lg-4">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-question-circle"></i>
                            Ajuda
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="callout callout-info">
                            <h6><i class="fas fa-lightbulb"></i> Dicas</h6>
                            <ul class="mb-0">
                                <li><strong>Descrição:</strong> Use um título claro e descritivo</li>
                                <li><strong>Data:</strong> Informe a data de criação do termo</li>
                                <li><strong>Organização:</strong> Selecione a organização responsável</li>
                                <li><strong>Arquivo:</strong> Anexe o documento oficial em PDF</li>
                            </ul>
                        </div>
                        
                        <div class="callout callout-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Importante</h6>
                            <p class="mb-0">
                                Campos marcados com <strong>*</strong> são obrigatórios.
                                O arquivo deve estar em formato PDF, DOC ou DOCX.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Custom file input
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });

    // Select2 para organizações
    $('#organizacao_id').select2({
        theme: 'bootstrap4',
        placeholder: 'Selecione uma organização',
        allowClear: true
    });
});
</script>
@stop

@section('css')
<style>
.card-outline.card-primary {
    border-top: 3px solid #007bff;
}
.card-outline.card-info {
    border-top: 3px solid #17a2b8;
}
.form-label {
    font-weight: 600;
    color: #495057;
}
.custom-file-label::after {
    content: 'Procurar';
}
.callout {
    border-radius: .25rem;
    border-left: 5px solid #17a2b8;
}
.callout-warning {
    border-left-color: #ffc107;
}
.is-invalid {
    border-color: #dc3545;
}
.invalid-feedback {
    display: block;
}
</style>
@stop 