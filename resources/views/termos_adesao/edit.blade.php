@extends('adminlte::page')

@section('title', 'Editar Termo de Adesão')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-edit text-warning"></i>
                    Editar Termo de Adesão
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('termos-adesao.index') }}">Termos de Adesão</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-edit"></i>
                            Dados do Termo de Adesão
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">ID: {{ $termo->id }}</span>
                        </div>
                    </div>
                    <form action="{{ route('termos-adesao.update', $termo) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
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
                                               value="{{ old('descricao', $termo->descricao) }}"
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
                                               value="{{ old('data_criacao', $termo->data_criacao) }}"
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
                                                <option value="{{ $organizacao->id }}" {{ old('organizacao_id', $termo->organizacao_id) == $organizacao->id ? 'selected' : '' }}>
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
                                    @if($termo->path_arquivo)
                                        <div class="alert alert-info">
                                            <i class="fas fa-file-alt"></i>
                                            <strong>Arquivo atual:</strong> {{ basename($termo->path_arquivo) }}
                                            <a href="{{ Storage::url($termo->path_arquivo) }}" target="_blank" class="btn btn-sm btn-outline-info ml-2">
                                                <i class="fas fa-eye"></i> Visualizar
                                            </a>
                                        </div>
                                    @endif
                                    
                                    <div class="form-group">
                                        <label for="arquivo" class="form-label">
                                            <i class="fas fa-paperclip text-success"></i>
                                            {{ $termo->path_arquivo ? 'Novo Arquivo (opcional)' : 'Arquivo do Termo' }}
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
                                            @if($termo->path_arquivo)
                                                <br>Deixe em branco para manter o arquivo atual.
                                            @endif
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
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save"></i>
                                        Atualizar Termo
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary ml-2">
                                        <i class="fas fa-undo"></i>
                                        Restaurar
                                    </button>
                                </div>
                                <div class="col-md-6 text-right">
                                    <a href="{{ route('termos-adesao.show', $termo) }}" class="btn btn-info mr-2">
                                        <i class="fas fa-eye"></i>
                                        Visualizar
                                    </a>
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
                                Se você não selecionar um novo arquivo, o arquivo atual será mantido.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card de Histórico -->
                <div class="card card-secondary card-outline mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            Histórico
                        </h3>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <i class="fas fa-calendar-plus"></i>
                            <strong>Criado em:</strong> {{ $termo->created_at ? $termo->created_at->format('d/m/Y H:i') : 'Não informado' }}
                        </small>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-calendar-edit"></i>
                            <strong>Última atualização:</strong> {{ $termo->updated_at ? $termo->updated_at->format('d/m/Y H:i') : 'Não informado' }}
                        </small>
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
.card-outline.card-warning {
    border-top: 3px solid #ffc107;
}
.card-outline.card-info {
    border-top: 3px solid #17a2b8;
}
.card-outline.card-secondary {
    border-top: 3px solid #6c757d;
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