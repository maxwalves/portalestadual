@extends('adminlte::page')

@section('title', 'Novo Grupo de Exigência')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">
                <i class="fas fa-plus text-success mr-2"></i>
                Novo Grupo de Exigência
            </h1>
            <small class="text-muted">Criar um novo grupo para organizar exigências documentais</small>
        </div>
        <a href="{{ route('grupo-exigencias.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>
            Voltar
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle mr-2"></i>
                Informações do Grupo
            </h3>
        </div>
        
        <form action="{{ route('grupo-exigencias.store') }}" method="POST">
            @csrf
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
                                   value="{{ old('nome') }}" 
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
                                       {{ old('is_ativo', true) ? 'checked' : '' }}>
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
                              maxlength="1000">{{ old('descricao') }}</textarea>
                    @error('descricao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Explique quais tipos de documentos estarão neste grupo e em que situações será usado.
                    </small>
                </div>

                <!-- Dicas de Criação -->
                <div class="alert alert-info">
                    <h5 class="alert-heading">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Dicas para criar um bom grupo de exigências:
                    </h5>
                    <ul class="mb-0">
                        <li><strong>Nome claro:</strong> Use nomes que indiquem claramente o propósito</li>
                        <li><strong>Organização lógica:</strong> Agrupe documentos que são solicitados em conjunto</li>
                        <li><strong>Descrição detalhada:</strong> Explique quando e por que este grupo será usado</li>
                        <li><strong>Templates:</strong> Após criar o grupo, você poderá adicionar templates de documentos específicos</li>
                    </ul>
                </div>

                <!-- Preview de próximos passos -->
                <div class="callout callout-success">
                    <h5>
                        <i class="fas fa-tasks mr-2"></i>
                        Próximos passos após criar o grupo:
                    </h5>
                    <p class="mb-0">
                        1. Adicionar templates de documentos ao grupo<br>
                        2. Configurar quais são obrigatórios e opcionais<br>
                        3. Definir ordem de apresentação<br>
                        4. Associar o grupo às etapas de fluxo apropriadas
                    </p>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('grupo-exigencias.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i>
                        Criar Grupo
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Card de Exemplos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-lightbulb mr-2"></i>
                Exemplos de Grupos Comuns
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box bg-gradient-primary">
                        <span class="info-box-icon">
                            <i class="fas fa-file-medical"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Documentação Básica</span>
                            <span class="info-box-number">Para todas as obras</span>
                            <span class="progress-description">
                                ARTs, RRTs, Registros profissionais
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon">
                            <i class="fas fa-leaf"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Licenciamento Ambiental</span>
                            <span class="info-box-number">Obras com impacto</span>
                            <span class="progress-description">
                                Licenças, estudos ambientais
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    
    .progress-description {
        font-size: 0.85rem;
    }
</style>
@stop

@section('js')
<script>
    // Contador de caracteres para o campo nome
    const nomeInput = document.getElementById('nome');
    const descricaoInput = document.getElementById('descricao');
    
    function updateCharCounter(input, maxLength) {
        const current = input.value.length;
        const remaining = maxLength - current;
        
        // Busca ou cria contador
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
</script>
@stop 