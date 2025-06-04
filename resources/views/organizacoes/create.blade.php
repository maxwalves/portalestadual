@extends('adminlte::page')

@section('title', 'Nova Organização')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-plus text-success"></i>
                    Nova Organização
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('organizacoes.index') }}">Organizações</a></li>
                    <li class="breadcrumb-item active">Criar</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-plus-circle"></i>
                            Criar Nova Organização
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-success">Novo Registro</span>
                        </div>
                    </div>
                    <form action="{{ route('organizacoes.store') }}" method="POST" id="organizacao-form">
                        @csrf
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
                                               value="{{ old('nome') }}" 
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
                                            <option value="1" {{ old('is_ativo') == '1' ? 'selected' : '' }}>
                                                <i class="fas fa-check-circle"></i> Ativo
                                            </option>
                                            <option value="0" {{ old('is_ativo') == '0' ? 'selected' : '' }}>
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
                                            Organizações ativas podem ser utilizadas no sistema
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
                                               value="{{ now()->format('d/m/Y H:i') }}" 
                                               readonly>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Data e hora atuais (preenchida automaticamente)
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-save"></i>
                                        Salvar Organização
                                    </button>
                                </div>
                                <div class="col-md-6">
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
.card-outline.card-success {
    border-top: 3px solid #28a745;
}
.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}
.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}
.select2-container--bootstrap4 .select2-selection {
    height: calc(2.25rem + 2px);
    border: 1px solid #ced4da;
}
.select2-container--bootstrap4 .select2-selection:focus {
    border-color: #28a745;
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
</style>
@stop 