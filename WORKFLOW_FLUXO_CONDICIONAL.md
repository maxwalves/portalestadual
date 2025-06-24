# Sistema de Fluxo Condicional - Portal de Obras Estaduais

## Resumo da Implementação

Sistema completo de **fluxo condicional** onde o usuário demandante escolhe manualmente a próxima etapa do processo, eliminando a aprovação automática e permitindo workflows não-sequenciais.

## 🎯 **Problema Resolvido**

### **ANTES**: Fluxo Automático
- ✅ Todos documentos aprovados → Etapa automaticamente aprovada
- ❌ Próxima etapa iniciada automaticamente (sequencial)
- ❌ Sem controle do usuário sobre direcionamento

### **DEPOIS**: Fluxo Condicional
- ✅ Todos documentos aprovados → Usuário escolhe destino
- ✅ **Botão "Escolher Próxima Etapa"** aparece
- ✅ **Modal elegante** com opções disponíveis  
- ✅ **Suporte a múltiplas rotas** (não-sequencial)
- ✅ **Histórico completo** das decisões

---

## 🔧 **Implementações Técnicas**

### **1. Controller - Novas Funcionalidades**

#### **Remoção da Aprovação Automática**
```php
// ANTES: app/Http/Controllers/ExecucaoEtapaController.php
private function verificarConclusaoEtapa(ExecucaoEtapa $execucao) {
    // Aprovava automaticamente quando todos documentos OK
    $execucao->update(['status_id' => $statusAprovado->id]);
}

// DEPOIS: 
private function verificarConclusaoEtapa(ExecucaoEtapa $execucao) {
    // Não faz mais nada - escolha é manual
    \Log::info('Aprovação automática removida - aguardando escolha manual');
}
```

#### **Nova Função: Verificar Documentos Aprovados**
```php
private function todosDocumentosAprovados(ExecucaoEtapa $execucao): bool
{
    // Verifica se todos documentos obrigatórios estão aprovados
    // sem alterar automaticamente o status da etapa
}
```

#### **Nova Rota: Buscar Opções de Transição**
```php
public function getOpcoesTransicao(ExecucaoEtapa $execucao)
{
    // 1. Verificar permissões (organização solicitante)
    // 2. Verificar se todos documentos aprovados
    // 3. Buscar transições disponíveis na tabela transicao_etapas
    // 4. Filtrar etapas já iniciadas
    // 5. Retornar opções em JSON
}
```

#### **Nova Rota: Executar Transição Escolhida**
```php
public function executarTransicaoEscolhida(Request $request, ExecucaoEtapa $execucao)
{
    // 1. Validar dados (transicao_id, observações)
    // 2. Atualizar etapa atual para APROVADO
    // 3. Criar nova execução na etapa destino
    // 4. Registrar histórico completo
    // 5. Retornar sucesso
}
```

### **2. Sistema de Permissões Atualizado**

#### **Nova Permissão: `pode_escolher_proxima_etapa`**
```php
$permissoes = [
    'pode_iniciar_etapa' => false,
    'pode_enviar_documento' => false,
    'pode_aprovar_documento' => false,
    'pode_concluir_etapa' => false,
    'pode_escolher_proxima_etapa' => false  // NOVA
];

// Lógica:
if ($userOrgId === $etapaAtual->organizacao_solicitante_id && 
    $this->todosDocumentosAprovados($execucao) && 
    in_array($execucao->status->codigo, ['PENDENTE', 'EM_ANALISE'])) {
    $permissoes['pode_escolher_proxima_etapa'] = true;
}
```

### **3. Rotas Adicionadas**

```php
// routes/web.php
Route::get('workflow/execucao/{execucao}/opcoes-transicao', 
    [ExecucaoEtapaController::class, 'getOpcoesTransicao'])
    ->name('workflow.opcoes-transicao');

Route::post('workflow/execucao/{execucao}/executar-transicao', 
    [ExecucaoEtapaController::class, 'executarTransicaoEscolhida'])
    ->name('workflow.executar-transicao');
```

---

## 🎨 **Interface do Usuário**

### **1. Botão Inteligente**

```blade
@if($permissoes['pode_escolher_proxima_etapa'] ?? false)
    <!-- NOVO: Fluxo condicional -->
    <button class="btn btn-primary btn-lg mr-2" onclick="escolherProximaEtapa()">
        <i class="fas fa-route"></i> Escolher Próxima Etapa
    </button>
@else
    <!-- FALLBACK: Fluxo tradicional -->
    <button class="btn btn-success btn-lg mr-2" onclick="alterarStatusEtapa()">
        <i class="fas fa-check-circle"></i> Concluir Etapa
    </button>
@endif
```

### **2. Modal Elegante: `escolher-proxima-etapa.blade.php`**

#### **Estrutura Visual**
- **Header azul** com ícone de rota
- **Loading spinner** enquanto carrega opções
- **Cards clicáveis** para cada opção de transição
- **Animações CSS** suaves (hover, seleção)
- **Campo de observações** opcional

#### **Informações Exibidas por Opção**
- ✅ **Nome da etapa destino**
- ✅ **Descrição da transição**
- ✅ **Organização executora**
- ✅ **Status/condição**
- ✅ **Badge de prioridade** (cores dinâmicas)

### **3. JavaScript Interativo**

#### **Fluxo da Interação**
1. **Clique no botão** → Modal abre
2. **Loading** → AJAX para `/opcoes-transicao`  
3. **Renderização** → Cards das opções aparecem
4. **Seleção** → Click no card (visual feedback)
5. **Confirmação** → AJAX para `/executar-transicao`
6. **Sucesso** → SweetAlert + reload da página

---

## 🔄 **Fluxo de Dados**

### **Estrutura da Resposta JSON - Opções**
```json
{
    "success": true,
    "opcoes": [
        {
            "transicao_id": 1,
            "etapa_destino_id": 5,
            "etapa_destino_nome": "Análise Técnica SECID",
            "descricao": "Encaminhar para análise técnica detalhada",
            "status_condicao": "Aprovado",
            "prioridade": 10,
            "organizacao_executora": "SECID - Secretaria de Cidade"
        }
    ],
    "etapa_atual": {
        "id": 3,
        "nome": "Envio de Documentos"
    }
}
```

---

## 🔐 **Segurança e Validações**

### **Validações de Permissão**
1. ✅ **Organização**: Apenas solicitante pode escolher
2. ✅ **Documentos**: Todos obrigatórios devem estar aprovados
3. ✅ **Status**: Etapa deve estar PENDENTE ou EM_ANALISE
4. ✅ **Transição**: Deve pertencer à etapa atual
5. ✅ **Duplicação**: Etapa destino não pode já estar iniciada

---

## 🚀 **Benefícios Implementados**

### **Para o Usuário**
- ✅ **Controle total** sobre direcionamento do fluxo
- ✅ **Interface intuitiva** para tomada de decisão
- ✅ **Feedback visual** claro das opções
- ✅ **Histórico completo** das escolhas realizadas

### **Para o Sistema**
- ✅ **Flexibilidade** de workflows não-sequenciais
- ✅ **Rastreabilidade** completa das transições
- ✅ **Segurança** com validações rigorosas
- ✅ **Performance** com carregamento assíncrono

---

## 📋 **Conclusão Técnica**

O sistema de **fluxo condicional** elimina completamente a aprovação automática, dando controle total ao usuário demandante sobre o direcionamento do processo. A implementação mantém backward compatibility enquanto oferece uma experiência moderna e flexível para workflows complexos.

**Resultado**: Sistema preparado para **fluxos não-sequenciais** com **máxima rastreabilidade** e **controle do usuário**.

---

## 🔗 **Arquivos Principais Modificados**

1. **`app/Http/Controllers/ExecucaoEtapaController.php`** - Lógica do fluxo condicional
2. **`routes/web.php`** - Novas rotas
3. **`resources/views/workflow/etapa-detalhada.blade.php`** - Interface e JavaScript
4. **`resources/views/workflow/modals/escolher-proxima-etapa.blade.php`** - Modal novo

**Status**: ✅ **Implementação Completa e Funcional**
