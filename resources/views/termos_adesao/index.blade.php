@extends('adminlte::page')

@section('title', 'Termos de Adesão')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-handshake text-primary"></i>
                    Termos de Adesão
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Termos de Adesão</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Botão de Ação e Filtros -->
        <div class="row mb-3">
            <div class="col-lg-12">
                <div class="card card-primary card-outline">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <a href="{{ route('termos-adesao.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Novo Termo de Adesão
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <span class="badge badge-info badge-lg">
                                    <i class="fas fa-list"></i>
                                    Total: {{ $termos->total() }} termos
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Tabela de Termos -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table"></i>
                            Lista de Termos de Adesão
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($termos->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 80px;">
                                                <i class="fas fa-hashtag"></i>
                                                ID
                                            </th>
                                            <th>
                                                <i class="fas fa-file-contract"></i>
                                                Descrição
                                            </th>
                                            <th style="width: 140px;">
                                                <i class="fas fa-calendar"></i>
                                                Data Criação
                                            </th>
                                            <th>
                                                <i class="fas fa-building"></i>
                                                Organização
                                            </th>
                                            <th style="width: 120px;">
                                                <i class="fas fa-paperclip"></i>
                                                Arquivo
                                            </th>
                                            <th style="width: 160px;">
                                                <i class="fas fa-cogs"></i>
                                                Ações
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($termos as $termo)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-secondary">{{ $termo->id }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $termo->descricao }}</strong>
                                                </td>
                                                <td>
                                                    <span class="text-muted">
                                                        <i class="fas fa-calendar-day"></i>
                                                        {{ \Carbon\Carbon::parse($termo->data_criacao)->format('d/m/Y') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        {{ $termo->organizacao->nome ?? '-' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($termo->path_arquivo)
                                                        <a href="{{ Storage::url($termo->path_arquivo) }}" target="_blank" title="Download do arquivo">
                                                            <i class="fas fa-file-alt text-success" title="Arquivo anexado"></i>
                                                        </a>
                                                    @else
                                                        <i class="fas fa-file-times text-danger" title="Sem arquivo"></i>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('termos-adesao.show', $termo) }}" class="btn btn-info btn-sm" title="Visualizar">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('termos-adesao.edit', $termo) }}" class="btn btn-warning btn-sm" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $termo->id }})" title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    
                                                    <form id="delete-form-{{ $termo->id }}" action="{{ route('termos-adesao.destroy', $termo) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-handshake fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Nenhum termo de adesão encontrado</h4>
                                <p class="text-muted">Clique no botão "Novo Termo de Adesão" para começar.</p>
                                <a href="{{ route('termos-adesao.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Criar Primeiro Termo
                                </a>
                            </div>
                        @endif
                    </div>
                    
                    @if($termos->count() > 0)
                        <div class="card-footer">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        Mostrando {{ $termos->firstItem() }} a {{ $termos->lastItem() }} de {{ $termos->total() }} resultados
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    {{ $termos->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
function confirmDelete(id) {
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
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
@stop

@section('css')
<style>
.card-outline.card-primary {
    border-top: 3px solid #007bff;
}
.table-responsive {
    border-radius: 0.25rem;
}
.thead-light th {
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
}
.btn-group .btn {
    margin-right: 2px;
}
.btn-group .btn:last-child {
    margin-right: 0;
}
.badge-lg {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}
</style>
@stop 