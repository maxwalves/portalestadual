@extends('adminlte::page')

@section('title', 'Tipos de Documento')

@section('content_header')
    <h1><i class="fas fa-file-alt mr-2"></i>Tipos de Documento</h1>
    <p class="text-muted">Gerenciamento de tipos de documentos aceitos pelo sistema</p>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                Lista de Tipos de Documento 
                <span class="badge badge-light ml-2">{{ $tiposDocumento->total() }}</span>
            </h3>
            <div class="card-tools">
                <a href="{{ route('tipos-documento.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Tipo
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if($tiposDocumento->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 120px;">Código</th>
                                <th>Nome</th>
                                <th style="width: 130px;">Categoria</th>
                                <th style="width: 120px;">Extensões</th>
                                <th style="width: 100px;">Tam. Máx</th>
                                <th style="width: 80px;" class="text-center">Assinatura</th>
                                <th style="width: 80px;" class="text-center">Status</th>
                                <th style="width: 160px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tiposDocumento as $tipo)
                                <tr>
                                    <td>
                                        <code class="text-primary">{{ $tipo->codigo }}</code>
                                    </td>
                                    <td>
                                        <strong>{{ $tipo->nome }}</strong>
                                        @if($tipo->descricao)
                                            <br>
                                            <small class="text-muted">{{ Str::limit($tipo->descricao, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($tipo->categoria)
                                            @php
                                                $badgeColor = match($tipo->categoria) {
                                                    'PROJETO' => 'primary',
                                                    'FINANCEIRO' => 'success',
                                                    'LICENCA' => 'warning',
                                                    'JURIDICO' => 'danger',
                                                    'TECNICO' => 'info',
                                                    'ADMINISTRATIVO' => 'secondary',
                                                    default => 'light'
                                                };
                                            @endphp
                                            <span class="badge badge-{{ $badgeColor }}">{{ $tipo->categoria }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($tipo->extensoes_permitidas)
                                            <small class="text-muted">{{ $tipo->extensoes_permitidas }}</small>
                                        @else
                                            <span class="text-success">
                                                <small><i class="fas fa-check"></i> Todas</small>
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $tipo->tamanho_maximo_mb }} MB</span>
                                    </td>
                                    <td class="text-center">
                                        @if($tipo->requer_assinatura)
                                            <i class="fas fa-signature text-warning" title="Requer assinatura digital"></i>
                                        @else
                                            <i class="fas fa-times text-muted" title="Não requer assinatura"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($tipo->is_ativo)
                                            <span class="badge badge-success">Ativo</span>
                                        @else
                                            <span class="badge badge-danger">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('tipos-documento.show', $tipo) }}" 
                                               class="btn btn-info btn-sm" 
                                               title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('tipos-documento.edit', $tipo) }}" 
                                               class="btn btn-warning btn-sm"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('tipos-documento.toggle-ativo', $tipo) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-{{ $tipo->is_ativo ? 'secondary' : 'success' }} btn-sm"
                                                        title="{{ $tipo->is_ativo ? 'Desativar' : 'Ativar' }}"
                                                        onclick="return confirm('Tem certeza que deseja {{ $tipo->is_ativo ? 'desativar' : 'ativar' }} este tipo?')">
                                                    <i class="fas fa-{{ $tipo->is_ativo ? 'eye-slash' : 'eye' }}"></i>
                                                </button>
                                            </form>
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
                    <h5 class="text-muted">Nenhum tipo de documento encontrado</h5>
                    <p class="text-muted">Comece criando o primeiro tipo de documento.</p>
                    <a href="{{ route('tipos-documento.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Criar Primeiro Tipo
                    </a>
                </div>
            @endif
        </div>
        
        @if($tiposDocumento->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">
                            Mostrando {{ $tiposDocumento->firstItem() }} até {{ $tiposDocumento->lastItem() }} 
                            de {{ $tiposDocumento->total() }} registros
                        </small>
                    </div>
                    <div>
                        {{ $tiposDocumento->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
<style>
    .table td {
        vertical-align: middle;
    }
    
    .btn-group .btn {
        margin: 0 1px;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    code {
        font-size: 0.85em;
        padding: 2px 6px;
    }
    
    .table-responsive {
        border: none;
    }
    
    .thead-light th {
        border-top: none;
        font-weight: 600;
        font-size: 0.9em;
    }
    
    /* Estilos para paginação */
    .pagination {
        margin-bottom: 0;
    }
    
    .pagination .page-link {
        color: #007bff;
        border-color: #dee2e6;
        padding: 0.375rem 0.75rem;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }
    
    .pagination .page-link:hover {
        color: #0056b3;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    
    .card-footer {
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
        padding: 1rem;
    }
</style>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        $('[title]').tooltip();
    });
</script>
@stop
