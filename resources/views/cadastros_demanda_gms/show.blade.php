@extends('adminlte::page')

@section('title', 'Detalhes do Cadastro GMS')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-eye text-info"></i>
                    Detalhes do Cadastro GMS
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cadastros-demanda-gms.index') }}">Cadastros GMS</a></li>
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
                            Informações do Cadastro GMS
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">ID: {{ $cadastroDemandaGms->id }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-primary">
                                        <i class="fas fa-file-alt"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Descrição do Cadastro</span>
                                        <span class="info-box-number">{{ $cadastroDemandaGms->descricao }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-code"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Código GMS</span>
                                        <span class="info-box-number">
                                            <code class="text-primary">{{ $cadastroDemandaGms->codigoGMS }}</code>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-warning">
                                        <i class="fas fa-receipt"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Protocolo</span>
                                        <span class="info-box-number">
                                            <code class="text-info">{{ $cadastroDemandaGms->protocolo }}</code>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card de Demandas Vinculadas -->
                @if($cadastroDemandaGms->demandas && $cadastroDemandaGms->demandas->count() > 0)
                    <div class="card card-success card-outline mt-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tasks"></i>
                                Demandas Vinculadas
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-success">{{ $cadastroDemandaGms->demandas->count() }} demandas</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Descrição</th>
                                            <th>Prioridade SAM</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cadastroDemandaGms->demandas as $demanda)
                                            <tr>
                                                <td><span class="badge badge-secondary">{{ $demanda->id }}</span></td>
                                                <td>{{ $demanda->descricao }}</td>
                                                <td><span class="badge badge-primary">{{ $demanda->prioridade_sam }}</span></td>
                                                <td>
                                                    <a href="{{ route('demandas.show', $demanda) }}" class="btn btn-sm btn-info" title="Ver demanda">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
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
                            <a href="{{ route('cadastros-demanda-gms.edit', $cadastroDemandaGms) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i>
                                Editar Cadastro
                            </a>
                            <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i>
                                Excluir Cadastro
                            </button>
                            <a href="{{ route('cadastros-demanda-gms.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i>
                                Voltar à Lista
                            </a>
                        </div>
                        
                        <form id="delete-form" action="{{ route('cadastros-demanda-gms.destroy', $cadastroDemandaGms) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
                
                <!-- Card de Informações do Sistema -->
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
                            <strong>Criado em:</strong> {{ $cadastroDemandaGms->created_at ? $cadastroDemandaGms->created_at->format('d/m/Y H:i') : 'Não informado' }}
                        </small>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-calendar-edit"></i>
                            <strong>Atualizado em:</strong> {{ $cadastroDemandaGms->updated_at ? $cadastroDemandaGms->updated_at->format('d/m/Y H:i') : 'Não informado' }}
                        </small>
                    </div>
                </div>

                <!-- Card de Sincronização -->
                <div class="card card-success card-outline mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-sync"></i>
                            Sincronização GMS
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Mantenha os dados atualizados com o sistema GMS.
                        </p>
                        <form action="{{ route('cadastros-demanda-gms.sync') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Deseja sincronizar os dados com o sistema GMS?')">
                                <i class="fas fa-cloud-download-alt"></i>
                                Sincronizar Agora
                            </button>
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
                        <h3>{{ $cadastroDemandaGms->demandas ? $cadastroDemandaGms->demandas->count() : 0 }}</h3>
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
                        <h3>1</h3>
                        <p>Código Único</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Mais informações <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $cadastroDemandaGms->created_at ? abs(round($cadastroDemandaGms->created_at->diffInDays(now()))) : 0 }}</h3>
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
        text: "Esta ação não pode ser desfeita! Todas as demandas vinculadas a este cadastro serão afetadas.",
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
.card-outline.card-success {
    border-top: 3px solid #28a745;
}
.btn-block {
    margin-bottom: 0.5rem;
}
.small-box {
    border-radius: .25rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}
code {
    font-size: 0.875rem;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
}
</style>
@stop 