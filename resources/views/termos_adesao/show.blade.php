@extends('adminlte::page')

@section('title', 'Detalhes do Termo de Adesão')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-eye text-info"></i>
                    Detalhes do Termo de Adesão
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('termos-adesao.index') }}">Termos de Adesão</a></li>
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
                            Informações do Termo de Adesão
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">ID: {{ $termo->id }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-primary">
                                        <i class="fas fa-file-contract"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Descrição do Termo</span>
                                        <span class="info-box-number">{{ $termo->descricao }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-calendar"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Data de Criação</span>
                                        <span class="info-box-number">{{ \Carbon\Carbon::parse($termo->data_criacao)->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-warning">
                                        <i class="fas fa-building"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Organização</span>
                                        <span class="info-box-number">{{ $termo->organizacao->nome ?? 'Não informado' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Seção do Arquivo -->
                        <div class="row">
                            <div class="col-12">
                                <div class="callout callout-info">
                                    <h5><i class="fas fa-paperclip"></i> Arquivo Anexo</h5>
                                    @if($termo->path_arquivo)
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-alt fa-2x text-success mr-3"></i>
                                            <div>
                                                <strong>Arquivo disponível</strong>
                                                <br>
                                                <small class="text-muted">Clique no botão para fazer o download</small>
                                            </div>
                                            <div class="ml-auto">
                                                <a href="{{ Storage::url($termo->path_arquivo) }}" target="_blank" class="btn btn-success">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-times fa-2x text-danger mr-3"></i>
                                            <div>
                                                <strong>Nenhum arquivo anexado</strong>
                                                <br>
                                                <small class="text-muted">Este termo não possui arquivo anexo</small>
                                            </div>
                                        </div>
                                    @endif
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
                            <a href="{{ route('termos-adesao.edit', $termo) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i>
                                Editar Termo
                            </a>
                            <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i>
                                Excluir Termo
                            </button>
                            <a href="{{ route('termos-adesao.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i>
                                Voltar à Lista
                            </a>
                        </div>
                        
                        <form id="delete-form" action="{{ route('termos-adesao.destroy', $termo) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
                
                <!-- Card de Informações Adicionais -->
                <div class="card card-secondary card-outline mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info"></i>
                            Informações do Sistema
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
                            <strong>Atualizado em:</strong> {{ $termo->updated_at ? $termo->updated_at->format('d/m/Y H:i') : 'Não informado' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card de Estatísticas -->
        <div class="row mt-3">
            <div class="col-md-4">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $termo->demandas ? $termo->demandas->count() : 0 }}</h3>
                        <p>Demandas Vinculadas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Mais informações <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $termo->path_arquivo ? 1 : 0 }}</h3>
                        <p>Arquivo{{ $termo->path_arquivo ? ' Anexado' : 's Anexados' }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-paperclip"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Mais informações <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $termo->data_criacao ? abs(round(\Carbon\Carbon::parse($termo->data_criacao)->diffInDays(now()))) : 0 }}</h3>
                        <p>Dias desde a criação</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Mais informações <i class="fas fa-arrow-circle-right"></i>
                    </a>
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
.card-outline.card-secondary {
    border-top: 3px solid #6c757d;
}
.btn-block {
    margin-bottom: 0.5rem;
}
.small-box {
    border-radius: .25rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}
.callout {
    border-radius: .25rem;
    border-left: 5px solid #17a2b8;
}
</style>
@stop 