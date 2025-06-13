@extends('adminlte::page')

@section('title', 'Etapas de Fluxo')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-stream text-primary"></i>
                    Lista de Etapas de Fluxo
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Etapas de Fluxo</li>
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
                            Lista de Etapas de Fluxo
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('etapas-fluxo.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i>
                                Nova Etapa
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
                                        <th><i class="fas fa-stream"></i> Nome da Etapa</th>
                                        <th><i class="fas fa-route"></i> Tipo de Fluxo</th>
                                        <th><i class="fas fa-cogs"></i> Módulo</th>
                                        <th><i class="fas fa-tags"></i> Grupo de Exigência</th>
                                        <th class="text-center"><i class="fas fa-sort-numeric-up"></i> Ordem</th>
                                        <th class="text-center"><i class="fas fa-toggle-on"></i> Obrigatória</th>
                                        <th class="text-center" style="width: 200px;"><i class="fas fa-cogs"></i> Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($etapas as $etapa)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge badge-secondary">{{ $etapa->id }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $etapa->nome_etapa }}</strong>
                                                <br>
                                                <small class="text-muted">{{ Str::limit($etapa->descricao_customizada, 60) }}</small>
                                            </td>
                                            <td>
                                                @if($etapa->tipoFluxo)
                                                    <span class="badge badge-info">{{ $etapa->tipoFluxo->nome }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($etapa->modulo)
                                                    <span class="badge badge-primary">{{ $etapa->modulo->nome }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($etapa->grupoExigencia)
                                                    <span class="badge badge-warning">{{ $etapa->grupoExigencia->nome }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{ $etapa->ordem_execucao ?? '-' }}
                                            </td>
                                            <td class="text-center">
                                                @if($etapa->is_obrigatoria)
                                                    <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                                @else
                                                    <span class="badge badge-danger"><i class="fas fa-times"></i></span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('etapas-fluxo.show', $etapa) }}" class="btn btn-info btn-sm" title="Visualizar">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('etapas-fluxo.edit', $etapa) }}" class="btn btn-warning btn-sm" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger btn-sm" title="Excluir" onclick="confirmDelete({{ $etapa->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <form id="delete-form-{{ $etapa->id }}" action="{{ route('etapas-fluxo.destroy', $etapa) }}" method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-stream fa-3x mb-3"></i>
                                                    <p class="h5">Nenhuma etapa cadastrada</p>
                                                    <a href="{{ route('etapas-fluxo.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus"></i>
                                                        Criar primeira etapa
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($etapas->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    Mostrando {{ $etapas->firstItem() }} a {{ $etapas->lastItem() }} de {{ $etapas->total() }} resultados
                                </div>
                                {{ $etapas->links() }}
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