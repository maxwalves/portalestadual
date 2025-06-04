@extends('adminlte::page')

@section('title', 'Editar Cadastro GMS')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-edit text-warning"></i>
                    Editar Cadastro GMS
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cadastros-demanda-gms.index') }}">Cadastros GMS</a></li>
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
                            Dados do Cadastro GMS
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">ID: {{ $cadastroDemandaGms->id }}</span>
                        </div>
                    </div>
                    <form action="{{ route('cadastros-demanda-gms.update', $cadastroDemandaGms) }}" method="POST">
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
                                            <i class="fas fa-file-alt text-primary"></i>
                                            Descrição *
                                        </label>
                                        <input type="text" 
                                               name="descricao" 
                                               id="descricao" 
                                               class="form-control @error('descricao') is-invalid @enderror" 
                                               value="{{ old('descricao', $cadastroDemandaGms->descricao) }}"
                                               placeholder="Descreva o cadastro GMS..."
                                               required>
                                        @error('descricao')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codigoGMS" class="form-label">
                                            <i class="fas fa-code text-info"></i>
                                            Código GMS *
                                        </label>
                                        <input type="text" 
                                               name="codigoGMS" 
                                               id="codigoGMS" 
                                               class="form-control @error('codigoGMS') is-invalid @enderror" 
                                               value="{{ old('codigoGMS', $cadastroDemandaGms->codigoGMS) }}"
                                               placeholder="Ex: GMS001"
                                               required>
                                        @error('codigoGMS')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="protocolo" class="form-label">
                                            <i class="fas fa-receipt text-warning"></i>
                                            Protocolo *
                                        </label>
                                        <input type="text" 
                                               name="protocolo" 
                                               id="protocolo" 
                                               class="form-control @error('protocolo') is-invalid @enderror" 
                                               value="{{ old('protocolo', $cadastroDemandaGms->protocolo) }}"
                                               placeholder="Ex: PROT001"
                                               required>
                                        @error('protocolo')
                                            <div class="invalid-feedback">{{ $message }}</div>
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
                                        Atualizar Cadastro
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary ml-2">
                                        <i class="fas fa-undo"></i>
                                        Restaurar
                                    </button>
                                </div>
                                <div class="col-md-6 text-right">
                                    <a href="{{ route('cadastros-demanda-gms.show', $cadastroDemandaGms) }}" class="btn btn-info mr-2">
                                        <i class="fas fa-eye"></i>
                                        Visualizar
                                    </a>
                                    <a href="{{ route('cadastros-demanda-gms.index') }}" class="btn btn-secondary">
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
                                <li><strong>Código GMS:</strong> Informe o código único do sistema GMS</li>
                                <li><strong>Protocolo:</strong> Número de protocolo oficial</li>
                            </ul>
                        </div>
                        
                        <div class="callout callout-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Importante</h6>
                            <p class="mb-0">
                                Campos marcados com <strong>*</strong> são obrigatórios.
                                O código GMS deve ser único no sistema.
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
                            <strong>Criado em:</strong> {{ $cadastroDemandaGms->created_at ? $cadastroDemandaGms->created_at->format('d/m/Y H:i') : 'Não informado' }}
                        </small>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-calendar-edit"></i>
                            <strong>Última atualização:</strong> {{ $cadastroDemandaGms->updated_at ? $cadastroDemandaGms->updated_at->format('d/m/Y H:i') : 'Não informado' }}
                        </small>
                    </div>
                </div>

                <!-- Card de Demandas Vinculadas -->
                @if($cadastroDemandaGms->demandas && $cadastroDemandaGms->demandas->count() > 0)
                    <div class="card card-success card-outline mt-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tasks"></i>
                                Demandas Vinculadas
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-success">{{ $cadastroDemandaGms->demandas->count() }}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="callout callout-warning">
                                <h6><i class="fas fa-exclamation-triangle"></i> Atenção</h6>
                                <p class="mb-0">
                                    Este cadastro possui <strong>{{ $cadastroDemandaGms->demandas->count() }} demanda(s)</strong> vinculada(s).
                                    Alterações podem afetar essas demandas.
                                </p>
                            </div>
                            
                            <div class="list-group list-group-flush">
                                @foreach($cadastroDemandaGms->demandas->take(3) as $demanda)
                                    <div class="list-group-item p-2">
                                        <small>
                                            <span class="badge badge-secondary">{{ $demanda->id }}</span>
                                            {{ Str::limit($demanda->descricao, 40) }}
                                        </small>
                                    </div>
                                @endforeach
                                @if($cadastroDemandaGms->demandas->count() > 3)
                                    <div class="list-group-item p-2 text-center">
                                        <small class="text-muted">
                                            + {{ $cadastroDemandaGms->demandas->count() - 3 }} demanda(s) a mais
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
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
.card-outline.card-success {
    border-top: 3px solid #28a745;
}
.form-label {
    font-weight: 600;
    color: #495057;
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
.list-group-item {
    border-left: none;
    border-right: none;
}
</style>
@stop 