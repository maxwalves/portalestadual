@extends('adminlte::page')

@section('title', 'Grupo de Exigência: ' . $grupoExigencia->nome)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">
                <i class="fas fa-layer-group text-primary mr-2"></i>
                {{ $grupoExigencia->nome }}
            </h1>
            <small class="text-muted">
                Detalhes do grupo de exigência
                @if(!$grupoExigencia->is_ativo)
                    <span class="badge badge-secondary ml-2">Inativo</span>
                @endif
            </small>
        </div>
        <div>
            <a href="{{ route('grupo-exigencias.templates', $grupoExigencia) }}" class="btn btn-info mr-2">
                <i class="fas fa-link mr-1"></i>
                Gerenciar Templates
            </a>
            <a href="{{ route('grupo-exigencias.edit', $grupoExigencia) }}" class="btn btn-warning mr-2">
                <i class="fas fa-edit mr-1"></i>
                Editar
            </a>
            <a href="{{ route('grupo-exigencias.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Voltar
            </a>
        </div>
    </div>
@stop

@section('content')
    <!-- Informações Gerais -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Informações Gerais
                    </h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Nome:</dt>
                        <dd class="col-sm-9">{{ $grupoExigencia->nome }}</dd>
                        
                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-{{ $grupoExigencia->getCorStatus() }}">
                                <i class="{{ $grupoExigencia->getIconeStatus() }} mr-1"></i>{{ $grupoExigencia->getStatusFormatado() }}
                            </span>
                        </dd>
                        
                        <dt class="col-sm-3">Descrição:</dt>
                        <dd class="col-sm-9">
                            @if($grupoExigencia->descricao)
                                {{ $grupoExigencia->descricao }}
                            @else
                                <span class="text-muted font-italic">Nenhuma descrição informada</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-3">Criado em:</dt>
                        <dd class="col-sm-9">{{ $grupoExigencia->created_at->format('d/m/Y H:i') }}</dd>
                        
                        <dt class="col-sm-3">Atualizado em:</dt>
                        <dd class="col-sm-9">{{ $grupoExigencia->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- Estatísticas -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Estatísticas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-gradient-primary mb-3">
                        <span class="info-box-icon">
                            <i class="fas fa-file-alt"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Templates</span>
                            <span class="info-box-number">{{ $estatisticas['total_templates'] }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box bg-gradient-danger mb-3">
                        <span class="info-box-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Obrigatórios</span>
                            <span class="info-box-number">{{ $estatisticas['templates_obrigatorios'] }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box bg-gradient-info mb-3">
                        <span class="info-box-icon">
                            <i class="fas fa-info-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Opcionais</span>
                            <span class="info-box-number">{{ $estatisticas['templates_opcionais'] }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon">
                            <i class="fas fa-project-diagram"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Etapas de Fluxo</span>
                            <span class="info-box-number">{{ $estatisticas['total_etapas'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates de Documentos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-alt mr-2"></i>
                Templates de Documentos ({{ $templates->count() }})
            </h3>
            <div class="card-tools">
                <a href="{{ route('template-documentos.create', ['grupo_exigencia_id' => $grupoExigencia->id]) }}" 
                   class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i>
                    Adicionar Template
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if($templates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 5%;" class="text-center">Ordem</th>
                                <th style="width: 35%;">Template</th>
                                <th style="width: 25%;">Tipo de Documento</th>
                                <th style="width: 15%;" class="text-center">Obrigatório</th>
                                <th style="width: 10%;" class="text-center">Arquivos</th>
                                <th style="width: 10%;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ $template->ordem }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $template->nome }}</strong>
                                            @if($template->descricao)
                                                <br><small class="text-muted">{{ Str::limit($template->descricao, 80) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-outline-primary">{{ $template->tipoDocumento->nome }}</span>
                                        @if($template->tipoDocumento->extensoes_permitidas)
                                            <br><small class="text-muted">{{ $template->tipoDocumento->extensoes_permitidas }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($template->is_obrigatorio)
                                            <span class="badge badge-danger">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>Obrigatório
                                            </span>
                                        @else
                                            <span class="badge badge-info">
                                                <i class="fas fa-info-circle mr-1"></i>Opcional
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group-vertical btn-group-sm">
                                            @if($template->hasArquivoModelo())
                                                <button class="btn btn-outline-primary btn-xs" title="Modelo disponível">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            @endif
                                            @if($template->hasExemploPreenchido())
                                                <button class="btn btn-outline-info btn-xs" title="Exemplo disponível">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif
                                        </div>
                                        @if(!$template->hasArquivoModelo() && !$template->hasExemploPreenchido())
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('template-documentos.show', $template) }}" 
                                               class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('template-documentos.edit', $template) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum template de documento encontrado</h5>
                    <p class="text-muted">Este grupo ainda não possui templates de documentos associados.</p>
                    <a href="{{ route('template-documentos.create', ['grupo_exigencia_id' => $grupoExigencia->id]) }}" 
                       class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i>
                        Adicionar Primeiro Template
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Etapas de Fluxo que usam este grupo -->
    @if($etapasFluxo->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-project-diagram mr-2"></i>
                    Etapas de Fluxo que Usam este Grupo ({{ $etapasFluxo->count() }})
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Tipo de Fluxo</th>
                                <th>Nome da Etapa</th>
                                <th>Organização Solicitante</th>
                                <th>Organização Executora</th>
                                <th class="text-center">Ordem</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($etapasFluxo as $etapa)
                                <tr>
                                    <td>
                                        <span class="badge badge-primary">{{ $etapa->tipoFluxo->nome }}</span>
                                    </td>
                                    <td>{{ $etapa->nome_etapa }}</td>
                                    <td>{{ $etapa->organizacaoSolicitante->nome }}</td>
                                    <td>{{ $etapa->organizacaoExecutora->nome }}</td>
                                    <td class="text-center">
                                        @if($etapa->ordem_execucao)
                                            <span class="badge badge-secondary">{{ $etapa->ordem_execucao }}</span>
                                        @else
                                            <span class="text-muted">Condicional</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('etapas-fluxo.show', $etapa) }}" 
                                           class="btn btn-sm btn-outline-primary">
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

    <!-- Ações Rápidas -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-bolt mr-2"></i>
                Ações Rápidas
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <a href="{{ route('grupo-exigencias.edit', $grupoExigencia) }}" class="btn btn-warning btn-block">
                        <i class="fas fa-edit mr-1"></i>
                        Editar Grupo
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('template-documentos.create', ['grupo_exigencia_id' => $grupoExigencia->id]) }}" 
                       class="btn btn-success btn-block">
                        <i class="fas fa-plus mr-1"></i>
                        Adicionar Template
                    </a>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-info btn-block" 
                            onclick="duplicarGrupo({{ $grupoExigencia->id }}, '{{ $grupoExigencia->nome }}')">
                        <i class="fas fa-copy mr-1"></i>
                        Duplicar Grupo
                    </button>
                </div>
                <div class="col-md-3">
                    <form action="{{ route('grupo-exigencias.toggle-ativo', $grupoExigencia) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn {{ $grupoExigencia->is_ativo ? 'btn-outline-secondary' : 'btn-outline-success' }} btn-block">
                            @if($grupoExigencia->is_ativo)
                                <i class="fas fa-times mr-1"></i>Desativar
                            @else
                                <i class="fas fa-check mr-1"></i>Ativar
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Duplicar Grupo -->
    <div class="modal fade" id="modalDuplicar" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formDuplicar" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-copy mr-2"></i>
                            Duplicar Grupo de Exigência
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Você está duplicando o grupo: <strong id="nomeGrupoOriginal"></strong></p>
                        <div class="form-group">
                            <label for="nomeNovo">Nome do novo grupo:</label>
                            <input type="text" class="form-control" id="nomeNovo" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="descricaoNova">Descrição (opcional):</label>
                            <textarea class="form-control" id="descricaoNova" name="descricao" rows="3"></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Todos os templates de documento serão copiados para o novo grupo.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-copy mr-1"></i>Duplicar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .info-box {
        margin-bottom: 1rem;
    }
    
    .badge-outline-primary {
        color: #007bff;
        border: 1px solid #007bff;
        background-color: transparent;
    }
    
    .btn-xs {
        padding: 0.125rem 0.25rem;
        font-size: 0.75rem;
    }
    
    dl.row dt {
        font-weight: 600;
        color: #495057;
    }
</style>
@stop

@section('js')
<script>
    function duplicarGrupo(id, nome) {
        document.getElementById('nomeGrupoOriginal').textContent = nome;
        document.getElementById('nomeNovo').value = nome + ' (Cópia)';
        document.getElementById('formDuplicar').action = `/grupo-exigencias/${id}/duplicar`;
        $('#modalDuplicar').modal('show');
    }
</script>
@stop
