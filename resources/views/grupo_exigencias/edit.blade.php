@extends('adminlte::page')

@section('title', 'Editar Grupo de Exigência')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">
                <i class="fas fa-edit text-warning mr-2"></i>
                Editar Grupo de Exigência
            </h1>
            <small class="text-muted">Alterar informações do grupo: {{ $grupoExigencia->nome }}</small>
        </div>
        <div>
            <a href="{{ route('grupo-exigencias.show', $grupoExigencia) }}" class="btn btn-info mr-2">
                <i class="fas fa-eye mr-1"></i>
                Visualizar
            </a>
            <a href="{{ route('grupo-exigencias.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Voltar
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit mr-2"></i>
                Editar Informações
            </h3>
        </div>
        
        <form action="{{ route('grupo-exigencias.update', $grupoExigencia) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="nome" class="required">
                                <i class="fas fa-tag mr-1"></i>
                                Nome do Grupo:
                            </label>
                            <input type="text" 
                                   name="nome" 
                                   id="nome" 
                                   class="form-control @error('nome') is-invalid @enderror" 
                                   placeholder="Ex: Documentação Básica, Licenciamento Ambiental..."
                                   value="{{ old('nome', $grupoExigencia->nome) }}" 
                                   required
                                   maxlength="255">
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-lightbulb mr-1"></i>
                                Use um nome descritivo que facilite a identificação do grupo.
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="is_ativo">
                                <i class="fas fa-toggle-on mr-1"></i>
                                Status:
                            </label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_ativo" 
                                       name="is_ativo" 
                                       value="1" 
                                       {{ old('is_ativo', $grupoExigencia->is_ativo) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_ativo">
                                    Ativo
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Grupos inativos não aparecem nas opções de seleção.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descricao">
                        <i class="fas fa-align-left mr-1"></i>
                        Descrição:
                    </label>
                    <textarea name="descricao" 
                              id="descricao" 
                              class="form-control @error('descricao') is-invalid @enderror" 
                              placeholder="Descreva o propósito e conteúdo deste grupo de exigências..."
                              rows="4"
                              maxlength="1000">{{ old('descricao', $grupoExigencia->descricao) }}</textarea>
                    @error('descricao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Explique quais tipos de documentos estarão neste grupo e em que situações será usado.
                    </small>
                </div>

                <!-- Informações sobre uso atual -->
                @if($grupoExigencia->templates_documento_count > 0 || $grupoExigencia->etapas_fluxo_count > 0)
                    <div class="alert alert-warning">
                        <h5 class="alert-heading">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Atenção: Este grupo está sendo usado
                        </h5>
                        <ul class="mb-0">
                            @if($grupoExigencia->templates_documento_count > 0)
                                <li><strong>{{ $grupoExigencia->templates_documento_count }}</strong> template(s) de documento associado(s)</li>
                            @endif
                            @if($grupoExigencia->etapas_fluxo_count > 0)
                                <li><strong>{{ $grupoExigencia->etapas_fluxo_count }}</strong> etapa(s) de fluxo usando este grupo</li>
                            @endif
                        </ul>
                        <small class="d-block mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Alterações no nome e descrição não afetarão os itens já associados.
                        </small>
                    </div>
                @endif

                <!-- Informações de auditoria -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-primary">
                                <i class="fas fa-calendar-plus"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Criado em</span>
                                <span class="info-box-number">{{ $grupoExigencia->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Última atualização</span>
                                <span class="info-box-number">{{ $grupoExigencia->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="{{ route('grupo-exigencias.show', $grupoExigencia) }}" class="btn btn-info">
                            <i class="fas fa-eye mr-1"></i>
                            Visualizar
                        </a>
                        <a href="{{ route('grupo-exigencias.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </a>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i>
                        Salvar Alterações
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Templates Associados (se houver) -->
    @if($grupoExigencia->templatesDocumento->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt mr-2"></i>
                    Templates de Documentos Associados ({{ $grupoExigencia->templatesDocumento->count() }})
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Template</th>
                                <th>Tipo Documento</th>
                                <th class="text-center">Obrigatório</th>
                                <th class="text-center">Ordem</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grupoExigencia->templatesDocumento->sortBy('ordem') as $template)
                                <tr>
                                    <td>
                                        <strong>{{ $template->nome }}</strong>
                                        @if($template->descricao)
                                            <br><small class="text-muted">{{ Str::limit($template->descricao, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $template->tipoDocumento->nome }}</td>
                                    <td class="text-center">
                                        @if($template->is_obrigatorio)
                                            <span class="badge badge-danger">Sim</span>
                                        @else
                                            <span class="badge badge-secondary">Não</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $template->ordem }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('template-documentos.edit', $template) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('template-documentos.create', ['grupo_exigencia_id' => $grupoExigencia->id]) }}" 
                   class="btn btn-success">
                    <i class="fas fa-plus mr-1"></i>
                    Adicionar Template
                </a>
            </div>
        </div>
    @endif
@stop

@section('css')
<style>
    .required::after {
        content: " *";
        color: #dc3545;
        font-weight: bold;
    }
    
    .alert-heading {
        font-size: 1.1rem;
    }
    
    .info-box {
        margin-bottom: 1rem;
    }
</style>
@stop

@section('js')
<script>
    // Contador de caracteres
    const nomeInput = document.getElementById('nome');
    const descricaoInput = document.getElementById('descricao');
    
    function updateCharCounter(input, maxLength) {
        const current = input.value.length;
        const remaining = maxLength - current;
        
        let counter = input.parentNode.querySelector('.char-counter');
        if (!counter) {
            counter = document.createElement('small');
            counter.className = 'form-text text-right char-counter';
            input.parentNode.appendChild(counter);
        }
        
        counter.textContent = `${current}/${maxLength} caracteres`;
        counter.style.color = remaining < 50 ? '#dc3545' : '#6c757d';
    }
    
    nomeInput.addEventListener('input', () => updateCharCounter(nomeInput, 255));
    descricaoInput.addEventListener('input', () => updateCharCounter(descricaoInput, 1000));
    
    // Inicializar contadores
    updateCharCounter(nomeInput, 255);
    updateCharCounter(descricaoInput, 1000);
    
    // Confirmação se há alterações não salvas
    let formChanged = false;
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        input.addEventListener('change', () => {
            formChanged = true;
        });
    });
    
    window.addEventListener('beforeunload', (e) => {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    
    form.addEventListener('submit', () => {
        formChanged = false;
    });
</script>
@stop 