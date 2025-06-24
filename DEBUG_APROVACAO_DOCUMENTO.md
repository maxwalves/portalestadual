# Debug: Problema na Aprovação de Documentos com Comentários

## Problema Relatado

Quando o usuário tenta aprovar um documento inserindo um comentário, as informações não persistem. Sem comentário, a aprovação funciona normalmente.

## Logs Adicionados

### Backend (ExecucaoEtapaController.php)
- Log antes da validação com dados recebidos
- Log dos dados que serão salvos
- Log após a atualização para verificar se foi persistido

### Frontend (etapa-detalhada.blade.php)
- Console.log dos dados antes do envio
- Console.log da resposta do servidor
- Console.log de erros detalhados

## Como Investigar

### 1. Verificar Console do Navegador
1. Abrir DevTools (F12)
2. Ir para a aba "Console"
3. Tentar aprovar um documento com comentário
4. Verificar se os logs mostram:
   - Documento ID correto
   - Observações sendo capturadas
   - Dados sendo enviados corretamente

### 2. Verificar Logs do Laravel
```bash
tail -f storage/logs/laravel.log
```

Procurar por:
- "Iniciando aprovação de documento"
- "Dados para atualização do documento"
- "Documento após atualização"

### 3. Verificar Banco de Dados
```sql
SELECT 
    id, 
    status_documento, 
    observacoes, 
    data_aprovacao, 
    usuario_aprovacao_id,
    updated_at
FROM documentos 
WHERE id = [ID_DO_DOCUMENTO]
ORDER BY updated_at DESC;
```

## Possíveis Causas

### 1. Problema no Frontend
- Campo observacoes não está sendo capturado corretamente
- Dados não estão sendo enviados no POST

### 2. Problema na Validação
- Regra de validação muito restritiva
- Caracteres especiais no comentário

### 3. Problema no Banco
- Campo observacoes não existe na tabela
- Permissões de escrita

### 4. Problema na Transação
- Rollback silencioso da transação
- Erro após o update que desfaz as alterações

## Testes para Executar

### Teste 1: Sem Comentário
- Aprovar documento sem observações
- Verificar se funciona normalmente

### Teste 2: Com Comentário Simples
- Usar texto simples: "Documento aprovado"
- Verificar se persiste

### Teste 3: Com Caracteres Especiais
- Usar acentos, símbolos, quebras de linha
- Verificar se há erro de encoding

### Teste 4: Comentário Longo
- Usar texto próximo ao limite (1000 chars)
- Verificar se há problema de tamanho

## Correções Esperadas

Baseado nos logs, identificaremos se o problema está:
1. Na captura dos dados (frontend)
2. No processamento (backend) 
3. Na persistência (banco de dados)

Depois removeremos os logs de debug e aplicaremos a correção específica. 