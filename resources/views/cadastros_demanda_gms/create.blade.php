@extends('adminlte::page')

@section('title', 'Criar Cadastro GMS')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-plus text-success"></i>
                    Criar Cadastro GMS
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cadastros-demanda-gms.index') }}">Cadastros GMS</a></li>
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
                            Dados do Cadastro GMS
                        </h3>
                    </div>
                    <form action="{{ route('cadastros-demanda-gms.store') }}" method="POST">
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
                                            <i class="fas fa-file-alt text-primary"></i>
                                            Descrição *
                                        </label>
                                        <input type="text" 
                                               name="descricao" 
                                               id="descricao" 
                                               class="form-control @error('descricao') is-invalid @enderror" 
                                               value="{{ old('descricao') }}"
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
                                               value="{{ old('codigoGMS') }}"
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
                                               value="{{ old('protocolo') }}"
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
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i>
                                        Salvar Cadastro
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary ml-2">
                                        <i class="fas fa-undo"></i>
                                        Limpar
                                    </button>
                                </div>
                                <div class="col-md-6 text-right">
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

                <!-- Card de Sincronização -->
                <div class="card card-success card-outline mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-sync"></i>
                            Sincronização
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Você também pode sincronizar dados diretamente do sistema GMS em vez de criar manualmente.
                        </p>
                        <form action="{{ route('cadastros-demanda-gms.sync') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Deseja sincronizar os dados com o sistema GMS?')">
                                <i class="fas fa-cloud-download-alt"></i>
                                Sincronizar com GMS
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.card-outline.card-primary {
    border-top: 3px solid #007bff;
}
.card-outline.card-info {
    border-top: 3px solid #17a2b8;
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
</style>
@stop 