# Sistema de Roles e Controle de Acesso por Organizações

## Visão Geral

O Portal de Obras Estaduais implementa um sistema robusto de controle de acesso baseado em **roles (perfis)** e **organizações**, permitindo que diferentes tipos de usuários tenham acesso apropriado aos recursos do sistema conforme sua função e organização.

## Estrutura Organizacional

### Organizações Participantes

1. **PARANACIDADE** - Órgão gestor do sistema
2. **SEED** - Secretaria de Estado da Educação
3. **SESA** - Secretaria de Estado da Saúde  
4. **SECID** - Secretaria de Estado das Cidades
5. **SESP** - Secretaria de Estado da Segurança Pública

## Perfis de Usuário (Roles)

### 1. Admin Sistema (`admin`)
- **Descrição**: Administrador completo do sistema
- **Acesso**: Total - pode gerenciar tudo no sistema
- **Organização**: Qualquer (geralmente Paranacidade)
- **Permissões**:
  - Gestão completa de usuários e roles
  - Configuração do sistema
  - Acesso a todas as organizações
  - Gestão de tipos de fluxo e etapas
  - Gestão documental completa

### 2. Admin Paranacidade (`admin_paranacidade`)
- **Descrição**: Administrador do órgão gestor
- **Acesso**: Gestão completa de obras de todas as secretarias
- **Organização**: PARANACIDADE
- **Permissões**:
  - Visualizar e gerenciar ações de todas as secretarias
  - Criar e editar tipos de fluxo e etapas
  - Gestão completa de documentos e templates
  - Configurar status e notificações
  - Acessar histórico completo

### 3. Técnico Paranacidade (`tecnico_paranacidade`)
- **Descrição**: Técnico do órgão gestor
- **Acesso**: Visualização de todas as ações (somente leitura)
- **Organização**: PARANACIDADE
- **Permissões**:
  - Visualizar ações de todas as secretarias
  - Acompanhar fluxo de trabalho
  - Visualizar documentos e histórico
  - **Não pode editar** nenhuma informação

### 4. Admin Secretaria (`admin_secretaria`)
- **Descrição**: Administrador de uma secretaria específica
- **Acesso**: Gestão completa da sua secretaria
- **Organização**: SEED, SESA, SECID ou SESP
- **Permissões**:
  - Gerenciar usuários da sua secretaria
  - Criar e editar termos de adesão
  - Gerenciar demandas e ações da sua secretaria
  - Upload e gestão de documentos
  - Visualizar histórico da sua secretaria

### 5. Técnico Secretaria (`tecnico_secretaria`)
- **Descrição**: Técnico de uma secretaria específica
- **Acesso**: Visualização dos dados da sua secretaria (somente leitura)
- **Organização**: SEED, SESA, SECID ou SESP
- **Permissões**:
  - Visualizar ações da sua secretaria
  - Acompanhar fluxo de trabalho
  - Visualizar documentos e histórico
  - **Não pode editar** nenhuma informação

## Controle de Acesso por Recurso

### Organizações
- **Acesso**: Apenas Admin Sistema e Admin Paranacidade
- **Funcionalidade**: Gestão das organizações participantes

### Termos de Adesão
- **Admin Sistema/Paranacidade**: Acesso total
- **Admin Secretaria**: Apenas termos da sua organização
- **Técnicos**: Apenas visualização

### Demandas e Ações
- **Admin Sistema/Paranacidade**: Todas as organizações
- **Admin Secretaria**: Apenas da sua organização
- **Técnicos**: Apenas visualização da sua organização

### Tipos de Fluxo e Etapas
- **Acesso**: Apenas Admin Sistema e Admin Paranacidade
- **Funcionalidade**: Configuração dos workflows do sistema

### Gestão Documental
- **Templates e Tipos**: Apenas Admin Sistema e Admin Paranacidade
- **Documentos**: Baseado na organização da ação relacionada

### Status e Notificações
- **Configuração**: Apenas Admin Sistema e Admin Paranacidade
- **Visualização**: Baseado na organização

## Middleware de Segurança

### `CheckOrganizacaoAccess`
Middleware personalizado que:
- Verifica se o usuário tem permissão para acessar o recurso
- Controla acesso baseado na organização
- Diferencia permissões de leitura e escrita
- Aplica regras específicas por tipo de recurso

### Aplicação nas Rotas
```php
// Apenas admins podem gerenciar organizações
Route::middleware(['role:admin|admin_paranacidade'])->group(function () {
    Route::resource('organizacoes', OrganizacaoController::class);
});

// Controle por organização
Route::middleware(['organizacao.access:termo_adesao'])->group(function () {
    Route::resource('termos-adesao', TermoAdesaoController::class);
});
```

## Trait Helper: `HasOrganizacaoAccess`

Fornece métodos auxiliares para controllers:
- `canAccessOrganizacao($organizacaoId)`: Verifica acesso a organização
- `canEdit()`: Verifica permissão de edição
- `canManageWorkflow()`: Verifica gestão de workflow
- `getAccessibleOrganizacoes()`: Retorna organizações acessíveis

## Usuários de Exemplo Criados

### Credenciais (senha: 123456)

1. **Admin Sistema**: admin@sistema.gov.br
2. **Admin Paranacidade**: admin@paranacidade.pr.gov.br
3. **Técnico Paranacidade**: tecnico@paranacidade.pr.gov.br
4. **Admin SEED**: admin@seed.pr.gov.br
5. **Técnico SEED**: tecnico@seed.pr.gov.br
6. **Admin SESA**: admin@sesa.pr.gov.br
7. **Técnico SESA**: tecnico@sesa.pr.gov.br
8. **Admin SECID**: admin@secid.pr.gov.br
9. **Admin SESP**: admin@sesp.pr.gov.br

## Fluxo de Autorização

1. **Autenticação**: Usuário faz login
2. **Verificação de Role**: Middleware verifica o role do usuário
3. **Verificação de Organização**: Para recursos específicos, verifica se o usuário pode acessar a organização
4. **Controle de Método**: Técnicos só podem usar GET/HEAD (visualização)
5. **Autorização**: Acesso liberado ou negado com mensagem específica

## Segurança Implementada

- **Isolamento por Organização**: Usuários só veem dados da sua organização
- **Controle Granular**: Diferentes permissões por tipo de usuário
- **Auditoria**: Todas as ações são registradas no histórico
- **Validação em Múltiplas Camadas**: Middleware + Controller + Model
- **Prevenção de Escalação**: Técnicos não podem elevar privilégios

## Comandos para Configuração

```bash
# Criar roles
php artisan db:seed --class=RoleSeeder

# Criar usuários de exemplo
php artisan db:seed --class=UserExampleSeeder

# Verificar roles criados
php artisan tinker
>>> App\Models\Role::all()->pluck('name', 'description')
```

## Extensibilidade

O sistema foi projetado para ser facilmente extensível:
- Novos roles podem ser adicionados no `RoleSeeder`
- Novas organizações podem ser criadas
- Middlewares podem ser customizados para regras específicas
- Trait `HasOrganizacaoAccess` pode ser estendido com novos métodos

## Considerações de Performance

- Queries otimizadas com eager loading
- Middleware eficiente com verificações em cascata
- Cache de roles e organizações quando necessário
- Índices apropriados nas tabelas de relacionamento 