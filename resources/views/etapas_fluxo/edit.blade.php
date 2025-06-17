@extends('adminlte::page')

@section('title', 'Editar Etapa de Fluxo')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-edit text-warning"></i>
                    Editar Etapa de Fluxo
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('etapas-fluxo.index') }}">Etapas de Fluxo</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-edit"></i>
                            Editar Etapa: {{ $etapaFluxo->nome_etapa }}
                        </h3>
                    </div>
                    <form action="{{ route('etapas-fluxo.update', $etapaFluxo) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <h5><i class="icon fas fa-ban"></i> Erro!</h5>
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nome_etapa">
                                            <i class="fas fa-stream"></i>
                                            Nome da Etapa <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('nome_etapa') is-invalid @enderror" id="nome_etapa" name="nome_etapa" value="{{ old('nome_etapa', $etapaFluxo->nome_etapa) }}" required>
                                        @error('nome_etapa')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tipo_fluxo_id">
                                            <i class="fas fa-route"></i>
                                            Tipo de Fluxo
                                        </label>
                                        <select class="form-control select2 @error('tipo_fluxo_id') is-invalid @enderror" id="tipo_fluxo_id" name="tipo_fluxo_id">
                                            <option value="">Genérica (não vinculada)</option>
                                            @foreach($tiposFluxo as $tipo)
                                                <option value="{{ $tipo->id }}" {{ old('tipo_fluxo_id', $etapaFluxo->tipo_fluxo_id) == $tipo->id ? 'selected' : '' }}>{{ $tipo->nome }}</option>
                                            @endforeach
                                        </select>
                                        @error('tipo_fluxo_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="ordem_execucao">
                                            <i class="fas fa-sort-numeric-up"></i>
                                            Ordem de Execução
                                        </label>
                                        <input type="number" class="form-control @error('ordem_execucao') is-invalid @enderror" id="ordem_execucao" name="ordem_execucao" value="{{ old('ordem_execucao', $etapaFluxo->ordem_execucao) }}" min="1">
                                        @error('ordem_execucao')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="modulo_id">
                                            <i class="fas fa-cogs"></i>
                                            Módulo <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control select2 @error('modulo_id') is-invalid @enderror" id="modulo_id" name="modulo_id" required>
                                            <option value="">Selecione o módulo</option>
                                            @foreach($modulos as $modulo)
                                                <option value="{{ $modulo->id }}" {{ old('modulo_id', $etapaFluxo->modulo_id) == $modulo->id ? 'selected' : '' }}>{{ $modulo->nome }}</option>
                                            @endforeach
                                        </select>
                                        @error('modulo_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="grupo_exigencia_id">
                                            <i class="fas fa-tags"></i>
                                            Grupo de Exigência
                                        </label>
                                        <select class="form-control select2 @error('grupo_exigencia_id') is-invalid @enderror" id="grupo_exigencia_id" name="grupo_exigencia_id">
                                            <option value="">Nenhum</option>
                                            @foreach($gruposExigencia as $grupo)
                                                <option value="{{ $grupo->id }}" {{ old('grupo_exigencia_id', $etapaFluxo->grupo_exigencia_id) == $grupo->id ? 'selected' : '' }}>{{ $grupo->nome }}</option>
                                            @endforeach
                                        </select>
                                        @error('grupo_exigencia_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="prazo_dias">
                                            <i class="fas fa-clock"></i>
                                            Prazo (dias) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control @error('prazo_dias') is-invalid @enderror" id="prazo_dias" name="prazo_dias" value="{{ old('prazo_dias', $etapaFluxo->prazo_dias) }}" min="1" required>
                                        @error('prazo_dias')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="organizacao_solicitante_id">
                                            <i class="fas fa-user-tie"></i>
                                            Organização Solicitante <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control select2 @error('organizacao_solicitante_id') is-invalid @enderror" id="organizacao_solicitante_id" name="organizacao_solicitante_id" required>
                                            <option value="">Selecione a organização solicitante</option>
                                            @foreach($organizacoes as $organizacao)
                                                <option value="{{ $organizacao->id }}" {{ old('organizacao_solicitante_id', $etapaFluxo->organizacao_solicitante_id) == $organizacao->id ? 'selected' : '' }}>{{ $organizacao->nome }}</option>
                                            @endforeach
                                        </select>
                                        @error('organizacao_solicitante_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="organizacao_executora_id">
                                            <i class="fas fa-users-cog"></i>
                                            Organização Executora <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control select2 @error('organizacao_executora_id') is-invalid @enderror" id="organizacao_executora_id" name="organizacao_executora_id" required>
                                            <option value="">Selecione a organização executora</option>
                                            @foreach($organizacoes as $organizacao)
                                                <option value="{{ $organizacao->id }}" {{ old('organizacao_executora_id', $etapaFluxo->organizacao_executora_id) == $organizacao->id ? 'selected' : '' }}>{{ $organizacao->nome }}</option>
                                            @endforeach
                                        </select>
                                        @error('organizacao_executora_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tipo_prazo">
                                            <i class="fas fa-calendar-alt"></i>
                                            Tipo de Prazo <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control @error('tipo_prazo') is-invalid @enderror" id="tipo_prazo" name="tipo_prazo" required>
                                            <option value="UTEIS" {{ old('tipo_prazo', $etapaFluxo->tipo_prazo ?? 'UTEIS') == 'UTEIS' ? 'selected' : '' }}>Dias Úteis</option>
                                            <option value="CORRIDOS" {{ old('tipo_prazo', $etapaFluxo->tipo_prazo) == 'CORRIDOS' ? 'selected' : '' }}>Dias Corridos</option>
                                        </select>
                                        @error('tipo_prazo')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- Espaço reservado para futuras expansões -->
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="descricao_customizada">
                                            <i class="fas fa-align-left"></i>
                                            Descrição da Etapa
                                        </label>
                                        <textarea class="form-control @error('descricao_customizada') is-invalid @enderror" id="descricao_customizada" name="descricao_customizada" rows="3">{{ old('descricao_customizada', $etapaFluxo->descricao_customizada) }}</textarea>
                                        @error('descricao_customizada')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="is_obrigatoria" name="is_obrigatoria" value="1" {{ old('is_obrigatoria', $etapaFluxo->is_obrigatoria) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="is_obrigatoria">
                                                <i class="fas fa-toggle-on"></i>
                                                Etapa obrigatória
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="permite_pular" name="permite_pular" value="1" {{ old('permite_pular', $etapaFluxo->permite_pular) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="permite_pular">
                                                <i class="fas fa-random"></i>
                                                Permite pular etapa
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="permite_retorno" name="permite_retorno" value="1" {{ old('permite_retorno', $etapaFluxo->permite_retorno) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="permite_retorno">
                                                <i class="fas fa-undo"></i>
                                                Permite retorno
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tipo_etapa">
                                            <i class="fas fa-random"></i>
                                            Tipo de Etapa
                                        </label>
                                        <select class="form-control @error('tipo_etapa') is-invalid @enderror" id="tipo_etapa" name="tipo_etapa">
                                            <option value="SEQUENCIAL" {{ old('tipo_etapa', $etapaFluxo->tipo_etapa) == 'SEQUENCIAL' ? 'selected' : '' }}>Sequencial</option>
                                            <option value="CONDICIONAL" {{ old('tipo_etapa', $etapaFluxo->tipo_etapa) == 'CONDICIONAL' ? 'selected' : '' }}>Condicional</option>
                                        </select>
                                        @error('tipo_etapa')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save"></i>
                                        Atualizar Etapa
                                    </button>
                                    <a href="{{ route('etapas-fluxo.index') }}" class="btn btn-secondary ml-2">
                                        <i class="fas fa-times"></i>
                                        Cancelar
                                    </a>
                                    <a href="{{ route('etapas-fluxo.show', $etapaFluxo) }}" class="btn btn-info ml-2">
                                        <i class="fas fa-eye"></i>
                                        Visualizar
                                    </a>
                                </div>
                                <div class="col-md-6 text-right">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Campos marcados com <span class="text-danger">*</span> são obrigatórios
                                    </small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Card para Configuração de Status e Transições -->
                <div class="card card-info mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exchange-alt"></i>
                            Configuração de Status e Transições
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Configuração de Fluxo:</strong> Defina quais opções de status estarão disponíveis para esta etapa e para onde cada status deve levar o fluxo.
                        </div>

                        <!-- Opções de Status Disponíveis -->
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="fas fa-list"></i> Opções de Status Disponíveis</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" id="tabelaStatusOpcoes">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Status</th>
                                                <th>Ordem</th>
                                                <th>Visível</th>
                                                <th>Requer Justificativa</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($statusDisponiveis as $status)
                                                @php
                                                    $opcaoExistente = $etapaFluxo->etapaStatusOpcoes->where('status_id', $status->id)->first();
                                                @endphp
                                                <tr data-status-id="{{ $status->id }}">
                                                    <td>
                                                        <span class="badge" style="background-color: {{ $status->cor }}">
                                                            <i class="{{ $status->icone }}"></i> {{ $status->nome }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm ordem-status" 
                                                               value="{{ $opcaoExistente ? $opcaoExistente->ordem : 0 }}" 
                                                               min="0" max="10" style="width: 70px;">
                                                    </td>
                                                    <td>
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input visivel-status" 
                                                                   id="visivel_{{ $status->id }}" 
                                                                   {{ $opcaoExistente && $opcaoExistente->mostra_para_responsavel ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="visivel_{{ $status->id }}"></label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input requer-justificativa" 
                                                                   id="justificativa_{{ $status->id }}" 
                                                                   {{ $opcaoExistente && $opcaoExistente->requer_justificativa ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="justificativa_{{ $status->id }}"></label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-success salvar-opcao-status" 
                                                                data-status-id="{{ $status->id }}">
                                                            <i class="fas fa-save"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Transições entre Etapas -->
                            <div class="col-md-6">
                                <h5><i class="fas fa-route"></i> Transições para Outras Etapas</h5>
                                <div class="mb-3">
                                    <button type="button" class="btn btn-sm btn-primary" id="adicionarTransicao">
                                        <i class="fas fa-plus"></i> Adicionar Transição
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" id="tabelaTransicoes">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Status Origem</th>
                                                <th>Status Destino</th>
                                                <th>Etapa Destino</th>
                                                <th>Prioridade</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($etapaFluxo->transicoesOrigem ?? [] as $transicao)
                                                <tr data-transicao-id="{{ $transicao->id }}">
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            ATUAL
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge" style="background-color: {{ $transicao->statusCondicao->cor ?? '#6c757d' }}">
                                                            {{ $transicao->statusCondicao->nome ?? 'Qualquer Status' }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $transicao->etapaDestino->nome_etapa }}</td>
                                                    <td>{{ $transicao->prioridade }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-warning editar-transicao" 
                                                                data-transicao-id="{{ $transicao->id }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger excluir-transicao" 
                                                                data-transicao-id="{{ $transicao->id }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Explicação do Sistema -->
                                <div class="alert alert-info alert-sm mt-3">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Como funciona:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><strong>Status Origem:</strong> Sempre será "ATUAL" (status atual da etapa)</li>
                                        <li><strong>Status Destino:</strong> Status que o usuário selecionará para finalizar a etapa</li>
                                        <li><strong>Etapa Destino:</strong> Para onde o fluxo irá após a seleção do status</li>
                                        <li><strong>Prioridade:</strong> Se houver múltiplas transições para o mesmo status, a de maior prioridade será usada</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Adicionar/Editar Transição -->
        <div class="modal fade" id="modalTransicao" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            <i class="fas fa-route"></i>
                            <span id="tituloModalTransicao">Adicionar Transição</span>
                        </h4>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form id="formTransicao">
                        <div class="modal-body">
                            <input type="hidden" id="transicaoId" name="transicao_id">
                            <input type="hidden" name="etapa_origem_id" value="{{ $etapaFluxo->id }}">
                            
                            <div class="alert alert-info alert-sm">
                                <i class="fas fa-info-circle"></i>
                                <strong>Configuração de Transição:</strong> Defina para qual etapa o fluxo deve ir quando o usuário selecionar um status específico para finalizar esta etapa.
                            </div>
                            
                            <div class="form-group">
                                <label for="statusCondicao">Status Selecionado pelo Usuário (Status Destino) *</label>
                                <select class="form-control" id="statusCondicao" name="status_condicao_id" required>
                                    <option value="">Selecione o status...</option>
                                    @foreach($statusDisponiveis as $status)
                                        <option value="{{ $status->id }}">{{ $status->nome }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Quando o usuário selecionar este status ao finalizar a etapa atual, o fluxo irá para a etapa destino escolhida abaixo.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="etapaDestino">Etapa de Destino *</label>
                                <select class="form-control" id="etapaDestino" name="etapa_destino_id" required>
                                    <option value="">Selecione a etapa de destino</option>
                                    @foreach($etapasDisponiveis as $etapa)
                                        @if($etapa->id !== $etapaFluxo->id)
                                            <option value="{{ $etapa->id }}">{{ $etapa->nome_etapa }} (Ordem: {{ $etapa->ordem_execucao }})</option>
                                        @endif
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Para onde o fluxo irá quando o status acima for selecionado.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="prioridade">Prioridade</label>
                                <input type="number" class="form-control" id="prioridade" name="prioridade" 
                                       value="0" min="0" max="100">
                                <small class="form-text text-muted">Maior número = maior prioridade (usado quando há múltiplas transições para o mesmo status)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="descricaoTransicao">Descrição</label>
                                <textarea class="form-control" id="descricaoTransicao" name="descricao" rows="2" 
                                          placeholder="Descreva quando esta transição deve ocorrer..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar Transição</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
    
    .alert-sm {
        padding: 0.25rem 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    
    .table-sm th,
    .table-sm td {
        padding: 0.3rem;
        vertical-align: middle;
    }
</style>
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@stop
@section('js')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    console.log('Script iniciado');
    
    // Aguardar o jQuery estar disponível
    function waitForJQuery(callback) {
        console.log('Verificando jQuery...');
        if (typeof $ !== 'undefined') {
            console.log('jQuery encontrado!');
            callback();
        } else {
            console.log('jQuery não encontrado, tentando novamente...');
            setTimeout(function() {
                waitForJQuery(callback);
            }, 100);
        }
    }

    waitForJQuery(function() {
        console.log('Iniciando jQuery ready...');
        $(document).ready(function() {
            console.log('jQuery ready executado!');
            
            // Teste simples
            console.log('Elementos encontrados:', {
                adicionarTransicao: $('#adicionarTransicao').length,
                modalTransicao: $('#modalTransicao').length,
                formTransicao: $('#formTransicao').length
            });
            
            // Inicializar Select2 se disponível
            if (typeof $.fn.select2 !== 'undefined') {
                console.log('Select2 disponível');
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            } else {
                console.log('Select2 não disponível');
            }

            // Adicionar transição - versão simplificada
            $('#adicionarTransicao').on('click', function() {
                console.log('Botão Adicionar Transição clicado');
                try {
                    $('#transicaoId').val('');
                    $('#tituloModalTransicao').text('Adicionar Transição');
                    $('#formTransicao')[0].reset();
                    $('#modalTransicao').modal('show');
                    console.log('Modal deveria estar aberto');
                } catch (error) {
                    console.error('Erro ao abrir modal:', error);
                    alert('Erro ao abrir modal: ' + error.message);
                }
            });

            // Salvar opção de status - versão corrigida
            $(document).on('click', '.salvar-opcao-status', function() {
                console.log('Salvar opção de status clicado');
                const statusId = $(this).data('status-id');
                const row = $(this).closest('tr');
                
                // Converter boolean para string para o Laravel
                const visivelChecked = row.find('.visivel-status').is(':checked');
                const requerJustificativaChecked = row.find('.requer-justificativa').is(':checked');
                
                const dados = {
                    etapa_fluxo_id: {{ $etapaFluxo->id }},
                    status_id: statusId,
                    ordem: row.find('.ordem-status').val(),
                    mostra_para_responsavel: visivelChecked ? '1' : '0',
                    requer_justificativa: requerJustificativaChecked ? '1' : '0',
                    _token: '{{ csrf_token() }}'
                };

                console.log('Dados a enviar:', dados);

                $.post('/api/etapa-status-opcoes/salvar', dados)
                    .done(function(response) {
                        console.log('Resposta recebida:', response);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Sucesso!', 'Opção de status salva com sucesso', 'success');
                        } else {
                            alert('Opção de status salva com sucesso');
                        }
                    })
                    .fail(function(xhr) {
                        console.error('Erro na requisição:', xhr);
                        let errorMessage = 'Erro desconhecido';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            // Formatar erros de validação
                            const errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join(', ');
                        } else if (xhr.responseText) {
                            errorMessage = xhr.responseText;
                        }
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Erro!', 'Erro ao salvar opção de status: ' + errorMessage, 'error');
                        } else {
                            alert('Erro ao salvar opção de status: ' + errorMessage);
                        }
                    });
            });

            // Salvar transição - versão simplificada
            $('#formTransicao').on('submit', function(e) {
                console.log('Form transição submetido');
                e.preventDefault();
                
                const dados = {
                    etapa_fluxo_origem_id: {{ $etapaFluxo->id }},
                    status_condicao_id: $('#statusCondicao').val() || null,
                    etapa_fluxo_destino_id: $('#etapaDestino').val(),
                    prioridade: $('#prioridade').val(),
                    descricao: $('#descricaoTransicao').val(),
                    _token: '{{ csrf_token() }}'
                };

                console.log('Dados da transição:', dados);

                const transicaoId = $('#transicaoId').val();
                const url = transicaoId ? `/api/transicoes-etapa/${transicaoId}` : '/api/transicoes-etapa';
                const method = transicaoId ? 'PUT' : 'POST';

                console.log('URL:', url, 'Method:', method);

                $.ajax({
                    url: url,
                    method: method,
                    data: dados
                })
                .done(function(response) {
                    console.log('Transição salva:', response);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Sucesso!', 'Transição salva com sucesso', 'success')
                            .then(() => {
                                $('#modalTransicao').modal('hide');
                                location.reload();
                            });
                    } else {
                        alert('Transição salva com sucesso');
                        $('#modalTransicao').modal('hide');
                        location.reload();
                    }
                })
                .fail(function(xhr) {
                    console.error('Erro ao salvar transição:', xhr);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Erro!', 'Erro ao salvar transição: ' + xhr.responseText, 'error');
                    } else {
                        alert('Erro ao salvar transição: ' + xhr.responseText);
                    }
                });
            });

            // Editar transição
            $(document).on('click', '.editar-transicao', function() {
                console.log('Botão Editar Transição clicado');
                const transicaoId = $(this).data('transicao-id');
                console.log('ID da transição:', transicaoId);
                
                if (!transicaoId) {
                    console.error('ID da transição não encontrado');
                    alert('Erro: ID da transição não encontrado');
                    return;
                }
                
                $('#tituloModalTransicao').text('Editar Transição');
                
                // Carregar dados da transição
                console.log('Fazendo requisição GET para:', `/api/transicoes-etapa/${transicaoId}`);
                $.get(`/api/transicoes-etapa/${transicaoId}`)
                    .done(function(response) {
                        console.log('Resposta completa da API:', response);
                        
                        // Verificar se a resposta tem a estrutura esperada
                        if (response.success === false) {
                            console.error('API retornou erro:', response.message);
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Erro!', response.message, 'error');
                            } else {
                                alert('Erro: ' + response.message);
                            }
                            return;
                        }
                        
                        // Usar os dados da resposta (pode ser response ou response.data)
                        const transicao = response.data || response;
                        console.log('Dados da transição processados:', transicao);
                        
                        $('#transicaoId').val(transicao.id);
                        $('#statusCondicao').val(transicao.status_condicao_id || '');
                        $('#etapaDestino').val(transicao.etapa_fluxo_destino_id);
                        $('#prioridade').val(transicao.prioridade || 0);
                        $('#descricaoTransicao').val(transicao.descricao || '');
                        
                        console.log('Campos preenchidos:', {
                            id: transicao.id,
                            status_condicao_id: transicao.status_condicao_id,
                            etapa_fluxo_destino_id: transicao.etapa_fluxo_destino_id,
                            prioridade: transicao.prioridade,
                            descricao: transicao.descricao
                        });
                        
                        $('#modalTransicao').modal('show');
                    })
                    .fail(function(xhr) {
                        console.error('Erro na requisição:', xhr);
                        console.error('Status:', xhr.status);
                        console.error('Response Text:', xhr.responseText);
                        
                        let errorMessage = 'Erro ao carregar dados da transição';
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            errorMessage = errorResponse.message || errorMessage;
                        } catch (e) {
                            errorMessage += ': ' + xhr.responseText;
                        }
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Erro!', errorMessage, 'error');
                        } else {
                            alert(errorMessage);
                        }
                    });
            });

            // Excluir transição
            $(document).on('click', '.excluir-transicao', function() {
                console.log('Botão Excluir Transição clicado');
                const transicaoId = $(this).data('transicao-id');
                console.log('ID da transição a excluir:', transicaoId);
                
                if (!transicaoId) {
                    console.error('ID da transição não encontrado');
                    alert('Erro: ID da transição não encontrado');
                    return;
                }
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Confirmar Exclusão',
                        text: 'Tem certeza que deseja excluir esta transição?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sim, excluir',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            excluirTransicao(transicaoId);
                        } else {
                            console.log('Exclusão cancelada pelo usuário');
                        }
                    });
                } else {
                    if (confirm('Tem certeza que deseja excluir esta transição?')) {
                        excluirTransicao(transicaoId);
                    } else {
                        console.log('Exclusão cancelada pelo usuário');
                    }
                }
            });

            // Função para excluir transição
            function excluirTransicao(transicaoId) {
                console.log('Executando exclusão da transição:', transicaoId);
                console.log('Fazendo requisição DELETE para:', `/api/transicoes-etapa/${transicaoId}`);
                
                $.ajax({
                    url: `/api/transicoes-etapa/${transicaoId}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    beforeSend: function() {
                        console.log('Enviando requisição de exclusão...');
                    }
                })
                .done(function(response) {
                    console.log('Resposta da exclusão:', response);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Excluído!', 'Transição excluída com sucesso', 'success')
                            .then(() => {
                                console.log('Recarregando página...');
                                location.reload();
                            });
                    } else {
                        alert('Transição excluída com sucesso');
                        console.log('Recarregando página...');
                        location.reload();
                    }
                })
                .fail(function(xhr) {
                    console.error('Erro ao excluir transição:', xhr);
                    console.error('Status:', xhr.status);
                    console.error('Response Text:', xhr.responseText);
                    
                    let errorMessage = 'Erro ao excluir transição';
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMessage = errorResponse.message || errorMessage;
                    } catch (e) {
                        errorMessage += ': ' + xhr.responseText;
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Erro!', errorMessage, 'error');
                    } else {
                        alert(errorMessage);
                    }
                });
            }

            console.log('Todos os event listeners configurados');
        });
    });
    
    console.log('Script finalizado');
</script>
@stop 