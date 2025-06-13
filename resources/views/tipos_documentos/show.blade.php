@extends('adminlte::page')

@section('title', 'Tipo de Documento: ' . $tipoDocumento->nome)

@section('content_header')
    <h1>Tipo de Documento: {{ $tipoDocumento->nome }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <!-- Informações Básicas -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informações Básicas</h3>
                    <div class="card-tools">
                        <a href="{{ route('tipos-documento.edit', $tipoDocumento) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('tipos-documento.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Código:</strong>
                            <p class="text-muted"><code>{{ $tipoDocumento->codigo }}</code></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <p>
                                @if($tipoDocumento->is_ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-danger">Inativo</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>Categoria:</strong>
                            <p class="text-muted">
                                @if($tipoDocumento->categoria)
                                    <span class="badge badge-info">{{ $tipoDocumento->categoria }}</span>
                                @else
                                    <span class="text-muted">Não definida</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Requer Assinatura:</strong>
                            <p>
                                @if($tipoDocumento->requer_assinatura)
                                    <i class="fas fa-signature text-warning"></i> Sim
                                @else
                                    <i class="fas fa-times text-muted"></i> Não
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($tipoDocumento->descricao)
                        <div class="row">
                            <div class="col-12">
                                <strong>Descrição:</strong>
                                <p class="text-muted">{{ $tipoDocumento->descricao }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <strong>Extensões Permitidas:</strong>
                            <p class="text-muted">
                                @if($tipoDocumento->extensoes_permitidas)
                                    {{ $tipoDocumento->extensoes_permitidas }}
                                @else
                                    <span class="text-success">Todas as extensões</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Tamanho Máximo:</strong>
                            <p class="text-muted">
                                <span class="badge badge-info">{{ $tipoDocumento->tamanho_maximo_mb }} MB</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Templates Vinculados -->
            @if($tipoDocumento->templatesDocumento->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Templates Vinculados ({{ $tipoDocumento->templatesDocumento->count() }})</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Grupo de Exigência</th>
                                    <th>Obrigatório</th>
                                    <th>Ordem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tipoDocumento->templatesDocumento as $template)
                                    <tr>
                                        <td>{{ $template->nome }}</td>
                                        <td>
                                            @if($template->grupoExigencia)
                                                {{ $template->grupoExigencia->nome }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($template->is_obrigatorio)
                                                <span class="badge badge-warning">Obrigatório</span>
                                            @else
                                                <span class="badge badge-secondary">Opcional</span>
                                            @endif
                                        </td>
                                        <td>{{ $template->ordem }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Documentos Recentes -->
            @if($tipoDocumento->documentos->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Documentos Recentes (últimos 10)</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nome do Arquivo</th>
                                    <th>Tamanho</th>
                                    <th>Data Upload</th>
                                    <th>Usuário</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tipoDocumento->documentos as $documento)
                                    <tr>
                                        <td>{{ $documento->nome_arquivo }}</td>
                                        <td>{{ number_format($documento->tamanho_bytes / 1024 / 1024, 2) }} MB</td>
                                        <td>{{ $documento->data_upload->format('d/m/Y H:i') }}</td>
                                        <td>{{ $documento->usuarioUpload->name ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Estatísticas -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Estatísticas</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-file"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total de Documentos</span>
                                    <span class="info-box-number">{{ $tipoDocumento->documentos()->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-file-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Templates</span>
                                    <span class="info-box-number">{{ $tipoDocumento->templatesDocumento->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Auditoria -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Auditoria</h3>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>Criado em:</strong><br>
                        {{ $tipoDocumento->created_at->format('d/m/Y H:i:s') }}
                    </small>
                    <br><br>
                    <small class="text-muted">
                        <strong>Última atualização:</strong><br>
                        {{ $tipoDocumento->updated_at->format('d/m/Y H:i:s') }}
                    </small>
                </div>
            </div>

            <!-- Ações -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ações</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('tipos-documento.edit', $tipoDocumento) }}" class="btn btn-warning btn-block">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    
                    <form action="{{ route('tipos-documento.toggle-ativo', $tipoDocumento) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-{{ $tipoDocumento->is_ativo ? 'secondary' : 'success' }} btn-block">
                            <i class="fas fa-{{ $tipoDocumento->is_ativo ? 'eye-slash' : 'eye' }}"></i>
                            {{ $tipoDocumento->is_ativo ? 'Desativar' : 'Ativar' }}
                        </button>
                    </form>

                    @if($tipoDocumento->documentos()->count() == 0 && $tipoDocumento->templatesDocumento()->count() == 0)
                        <form action="{{ route('tipos-documento.destroy', $tipoDocumento) }}" method="POST" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block" 
                                    onclick="return confirm('Tem certeza que deseja excluir este tipo de documento?')">
                                <i class="fas fa-trash"></i> Excluir
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop 