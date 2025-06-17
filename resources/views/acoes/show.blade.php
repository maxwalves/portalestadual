@extends('adminlte::page')

@section('title', 'Visualizar Ação')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-eye text-info"></i>
                    Visualizar Ação
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('acoes.index') }}">Ações</a></li>
                    <li class="breadcrumb-item active">Visualizar</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Informações Principais -->
            <div class="col-md-8">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            Informações da Ação
                        </h3>
                        <div class="card-tools">
                            <div class="btn-group">
                                <a href="{{ route('workflow.acao', $acao) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-route"></i>
                                    Workflow
                                </a>
                                <a href="{{ route('acoes.edit', $acao) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                    Editar
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-hashtag mr-1"></i> ID:</strong>
                                <span class="badge badge-secondary">{{ $acao->id }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-calendar mr-1"></i> Criado em:</strong>
                                {{ $acao->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-12">
                                <strong><i class="fas fa-tasks mr-1"></i> Descrição:</strong>
                                <p class="text-muted">{{ $acao->descricao }}</p>
                            </div>
                        </div>

                        @if($acao->projeto_sam)
                            <div class="row">
                                <div class="col-12">
                                    <strong><i class="fas fa-project-diagram mr-1"></i> Projeto SAM:</strong>
                                    <span class="badge badge-warning">{{ $acao->projeto_sam }}</span>
                                </div>
                            </div>
                            <hr>
                        @endif

                        @if($acao->localizacao)
                            <div class="row">
                                <div class="col-12">
                                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Localização:</strong>
                                    <p class="text-muted">{{ $acao->localizacao }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Valores -->
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-dollar-sign mr-1"></i> Valor Estimado:</strong>
                                @if($acao->valor_estimado)
                                    <span class="text-success font-weight-bold">
                                        R$ {{ number_format($acao->valor_estimado, 2, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-muted">Não informado</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-hand-holding-usd mr-1"></i> Valor Contratado:</strong>
                                @if($acao->valor_contratado)
                                    <span class="text-primary font-weight-bold">
                                        R$ {{ number_format($acao->valor_contratado, 2, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-muted">Não informado</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Demanda Associada -->
                @if($acao->demanda)
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clipboard-list"></i>
                                Demanda Associada
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-hashtag mr-1"></i> ID:</strong>
                                    <span class="badge badge-info">{{ $acao->demanda->id }}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-building mr-1"></i> Organização:</strong>
                                    @if($acao->demanda->organizacao)
                                        <span class="badge badge-primary">{{ $acao->demanda->organizacao->nome }}</span>
                                    @else
                                        <span class="text-muted">Não informado</span>
                                    @endif
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <strong><i class="fas fa-clipboard-list mr-1"></i> Descrição:</strong>
                                    <p class="text-muted">{{ $acao->demanda->descricao }}</p>
                                </div>
                            </div>
                            @if($acao->demanda->prioridade_sam)
                                <div class="row">
                                    <div class="col-12">
                                        <strong><i class="fas fa-exclamation-triangle mr-1"></i> Prioridade SAM:</strong>
                                        <span class="badge badge-warning">{{ $acao->demanda->prioridade_sam }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Tipo de Fluxo -->
                @if($acao->tipoFluxo)
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-route"></i>
                                Tipo de Fluxo
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <div class="icon-circle bg-warning text-dark mx-auto mb-3">
                                    <i class="fas fa-route"></i>
                                </div>
                                <h5>{{ $acao->tipoFluxo->nome }}</h5>
                                @if($acao->tipoFluxo->descricao)
                                    <p class="text-muted">{{ $acao->tipoFluxo->descricao }}</p>
                                @endif
                                @if($acao->tipoFluxo->versao)
                                    <span class="badge badge-info">Versão: {{ $acao->tipoFluxo->versao }}</span>
                                @endif
                                <br>
                                <span class="badge badge-{{ $acao->tipoFluxo->ativo ? 'success' : 'danger' }}">
                                    {{ $acao->tipoFluxo->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Execuções de Etapa -->
                {{-- TODO: Descomentar quando ExecucaoEtapa for implementado
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tasks"></i>
                            Execuções de Etapa
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($acao->execucoesEtapa && $acao->execucoesEtapa->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($acao->execucoesEtapa as $execucao)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge badge-primary">{{ $execucao->id }}</span>
                                            @if($execucao->status)
                                                <span class="badge badge-info">{{ $execucao->status->descricao }}</span>
                                            @endif
                                        </div>
                                        @if($execucao->observacoes)
                                            <small class="text-muted">{{ $execucao->observacoes }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>Nenhuma execução de etapa encontrada</p>
                            </div>
                        @endif
                    </div>
                </div>
                --}}

                <!-- Ações -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cogs"></i>
                            Ações
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('acoes.edit', $acao) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i>
                                Editar Ação
                            </a>
                            <a href="{{ route('acoes.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i>
                                Voltar à Lista
                            </a>
                            <button type="button" 
                                    class="btn btn-danger btn-block" 
                                    onclick="confirmDelete({{ $acao->id }})">
                                <i class="fas fa-trash"></i>
                                Excluir Ação
                            </button>
                        </div>
                        <form id="delete-form-{{ $acao->id }}" 
                              action="{{ route('acoes.destroy', $acao) }}" 
                              method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.card-outline.card-info {
    border-top: 3px solid #17a2b8;
}
.card-outline.card-success {
    border-top: 3px solid #28a745;
}
.card-outline.card-warning {
    border-top: 3px solid #ffc107;
}
.card-outline.card-secondary {
    border-top: 3px solid #6c757d;
}
.card-outline.card-primary {
    border-top: 3px solid #007bff;
}
</style>
@stop

@section('js')
<script>
function confirmDelete(id) {
    // Verificar se SweetAlert está disponível
    if (typeof Swal === 'undefined') {
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
    const formId = 'delete-form-' + id;
    const form = document.getElementById(formId);
    
    if (form) {
        form.submit();
    } else {
        console.error('Form not found:', formId);
        alert('Erro: Formulário não encontrado!');
    }
}
</script>
@stop 