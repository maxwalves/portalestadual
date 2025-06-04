@extends('adminlte::page')

@section('title', 'Editar Organização')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-edit text-warning"></i>
                    Editar Organização
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('organizacoes.index') }}">Organizações</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-edit"></i>
                            Editar Organização
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-warning">ID: {{ $organizacao->id }}</span>
                        </div>
                    </div>
                    <form action="{{ route('organizacoes.update', $organizacao) }}" method="POST" id="organizacao-form">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="nome" class="form-label">
                                            <i class="fas fa-building text-primary"></i>
                                            Nome da Organização <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               name="nome" 
                                               id="nome" 
                                               class="form-control @error('nome') is-invalid @enderror" 
                                               value="{{ old('nome', $organizacao->nome) }}" 
                                               placeholder="Digite o nome da organização..."
                                               required>
                                        @error('nome')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Forneça um nome claro e identificável para a organização
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="is_ativo" class="form-label">
                                            <i class="fas fa-toggle-on text-success"></i>
                                            Status da Organização <span class="text-danger">*</span>
                                        </label>
                                        <select name="is_ativo" 
                                                id="is_ativo" 
                                                class="form-control select2 @error('is_ativo') is-invalid @enderror" 
                                                required>
                                            <option value="">Selecione o status</option>
                                            <option value="1" {{ old('is_ativo', $organizacao->is_ativo) == '1' ? 'selected' : '' }}>
                                                <i class="fas fa-check-circle"></i> Ativo
                                            </option>
                                            <option value="0" {{ old('is_ativo', $organizacao->is_ativo) == '0' ? 'selected' : '' }}>
                                                <i class="fas fa-times-circle"></i> Inativo
                                            </option>
                                        </select>
                                        @error('is_ativo')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Status atual: <strong>{{ $organizacao->is_ativo ? 'Ativo' : 'Inativo' }}</strong>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-calendar text-secondary"></i>
                                            Data de Criação
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               value="{{ $organizacao->created_at->format('d/m/Y H:i:s') }}" 
                                               readonly>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Data de criação do registro (não editável)
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Informações de Auditoria -->
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle"></i> Informações de Registro</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small>
                                                    <strong>Criado em:</strong> {{ $organizacao->created_at->format('d/m/Y H:i:s') }}
                                                </small>
                                            </div>
                                            <div class="col-md-6">
                                                <small>
                                                    <strong>Última atualização:</strong> {{ $organizacao->updated_at->format('d/m/Y H:i:s') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-warning btn-block">
                                        <i class="fas fa-save"></i>
                                        Atualizar Organização
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ route('organizacoes.show', $organizacao) }}" class="btn btn-info btn-block">
                                        <i class="fas fa-eye"></i>
                                        Visualizar
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ route('organizacoes.index') }}" class="btn btn-secondary btn-block">
                                        <i class="fas fa-times"></i>
                                        Cancelar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: function() {
            return $(this).attr('placeholder') || 'Selecione uma opção';
        },
        allowClear: true
    });
    
    // Validação do formulário
    $('#organizacao-form').on('submit', function(e) {
        let isValid = true;
        
        // Remover classes de erro anteriores
        $('.form-control').removeClass('is-invalid');
        
        // Validar campos obrigatórios
        $('input[required], select[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            toastr.error('Por favor, preencha todos os campos obrigatórios.');
        }
    });
    
    // Feedback visual em tempo real
    $('input, select').on('change blur', function() {
        if ($(this).attr('required') && $(this).val()) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }
    });
    
    // Marcar campos preenchidos como válidos
    $('input[required], select[required]').each(function() {
        if ($(this).val()) {
            $(this).addClass('is-valid');
        }
    });
    
    // Formatar nome automaticamente
    $('#nome').on('input', function() {
        let value = $(this).val();
        // Capitalizar primeira letra de cada palavra
        value = value.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
        $(this).val(value);
    });
});
</script>
@stop

@section('css')
<style>
.card-outline.card-warning {
    border-top: 3px solid #ffc107;
}
.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}
.form-control:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}
.select2-container--bootstrap4 .select2-selection {
    height: calc(2.25rem + 2px);
    border: 1px solid #ced4da;
}
.select2-container--bootstrap4 .select2-selection:focus {
    border-color: #ffc107;
}
.invalid-feedback {
    display: block;
}
.text-danger {
    color: #dc3545 !important;
}
.btn-block {
    margin-bottom: 0;
}
.alert-info {
    background-color: #e8f4fd;
    border-color: #b8daff;
    color: #004085;
}
</style>
@stop 