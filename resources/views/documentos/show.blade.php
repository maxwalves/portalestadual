@extends('adminlte::page')

@section('title', 'Documento: ' . $documento->nome_arquivo)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-alt mr-2"></i>Documento: {{ Str::limit($documento->nome_arquivo, 40) }}</h1>
        <a href="{{ route('documentos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Voltar
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i>Informações do Documento</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Nome do Arquivo:</dt>
                        <dd class="col-sm-9">{{ $documento->nome_arquivo }}</dd>

                        <dt class="col-sm-3">Tipo:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-secondary">{{ $documento->tipoDocumento->nome }}</span>
                        </dd>

                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-9">
                            @php
                                $statusColor = match($documento->status_documento) {
                                    'PENDENTE' => 'secondary',
                                    'EM_ANALISE' => 'warning',
                                    'APROVADO' => 'success',
                                    'REPROVADO' => 'danger',
                                    'EXPIRADO' => 'dark',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge badge-{{ $statusColor }}">{{ $documento->status_documento }}</span>
                        </dd>

                        <dt class="col-sm-3">Tamanho:</dt>
                        <dd class="col-sm-9">{{ number_format($documento->tamanho_bytes / 1024 / 1024, 2) }} MB</dd>

                        <dt class="col-sm-3">Versão:</dt>
                        <dd class="col-sm-9">{{ $documento->versao }}</dd>

                        <dt class="col-sm-3">Usuário:</dt>
                        <dd class="col-sm-9">{{ $documento->usuarioUpload->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Data de Upload:</dt>
                        <dd class="col-sm-9">{{ $documento->created_at->format('d/m/Y H:i') }}</dd>

                        @if($documento->observacoes)
                        <dt class="col-sm-3">Observações:</dt>
                        <dd class="col-sm-9">{{ $documento->observacoes }}</dd>
                        @endif

                        @if($documento->motivo_reprovacao)
                        <dt class="col-sm-3">Motivo da Reprovação:</dt>
                        <dd class="col-sm-9">
                            <div class="alert alert-danger">{{ $documento->motivo_reprovacao }}</div>
                        </dd>
                        @endif
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
                        <a href="{{ route('documentos.download', $documento) }}" 
                           class="btn btn-primary btn-block mb-2">
                            <i class="fas fa-download mr-1"></i>Download
                        </a>

                        <a href="{{ route('documentos.edit', $documento) }}" 
                           class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-edit mr-1"></i>Editar
                        </a>

                        <form action="{{ route('documentos.destroy', $documento) }}" 
                              method="POST" 
                              onsubmit="return confirm('Tem certeza que deseja excluir este documento?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-trash mr-1"></i>Excluir
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if($documento->execucaoEtapa && $documento->execucaoEtapa->acao)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-project-diagram mr-1"></i>Ação Relacionada</h3>
                </div>
                <div class="card-body">
                    <p><strong>{{ $documento->execucaoEtapa->acao->nome }}</strong></p>
                    @if($documento->execucaoEtapa->acao->descricao)
                        <p class="text-muted">{{ Str::limit($documento->execucaoEtapa->acao->descricao, 100) }}</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection 