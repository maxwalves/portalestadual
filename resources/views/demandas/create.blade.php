@extends('adminlte::page')

@section('title', 'Nova Demanda')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-plus text-success"></i>
                    Nova Demanda
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('demandas.index') }}">Demandas</a></li>
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
                            Criar Nova Demanda
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-success">Novo Registro</span>
                        </div>
                    </div>
                    <form action="{{ route('demandas.store') }}" method="POST" id="demanda-form">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="descricao" class="form-label">
                                            <i class="fas fa-file-alt text-info"></i>
                                            Descrição <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="descricao" 
                                                  id="descricao" 
                                                  class="form-control @error('descricao') is-invalid @enderror" 
                                                  rows="3"
                                                  placeholder="Descreva detalhadamente a demanda..."
                                                  required>{{ old('descricao') }}</textarea>
                                        @error('descricao')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Forneça uma descrição clara e objetiva da demanda
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="prioridade_sam" class="form-label">
                                            <i class="fas fa-star text-warning"></i>
                                            Prioridade SAM <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               name="prioridade_sam" 
                                               id="prioridade_sam" 
                                               class="form-control @error('prioridade_sam') is-invalid @enderror" 
                                               value="{{ old('prioridade_sam') }}" 
                                               placeholder="Ex: Alta, Média, Baixa"
                                               required>
                                        @error('prioridade_sam')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="termo_adesao_id" class="form-label">
                                            <i class="fas fa-handshake text-success"></i>
                                            Termo de Adesão <span class="text-danger">*</span>
                                        </label>
                                        <select name="termo_adesao_id" 
                                                id="termo_adesao_id" 
                                                class="form-control select2 @error('termo_adesao_id') is-invalid @enderror" 
                                                required>
                                            <option value="">Selecione um Termo de Adesão</option>
                                            @foreach($termosAdesao as $termo)
                                                <option value="{{ $termo->id }}" {{ old('termo_adesao_id') == $termo->id ? 'selected' : '' }}>
                                                    {{ $termo->descricao }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('termo_adesao_id')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="cadastro_demanda_gms_id" class="form-label">
                                            <i class="fas fa-database text-primary"></i>
                                            Cadastro GMS <span class="text-danger">*</span>
                                        </label>
                                        <select name="cadastro_demanda_gms_id" 
                                                id="cadastro_demanda_gms_id" 
                                                class="form-control select2 @error('cadastro_demanda_gms_id') is-invalid @enderror" 
                                                required>
                                            <option value="">Selecione um Cadastro GMS</option>
                                            @foreach($cadastrosDemandaGms as $cadastro)
                                                <option value="{{ $cadastro->id }}" {{ old('cadastro_demanda_gms_id') == $cadastro->id ? 'selected' : '' }}>
                                                    {{ $cadastro->descricao }}
                                                    @if($cadastro->codigoGMS)
                                                        - Código: {{ $cadastro->codigoGMS }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('cadastro_demanda_gms_id')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-save"></i>
                                        Salvar Demanda
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('demandas.index') }}" class="btn btn-secondary btn-block">
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
    $('#demanda-form').on('submit', function(e) {
        let isValid = true;
        
        // Remover classes de erro anteriores
        $('.form-control').removeClass('is-invalid');
        
        // Validar campos obrigatórios
        $('input[required], select[required], textarea[required]').each(function() {
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
    $('input, select, textarea').on('change blur', function() {
        if ($(this).attr('required') && $(this).val()) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }
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