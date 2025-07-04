@extends('adminlte::page')

@section('title', 'Tipos de Fluxo')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-route text-primary"></i>
                    Gerenciamento de Tipos de Fluxo
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Tipos de Fluxo</li>
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
                            Lista de Tipos de Fluxo
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('tipos-fluxo.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i>
                                Novo Tipo de Fluxo
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

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible mx-3 mt-3">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <i class="icon fas fa-times"></i>
                                {{ session('error') }}
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
                                            <i class="fas fa-route"></i>
                                            Nome
                                        </th>
                                        <th>
                                            <i class="fas fa-align-left"></i>
                                            Descrição
                                        </th>
                                        <th>
                                            <i class="fas fa-tags"></i>
                                            Categoria
                                        </th>
                                        <th class="text-center">
                                            <i class="fas fa-code-branch"></i>
                                            Versão
                                        </th>
                                        <th class="text-center">
                                            <i class="fas fa-toggle-on"></i>
                                            Status
                                        </th>
                                        <th class="text-center" style="width: 250px;">
                                            <i class="fas fa-cogs"></i>
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tipoFluxos as $tipoFluxo)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge badge-secondary">{{ $tipoFluxo->id }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-circle bg-primary text-white mr-3">
                                                        <i class="fas fa-route"></i>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $tipoFluxo->nome }}</strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($tipoFluxo->descricao)
                                                    <span class="text-muted">{{ Str::limit($tipoFluxo->descricao, 80) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($tipoFluxo->categoria)
                                                    <span class="badge badge-info">
                                                        {{ $tipoFluxo->categoria }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-warning">
                                                    v{{ $tipoFluxo->versao }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($tipoFluxo->is_ativo)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i>
                                                        Ativo
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times"></i>
                                                        Inativo
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('tipos-fluxo.show', $tipoFluxo) }}" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Visualizar">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('tipos-fluxo.etapas', $tipoFluxo) }}" 
                                                       class="btn btn-success btn-sm" 
                                                       title="Gerenciar Etapas">
                                                        <i class="fas fa-tasks"></i>
                                                    </a>
                                                    <a href="{{ route('tipos-fluxo.edit', $tipoFluxo) }}" 
                                                       class="btn btn-warning btn-sm" 
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            title="Excluir"
                                                            onclick="confirmDelete({{ $tipoFluxo->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <form id="delete-form-{{ $tipoFluxo->id }}" 
                                                      action="{{ route('tipos-fluxo.destroy', $tipoFluxo) }}" 
                                                      method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-route fa-3x mb-3"></i>
                                                    <p class="h5">Nenhum tipo de fluxo encontrado</p>
                                                    <a href="{{ route('tipos-fluxo.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus"></i>
                                                        Criar primeiro tipo de fluxo
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($tipoFluxos->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    Mostrando {{ $tipoFluxos->firstItem() }} a {{ $tipoFluxos->lastItem() }} 
                                    de {{ $tipoFluxos->total() }} resultados
                                </div>
                                {{ $tipoFluxos->links() }}
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
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
</style>
@stop 