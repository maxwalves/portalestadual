@extends('adminlte::page')

@section('title', 'Cadastros GMS')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-database text-primary"></i>
                    Cadastros GMS
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Cadastros GMS</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Botões de Ação -->
        <div class="row mb-3">
            <div class="col-lg-12">
                <div class="card card-primary card-outline">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <a href="{{ route('cadastros-demanda-gms.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Novo Cadastro GMS
                                </a>
                                <form action="{{ route('cadastros-demanda-gms.sync') }}" method="POST" style="display: inline;" class="ml-2">
                                    @csrf
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Deseja sincronizar os dados com o sistema GMS?')">
                                        <i class="fas fa-sync"></i>
                                        Sincronizar com GMS
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6 text-right">
                                <span class="badge badge-info badge-lg">
                                    <i class="fas fa-list"></i>
                                    Total: {{ $cadastros->total() }} cadastros
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

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Tabela de Cadastros -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table"></i>
                            Lista de Cadastros GMS
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($cadastros->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 80px;">
                                                <i class="fas fa-hashtag"></i>
                                                ID
                                            </th>
                                            <th>
                                                <i class="fas fa-file-alt"></i>
                                                Descrição
                                            </th>
                                            <th style="width: 140px;">
                                                <i class="fas fa-code"></i>
                                                Código GMS
                                            </th>
                                            <th style="width: 140px;">
                                                <i class="fas fa-receipt"></i>
                                                Protocolo
                                            </th>
                                            <th style="width: 120px;">
                                                <i class="fas fa-link"></i>
                                                Demandas
                                            </th>
                                            <th style="width: 160px;">
                                                <i class="fas fa-cogs"></i>
                                                Ações
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cadastros as $cadastro)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-secondary">{{ $cadastro->id }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $cadastro->descricao }}</strong>
                                                </td>
                                                <td>
                                                    <code class="text-primary">{{ $cadastro->codigoGMS }}</code>
                                                </td>
                                                <td>
                                                    <code class="text-info">{{ $cadastro->protocolo }}</code>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-success">
                                                        {{ $cadastro->demandas ? $cadastro->demandas->count() : 0 }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('cadastros-demanda-gms.show', $cadastro) }}" class="btn btn-info btn-sm" title="Visualizar">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('cadastros-demanda-gms.edit', $cadastro) }}" class="btn btn-warning btn-sm" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $cadastro->id }})" title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    
                                                    <form id="delete-form-{{ $cadastro->id }}" action="{{ route('cadastros-demanda-gms.destroy', $cadastro) }}" method="POST" style="display: none;">
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
                                <i class="fas fa-database fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Nenhum cadastro GMS encontrado</h4>
                                <p class="text-muted">Clique no botão "Novo Cadastro GMS" para começar ou sincronize com o sistema GMS.</p>
                                <div class="mt-3">
                                    <a href="{{ route('cadastros-demanda-gms.create') }}" class="btn btn-primary mr-2">
                                        <i class="fas fa-plus"></i>
                                        Criar Primeiro Cadastro
                                    </a>
                                    <form action="{{ route('cadastros-demanda-gms.sync') }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-sync"></i>
                                            Sincronizar com GMS
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    @if($cadastros->count() > 0)
                        <div class="card-footer">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        Mostrando {{ $cadastros->firstItem() }} a {{ $cadastros->lastItem() }} de {{ $cadastros->total() }} resultados
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    {{ $cadastros->links() }}
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
        text: "Esta ação não pode ser desfeita! Todas as demandas vinculadas a este cadastro também serão afetadas.",
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
code {
    font-size: 0.875rem;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
}
</style>
@stop 