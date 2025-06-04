@extends('adminlte::page')

@section('title', 'Organizações')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-building text-primary"></i>
                    Gerenciamento de Organizações
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Organizações</li>
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
                            Lista de Organizações
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('organizacoes.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i>
                                Nova Organização
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
                                            <i class="fas fa-building"></i>
                                            Nome da Organização
                                        </th>
                                        <th class="text-center" style="width: 120px;">
                                            <i class="fas fa-toggle-on"></i>
                                            Status
                                        </th>
                                        <th class="text-center" style="width: 200px;">
                                            <i class="fas fa-cogs"></i>
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($organizacoes as $organizacao)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge badge-secondary">{{ $organizacao->id }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-circle bg-primary text-white mr-3">
                                                        <i class="fas fa-building"></i>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $organizacao->nome }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar"></i>
                                                            Criado em {{ $organizacao->created_at->format('d/m/Y') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($organizacao->is_ativo)
                                                    <span class="badge badge-success badge-lg">
                                                        <i class="fas fa-check-circle"></i>
                                                        Ativo
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger badge-lg">
                                                        <i class="fas fa-times-circle"></i>
                                                        Inativo
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('organizacoes.show', $organizacao) }}" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Visualizar">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('organizacoes.edit', $organizacao) }}" 
                                                       class="btn btn-warning btn-sm" 
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            title="Excluir"
                                                            onclick="confirmDelete({{ $organizacao->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <form id="delete-form-{{ $organizacao->id }}" 
                                                      action="{{ route('organizacoes.destroy', $organizacao) }}" 
                                                      method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-building fa-3x mb-3"></i>
                                                    <p class="h5">Nenhuma organização encontrada</p>
                                                    <a href="{{ route('organizacoes.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus"></i>
                                                        Criar primeira organização
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($organizacoes->hasPages())
                        <div class="card-footer">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <p class="text-muted mb-0">
                                        Mostrando {{ $organizacoes->firstItem() }} a {{ $organizacoes->lastItem() }} 
                                        de {{ $organizacoes->total() }} resultados
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        {{ $organizacoes->links() }}
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
    console.log('confirmDelete called with id:', id);
    
    // Verificar se SweetAlert está disponível
    if (typeof Swal === 'undefined') {
        console.warn('SweetAlert não está carregado, usando confirm nativo');
        if (confirm('Tem certeza que deseja excluir? Esta ação não pode ser desfeita!')) {
            submitDeleteForm(id);
        }
        return;
    }
    
    // Usar SweetAlert
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
            submitDeleteForm(id);
        }
    });
}

function submitDeleteForm(id) {
    console.log('submitDeleteForm called with id:', id);
    
    const formId = 'delete-form-' + id;
    const form = document.getElementById(formId);
    
    if (form) {
        console.log('Submitting form:', form.action);
        form.submit();
    } else {
        console.error('Form not found:', formId);
        alert('Erro: Formulário não encontrado!');
    }
}

// Aguardar o carregamento completo da página
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado');
    
    // Aguardar um pouco para garantir que o SweetAlert carregue
    setTimeout(function() {
        if (typeof Swal !== 'undefined') {
            console.log('✅ SweetAlert carregado com sucesso');
        } else {
            console.warn('⚠️ SweetAlert não carregou, usando fallback');
        }
    }, 1000);
});
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
.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}
.badge-lg {
    font-size: 0.85em;
    padding: 0.5em 0.75em;
}
</style>
@stop 