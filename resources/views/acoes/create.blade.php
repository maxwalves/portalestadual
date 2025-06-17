@extends('adminlte::page')

@section('title', 'Nova Ação')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-plus text-primary"></i>
                    Nova Ação
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('acoes.index') }}">Ações</a></li>
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
                            Criar Nova Ação
                        </h3>
                    </div>
                    <form action="{{ route('acoes.store') }}" method="POST">
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
                                        <label for="nome">
                                            <i class="fas fa-tag"></i>
                                            Nome da Ação <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('nome') is-invalid @enderror" 
                                               id="nome" 
                                               name="nome" 
                                               value="{{ old('nome') }}" 
                                               placeholder="Digite o nome da ação"
                                               required>
                                        @error('nome')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="demanda_id">
                                            <i class="fas fa-clipboard-list"></i>
                                            Demanda <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control select2 @error('demanda_id') is-invalid @enderror" 
                                                id="demanda_id" 
                                                name="demanda_id" 
                                                required>
                                            <option value="">Selecione uma demanda</option>
                                            @foreach($demandas as $demanda)
                                                <option value="{{ $demanda->id }}" 
                                                        {{ old('demanda_id') == $demanda->id ? 'selected' : '' }}>
                                                    {{ $demanda->descricao }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('demanda_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="descricao">
                                            <i class="fas fa-tasks"></i>
                                            Descrição Detalhada
                                        </label>
                                        <textarea class="form-control @error('descricao') is-invalid @enderror" 
                                                  id="descricao" 
                                                  name="descricao" 
                                                  rows="3"
                                                  placeholder="Digite uma descrição detalhada da ação (opcional)">{{ old('descricao') }}</textarea>
                                        @error('descricao')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="projeto_sam">
                                            <i class="fas fa-project-diagram"></i>
                                            Projeto SAM
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('projeto_sam') is-invalid @enderror" 
                                               id="projeto_sam" 
                                               name="projeto_sam" 
                                               value="{{ old('projeto_sam') }}" 
                                               placeholder="Digite o código do projeto SAM">
                                        @error('projeto_sam')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tipo_fluxo_id">
                                            <i class="fas fa-route"></i>
                                            Tipo de Fluxo <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control select2 @error('tipo_fluxo_id') is-invalid @enderror" 
                                                id="tipo_fluxo_id" 
                                                name="tipo_fluxo_id" 
                                                required>
                                            <option value="">Selecione um tipo de fluxo</option>
                                            @foreach($tipoFluxos as $tipoFluxo)
                                                <option value="{{ $tipoFluxo->id }}" 
                                                        {{ old('tipo_fluxo_id') == $tipoFluxo->id ? 'selected' : '' }}>
                                                    {{ $tipoFluxo->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('tipo_fluxo_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="valor_estimado">
                                            <i class="fas fa-dollar-sign"></i>
                                            Valor Estimado (R$)
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">R$</span>
                                            </div>
                                            <input type="text" 
                                                   class="form-control money-mask @error('valor_estimado') is-invalid @enderror" 
                                                   id="valor_estimado" 
                                                   name="valor_estimado" 
                                                   value="{{ old('valor_estimado') }}" 
                                                   placeholder="Ex: 50.000.000,00">
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Limite máximo: R$ 999.999.999.999,99
                                        </small>
                                        @error('valor_estimado')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="valor_contratado">
                                            <i class="fas fa-hand-holding-usd"></i>
                                            Valor Contratado (R$)
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">R$</span>
                                            </div>
                                            <input type="text" 
                                                   class="form-control money-mask @error('valor_contratado') is-invalid @enderror" 
                                                   id="valor_contratado" 
                                                   name="valor_contratado" 
                                                   value="{{ old('valor_contratado') }}" 
                                                   placeholder="Ex: 45.000.000,00">
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Limite máximo: R$ 999.999.999.999,99
                                        </small>
                                        @error('valor_contratado')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="localizacao">
                                            <i class="fas fa-map-marker-alt"></i>
                                            Localização
                                        </label>
                                        <div class="input-group">
                                            <input type="text" 
                                                   class="form-control cidade-autocomplete @error('localizacao') is-invalid @enderror" 
                                                   id="localizacao" 
                                                   name="localizacao" 
                                                   value="{{ old('localizacao') }}" 
                                                   placeholder="Digite o nome da cidade">
                                            <div class="input-group-append">
                                                <span class="input-group-text cidade-loading" style="display: none;">
                                                    <i class="fas fa-spinner fa-spin text-primary"></i>
                                                </span>
                                            </div>
                                        </div>
                                        @error('localizacao')
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
                                        Salvar Ação
                                    </button>
                                    <a href="{{ route('acoes.index') }}" class="btn btn-secondary ml-2">
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

@section('css')
<style>
.card-primary:not(.card-outline) > .card-header {
    background-color: #007bff;
}
.form-group label {
    font-weight: 600;
}
.text-danger {
    color: #dc3545 !important;
}
.ui-autocomplete {
    max-height: 200px;
    overflow-y: auto;
    z-index: 1051 !important;
}
.cidade-loading {
    background-color: #f8f9fa !important;
    border-left: 0 !important;
    padding: 0.375rem 0.75rem !important;
}
.cidade-loading i {
    font-size: 0.875rem;
    animation-duration: 1s;
}
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">
@stop

@section('js')
<script src="//code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializar Select2 se disponível
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Selecione uma opção',
            allowClear: true
        });
    }
    
    // Máscara para valores monetários - permitindo até bilhões
    $('.money-mask').mask('000.000.000.000.000,00', {
        reverse: true,
        translation: {
            '0': {pattern: /[0-9]/}
        }
    });
    
    // Autocomplete para cidades do Paraná
    $('.cidade-autocomplete').autocomplete({
        source: function(request, response) {
            // Mostrar indicador de carregamento
            $('.cidade-loading').show();
            
            $.ajax({
                url: '{{ route("api.cidades-parana") }}',
                dataType: 'json',
                data: {
                    term: request.term
                },
                success: function(data) {
                    // Esconder indicador de carregamento
                    $('.cidade-loading').hide();
                    
                    var filtered = data.filter(function(item) {
                        return item.nome.toLowerCase().indexOf(request.term.toLowerCase()) !== -1;
                    });
                    response(filtered.slice(0, 10)); // Limitar a 10 resultados
                },
                error: function() {
                    // Esconder indicador de carregamento em caso de erro
                    $('.cidade-loading').hide();
                    response([]);
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $(this).val(ui.item.nome);
            return false;
        },
        focus: function(event, ui) {
            $(this).val(ui.item.nome);
            return false;
        },
        close: function() {
            // Garantir que o loading seja escondido quando o autocomplete fechar
            $('.cidade-loading').hide();
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append("<div>" + item.label + "</div>")
            .appendTo(ul);
    };
    
    // Converter valores antes do envio do formulário
    $('form').on('submit', function() {
        $('.money-mask').each(function() {
            var value = $(this).val();
            if (value) {
                // Remover formatação e converter para decimal
                value = value.replace(/\./g, '').replace(',', '.');
                $(this).val(value);
            }
        });
    });
});
</script>
@stop 