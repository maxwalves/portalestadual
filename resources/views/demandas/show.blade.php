@extends('adminlte::page')

@section('title', 'Detalhes da Demanda')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-eye text-info"></i>
                    Detalhes da Demanda
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('demandas.index') }}">Demandas</a></li>
                    <li class="breadcrumb-item active">Visualizar</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Card Principal -->
            <div class="col-lg-8">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            Informações da Demanda
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">ID: {{ $demanda->id }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-file-alt"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Descrição</span>
                                        <span class="info-box-number">{{ $demanda->descricao }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-warning">
                                        <i class="fas fa-star"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Prioridade SAM</span>
                                        <span class="info-box-number">{{ $demanda->prioridade_sam }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-secondary">
                                        <i class="fas fa-calendar"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Criado em</span>
                                        <span class="info-box-number">{{ $demanda->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card de Ações -->
            <div class="col-lg-4">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cogs"></i>
                            Ações Disponíveis
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('demandas.edit', $demanda) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i>
                                Editar Demanda
                            </a>
                            <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i>
                                Excluir Demanda
                            </button>
                            <a href="{{ route('demandas.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i>
                                Voltar à Lista
                            </a>
                        </div>
                        
                        <form id="delete-form" action="{{ route('demandas.destroy', $demanda) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cards de Vinculações -->
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-handshake"></i>
                            Termo de Adesão
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($demanda->termoAdesao)
                            <div class="media">
                                <div class="media-object">
                                    <i class="fas fa-file-contract fa-2x text-success"></i>
                                </div>
                                <div class="media-body ml-3">
                                    <h5 class="mt-0">{{ $demanda->termoAdesao->descricao }}</h5>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-calendar-alt"></i>
                                        Data de Criação: {{ \Carbon\Carbon::parse($demanda->termoAdesao->data_criacao)->format('d/m/Y') }}
                                    </p>
                                    @if($demanda->termoAdesao->path_arquivo)
                                        <p class="mb-0">
                                            <a href="{{ Storage::url($demanda->termoAdesao->path_arquivo) }}" 
                                               class="btn btn-sm btn-outline-success" 
                                               target="_blank">
                                                <i class="fas fa-download"></i>
                                                Baixar Arquivo
                                            </a>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-exclamation-circle fa-3x mb-2"></i>
                                <p>Nenhum termo de adesão vinculado</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-database"></i>
                            Cadastro GMS
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($demanda->cadastroDemandaGms)
                            <div class="media">
                                <div class="media-object">
                                    <i class="fas fa-server fa-2x text-primary"></i>
                                </div>
                                <div class="media-body ml-3">
                                    <h5 class="mt-0">{{ $demanda->cadastroDemandaGms->descricao }}</h5>
                                    @if($demanda->cadastroDemandaGms->codigoGMS)
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-code"></i>
                                            Código GMS: <code>{{ $demanda->cadastroDemandaGms->codigoGMS }}</code>
                                        </p>
                                    @endif
                                    @if($demanda->cadastroDemandaGms->protocolo)
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-receipt"></i>
                                            Protocolo: <code>{{ $demanda->cadastroDemandaGms->protocolo }}</code>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-exclamation-circle fa-3x mb-2"></i>
                                <p>Nenhum cadastro GMS vinculado</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
function confirmDelete() {
    Swal.fire({
        title: 'Tem certeza?',
        text: "Esta ação não pode ser desfeita!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form').submit();
        }
    });
}
</script>
@stop

@section('css')
<style>
.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: .25rem;
}
.info-box-icon {
    border-radius: .25rem 0 0 .25rem;
}
.card-outline.card-info {
    border-top: 3px solid #17a2b8;
}
.card-outline.card-warning {
    border-top: 3px solid #ffc107;
}
.card-outline.card-success {
    border-top: 3px solid #28a745;
}
.card-outline.card-primary {
    border-top: 3px solid #007bff;
}
.media-object {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background-color: #f8f9fa;
    border-radius: 50%;
}
.btn-block {
    margin-bottom: 0.5rem;
}
</style>
@stop 