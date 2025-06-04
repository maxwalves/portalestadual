@extends('adminlte::page')

@section('title', 'Demandas')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-tasks text-primary"></i>
                    Gerenciamento de Demandas
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Demandas</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            Lista de Demandas
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('demandas.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i>
                                Nova Demanda
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible mx-3 mt-3">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <i class="icon fas fa-check"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center" style="width: 60px;">
                                            <i class="fas fa-hashtag"></i>
                                        </th>
                                        <th>
                                            <i class="fas fa-file-alt"></i>
                                            Descrição
                                        </th>
                                        <th class="text-center">
                                            <i class="fas fa-star"></i>
                                            Prioridade SAM
                                        </th>
                                        <th class="text-center">
                                            <i class="fas fa-handshake"></i>
                                            Termo de Adesão
                                        </th>
                                        <th class="text-center">
                                            <i class="fas fa-database"></i>
                                            Cadastro GMS
                                        </th>
                                        <th class="text-center" style="width: 200px;">
                                            <i class="fas fa-cogs"></i>
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($demandas as $demanda)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge badge-secondary">{{ $demanda->id }}</span>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 250px;" title="{{ $demanda->descricao }}">
                                                    {{ $demanda->descricao }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info">{{ $demanda->prioridade_sam }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($demanda->termoAdesao)
                                                    <span class="badge badge-success" title="{{ $demanda->termoAdesao->descricao }}">
                                                        <i class="fas fa-check"></i>
                                                        {{ Str::limit($demanda->termoAdesao->descricao, 20) }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Não vinculado
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($demanda->cadastroDemandaGms)
                                                    <span class="badge badge-primary" title="{{ $demanda->cadastroDemandaGms->descricao }}">
                                                        <i class="fas fa-check"></i>
                                                        {{ Str::limit($demanda->cadastroDemandaGms->descricao, 20) }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Não vinculado
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('demandas.show', $demanda) }}" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Visualizar">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('demandas.edit', $demanda) }}" 
                                                       class="btn btn-warning btn-sm" 
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            title="Excluir"
                                                            onclick="confirmDelete({{ $demanda->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <form id="delete-form-{{ $demanda->id }}" 
                                                      action="{{ route('demandas.destroy', $demanda) }}" 
                                                      method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <p class="h5">Nenhuma demanda encontrada</p>
                                                    <a href="{{ route('demandas.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus"></i>
                                                        Criar primeira demanda
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($demandas->hasPages())
                        <div class="card-footer">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <p class="text-muted mb-0">
                                        Mostrando {{ $demandas->firstItem() }} a {{ $demandas->lastItem() }} 
                                        de {{ $demandas->total() }} resultados
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        {{ $demandas->links() }}
                                    </div>
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
.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}
.btn-group .btn {
    margin-right: 2px;
}
.btn-group .btn:last-child {
    margin-right: 0;
}
.card-outline.card-primary {
    border-top: 3px solid #007bff;
}
</style>
@stop 