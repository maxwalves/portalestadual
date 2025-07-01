@extends('adminlte::page')

@section('title', 'Visualizar Status')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-eye"></i> Status - {{ $status->nome }}</h1>
        <div>
            <a href="{{ route('status.edit', $status) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('status.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Informações do Status
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Código:</strong>
                        <p class="text-muted"><code>{{ $status->codigo }}</code></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Nome:</strong>
                        <p class="text-muted">{{ $status->nome }}</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <strong>Categoria:</strong>
                        <p class="text-muted">
                            <span class="badge badge-primary">{{ $status->categoria }}</span>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <strong>Cor:</strong>
                        <p class="text-muted">
                            @if($status->cor)
                                <span class="badge" style="background-color: {{ $status->cor }};">{{ $status->cor }}</span>
                            @else
                                <span class="text-muted">Não definida</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <strong>Status:</strong>
                        <p class="text-muted">
                            @if($status->is_ativo)
                                <span class="badge badge-success">Ativo</span>
                            @else
                                <span class="badge badge-danger">Inativo</span>
                            @endif
                        </p>
                    </div>
                </div>
                
                @if($status->descricao)
                <div class="row">
                    <div class="col-12">
                        <strong>Descrição:</strong>
                        <p class="text-muted">{{ $status->descricao }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar"></i> Estatísticas
                </h3>
            </div>
            <div class="card-body">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-play"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Execuções</span>
                        <span class="info-box-number">{{ $estatisticas['execucoes_ativas'] }}</span>
                    </div>
                </div>
                
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-route"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Transições</span>
                        <span class="info-box-number">{{ $estatisticas['transicoes_configuradas'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Informações de Auditoria -->
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock"></i> Informações de Auditoria
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Criado em:</strong>
                        <p class="text-muted">{{ $status->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Última atualização:</strong>
                        <p class="text-muted">{{ $status->updated_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($estatisticas['execucoes_ativas'] > 0 || $estatisticas['transicoes_configuradas'] > 0)
<!-- Aviso de Uso -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <h5><i class="icon fas fa-exclamation-triangle"></i> Status em Uso!</h5>
            Este status está sendo utilizado no sistema. Tenha cuidado ao fazer alterações
            que possam afetar o funcionamento dos fluxos de trabalho.
        </div>
    </div>
</div>
@endif
@stop

@section('css')
<style>
.info-box {
    margin-bottom: 15px;
}
.badge {
    font-size: 0.9em;
}
.btn-group-vertical .btn {
    border-radius: 0.25rem !important;
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
        var isAtivo = {{ $status->is_ativo ? 'true' : 'false' }};
        var acao = isAtivo ? 'desativar' : 'ativar';
        
        Swal.fire({
            title: 'Confirmar ação',
            text: 'Tem certeza que deseja ' + acao + ' este status?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, ' + acao + '!',
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