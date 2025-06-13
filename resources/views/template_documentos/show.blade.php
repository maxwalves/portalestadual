@extends('adminlte::page')

@section('title', 'Template: ' . $templateDocumento->nome)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-invoice mr-2"></i>Template: {{ $templateDocumento->nome }}</h1>
        <a href="{{ route('template-documentos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Voltar
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i>Informações do Template</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Nome:</dt>
                        <dd class="col-sm-9">{{ $templateDocumento->nome }}</dd>

                        <dt class="col-sm-3">Tipo de Documento:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-secondary">{{ $templateDocumento->tipoDocumento->nome }}</span>
                        </dd>

                        <dt class="col-sm-3">Grupo de Exigência:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-info">{{ $templateDocumento->grupoExigencia->nome }}</span>
                        </dd>

                        <dt class="col-sm-3">Obrigatório:</dt>
                        <dd class="col-sm-9">
                            @if($templateDocumento->is_obrigatorio)
                                <span class="badge badge-danger">Sim</span>
                            @else
                                <span class="badge badge-secondary">Não</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Ordem:</dt>
                        <dd class="col-sm-9">{{ $templateDocumento->ordem }}</dd>

                        @if($templateDocumento->descricao)
                        <dt class="col-sm-3">Descrição:</dt>
                        <dd class="col-sm-9">{{ $templateDocumento->descricao }}</dd>
                        @endif

                        @if($templateDocumento->instrucoes_preenchimento)
                        <dt class="col-sm-3">Instruções:</dt>
                        <dd class="col-sm-9">{{ $templateDocumento->instrucoes_preenchimento }}</dd>
                        @endif

                        <dt class="col-sm-3">Criado em:</dt>
                        <dd class="col-sm-9">{{ $templateDocumento->created_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tools mr-1"></i>Ações</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('template-documentos.edit', $templateDocumento) }}" 
                           class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-edit mr-1"></i>Editar
                        </a>

                        <form action="{{ route('template-documentos.destroy', $templateDocumento) }}" 
                              method="POST" 
                              onsubmit="return confirm('Tem certeza que deseja excluir este template?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-trash mr-1"></i>Excluir
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if($templateDocumento->caminho_modelo_storage || $templateDocumento->exemplo_preenchido)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-download mr-1"></i>Downloads</h3>
                </div>
                <div class="card-body">
                    @if($templateDocumento->caminho_modelo_storage)
                        <a href="{{ route('template-documentos.download-modelo', $templateDocumento) }}" 
                           class="btn btn-primary btn-block mb-2">
                            <i class="fas fa-file-download mr-1"></i>Baixar Modelo
                        </a>
                    @endif

                    @if($templateDocumento->exemplo_preenchido)
                        <a href="{{ route('template-documentos.download-exemplo', $templateDocumento) }}" 
                           class="btn btn-success btn-block">
                            <i class="fas fa-file-alt mr-1"></i>Baixar Exemplo
                        </a>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection 