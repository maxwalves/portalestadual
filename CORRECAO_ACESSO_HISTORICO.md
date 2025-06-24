# Correção de Acesso ao Histórico de Etapas

## Problema Identificado

Os técnicos da SECID e SEED estavam sendo impedidos de acessar o histórico de etapas, mesmo sendo envolvidos no projeto. O sistema estava usando uma validação muito restritiva.

## Causa Raiz

O método `historicoEtapa()` no `ExecucaoEtapaController` estava usando `canAccessOrganizacao()` que só permite acesso à organização que criou a ação original (termo de adesão). 

Isso impedia que organizações participantes de etapas específicas (como SECID e SEED) acessassem o histórico, mesmo fazendo parte do workflow.

## Solução Aplicada

### Alteração 1: Método `historicoEtapa()`

**Antes:**
```php
if (!$this->canAccessOrganizacao($execucao->acao->demanda->termoAdesao->organizacao_id)) {
    abort(403, 'Acesso negado a este histórico.');
}
```

**Depois:**
```php
if (!$this->canAccessAcao($execucao->acao)) {
    abort(403, 'Acesso negado a este histórico.');
}
```

### Alteração 2: Método `getOpcoesStatus()`

**Antes:**
```php
if (!$this->canAccessOrganizacao($execucao->acao->demanda->termoAdesao->organizacao_id)) {
    return response()->json(['error' => 'Acesso negado'], 403);
}
```

**Depois:**
```php
if (!$this->canAccessAcao($execucao->acao)) {
    return response()->json(['error' => 'Acesso negado'], 403);
}
```

### Alteração 3: Controle de Visibilidade do Botão

**Adicionada lógica para ocultar botão de histórico para usuários não envolvidos na etapa:**

```php
// Verificar se pode acessar histórico (específico para usuários envolvidos na etapa)
$podeVerHistorico = false;
if ($execucao && $podeVisualizar) {
    $userOrgId = $user->organizacao_id;
    $pertenceEtapa = ($userOrgId === $etapaFluxo->organizacao_solicitante_id) || 
                    ($userOrgId === $etapaFluxo->organizacao_executora_id);
    $podeVerHistorico = $pertenceEtapa || $user->hasRole(['admin', 'admin_paranacidade']);
}
```

**Templates atualizados para usar a nova condição:**
```blade
@if($execucao && $execucao->id && $podeVerHistorico)
    <a href="{{ route('workflow.historico-etapa', $execucao->id) }}" 
       class="btn btn-outline-secondary btn-sm" title="Histórico">
        <i class="fas fa-history"></i>
    </a>
@endif
```

## Diferença Entre os Métodos

### `canAccessOrganizacao()`
- ✅ Permite apenas à organização que criou o termo de adesão
- ❌ Bloqueia outras organizações envolvidas nas etapas

### `canAccessAcao()`
- ✅ Permite à organização que criou o termo de adesão
- ✅ Permite às organizações solicitantes de qualquer etapa
- ✅ Permite às organizações executoras de qualquer etapa
- ✅ Sempre permite admins e PARANACIDADE

## Benefícios da Correção

1. **Transparência Completa**: Todos os envolvidos no projeto podem ver o histórico completo
2. **Auditoria Acessível**: Permite rastreabilidade para todas as partes interessadas
3. **Comunicação Melhorada**: Todos podem acompanhar o progresso e decisões
4. **Consistência**: Alinhado com a lógica de visualização das etapas

## Cenários Corrigidos

### Cenário 1: Usuário SECID
- **Antes**: ❌ Bloqueado para ver histórico de projetos SEED
- **Depois**: ✅ Pode ver histórico de etapas onde SECID participa

### Cenário 2: Usuário SEED  
- **Antes**: ❌ Bloqueado para ver histórico completo
- **Depois**: ✅ Pode ver histórico de todas as etapas do projeto

### Cenário 3: Usuário PARANACIDADE
- **Antes**: ✅ Sempre tinha acesso (admin)
- **Depois**: ✅ Mantém acesso total (inalterado)

## Arquivos Modificados

- `app/Http/Controllers/ExecucaoEtapaController.php`
  - Método `historicoEtapa()` (linha ~629) 
  - Método `getOpcoesStatus()` (linha ~863)
  - Método `workflow()` - Adicionada lógica `pode_ver_historico`
  - Método `etapaDetalhada()` - Adicionada validação `podeVerHistorico`

- `resources/views/workflow/acao.blade.php`
  - Condição do botão histórico (linha ~188)

- `resources/views/workflow/etapa-detalhada.blade.php`  
  - Condição do botão histórico (linha ~306)

## Validação de Segurança

A alteração **NÃO** compromete a segurança porque:

1. Ainda valida se o usuário pertence ao projeto
2. Mantém validação de organizações por etapa para ações
3. Admins continuam com controle total
4. Apenas amplia visibilidade do histórico, não permissões de alteração

## Data da Correção

**Data**: Janeiro 2025  
**Solicitante**: Usuário do sistema  
**Motivo**: Técnicos não conseguiam acessar histórico das etapas 