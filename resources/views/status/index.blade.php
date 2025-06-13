@extends('adminlte::page')

@section('title', 'Status do Sistema')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tags"></i> Status do Sistema</h1>
        <a href="{{ route('status.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo Status
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filtros -->
        <div class="card card-outline card-primary collapsed-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i> Filtros
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style="display: none;">
                <form method="GET" action="{{ route('status.index') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Categoria</label>
                                <select name="categoria" class="form-control">
                                    <option value="">Todas as categorias</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>
                                            {{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Buscar</label>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Código, nome ou descrição..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="ativo" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="1" {{ request('ativo') === '1' ? 'selected' : '' }}>Ativos</option>
                                    <option value="0" {{ request('ativo') === '0' ? 'selected' : '' }}>Inativos</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="{{ route('status.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabela -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i> Lista de Status
                </h3>
                <div class="card-tools">
                    <span class="badge badge-info">{{ $status->total() }} registro(s)</span>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Cor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($status as $item)
                        <tr>
                            <td><code>{{ $item->codigo }}</code></td>
                            <td><strong>{{ $item->nome }}</strong></td>
                            <td>
                                <span class="badge badge-primary">{{ $item->categoria }}</span>
                            </td>
                            <td>
                                @if($item->cor)
                                    <span class="badge" style="background-color: {{ $item->cor }};">{{ $item->cor }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($item->is_ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-danger">Inativo</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('status.show', $item) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('status.edit', $item) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Nenhum status encontrado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($status->hasPages())
            <div class="card-footer">
                {{ $status->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.badge {
    font-size: 0.75em;
}
.table td {
    vertical-align: middle;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Confirmação antes de alterar status
    $('form[action*="toggle-ativo"]').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        var acao = $(this).find('button').attr('title');
        
        Swal.fire({
            title: 'Confirmar ação',
            text: 'Tem certeza que deseja ' + acao.toLowerCase() + ' este status?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, confirmar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@stop 