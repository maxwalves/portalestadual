@extends('adminlte::page')

@section('title', 'Detalhes da Organização')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-eye text-info"></i>
                    Detalhes da Organização
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('organizacoes.index') }}">Organizações</a></li>
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
                            Informações da Organização
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">ID: {{ $organizacao->id }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-primary">
                                        <i class="fas fa-building"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Nome da Organização</span>
                                        <span class="info-box-number">{{ $organizacao->nome }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon {{ $organizacao->is_ativo ? 'bg-success' : 'bg-danger' }}">
                                        <i class="fas {{ $organizacao->is_ativo ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Status</span>
                                        <span class="info-box-number">
                                            {{ $organizacao->is_ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
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
                                        <span class="info-box-number">{{ $organizacao->created_at->format('d/m/Y H:i') }}</span>
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
                            <a href="{{ route('organizacoes.edit', $organizacao) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i>
                                Editar Organização
                            </a>
                            <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i>
                                Excluir Organização
                            </button>
                            <a href="{{ route('organizacoes.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i>
                                Voltar à Lista
                            </a>
                        </div>
                        
                        <form id="delete-form" action="{{ route('organizacoes.destroy', $organizacao) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card de Estatísticas -->
        <div class="row mt-3">
            <div class="col-md-4">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $organizacao->termosAdesao ? $organizacao->termosAdesao->count() : 0 }}</h3>
                        <p>Termos de Adesão</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Mais informações <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $organizacao->users ? $organizacao->users->count() : 0 }}</h3>
                        <p>Usuários Vinculados</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Mais informações <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ round($organizacao->created_at->diffInDays(now())) }}</h3>
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
.btn-block {
    margin-bottom: 0.5rem;
}
.small-box {
    border-radius: .25rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}
</style>
@stop 