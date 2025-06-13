# Menu AdminLTE com Controle de Roles

## Visão Geral

O menu do AdminLTE foi configurado para exibir apenas as opções que cada usuário tem permissão para acessar, baseado em seus roles e organização.

## Estrutura do Menu por Role

### 🔧 **Admin Sistema** (`admin`)
**Acesso Total** - Vê todos os itens do menu:

- **Dashboard**
- **Usuários** - Gestão completa de usuários
- **Gestão Organizacional**
  - Organizações
  - Termos de Adesão
- **Demandas e Ações**
  - Cadastro Demanda GMS
  - Demandas
  - Ações/Obras
- **Configuração de Workflow**
  - Tipos de Fluxo
  - Etapas de Fluxo
  - Status
- **Gestão Documental**
  - Tipos de Documento
  - Templates de Documento
  - Documentos
- **Acompanhamento**
  - Notificações
  - Histórico de Etapas

### 🏢 **Admin Paranacidade** (`admin_paranacidade`)
**Gestão Completa de Obras** - Vê quase tudo exceto gestão de usuários:

- **Dashboard**
- **Gestão Organizacional**
  - Organizações
  - Termos de Adesão
- **Demandas e Ações**
  - Cadastro Demanda GMS
  - Demandas
  - Ações/Obras
- **Configuração de Workflow**
  - Tipos de Fluxo
  - Etapas de Fluxo
  - Status
- **Gestão Documental**
  - Tipos de Documento
  - Templates de Documento
  - Documentos
- **Acompanhamento**
  - Notificações
  - Histórico de Etapas

### 👁️ **Técnico Paranacidade** (`tecnico_paranacidade`)
**Visualização Total** - Vê tudo mas não pode editar:

- **Dashboard**
- **Gestão Organizacional**
  - Termos de Adesão (somente leitura)
- **Demandas e Ações**
  - Cadastro Demanda GMS (somente leitura)
  - Demandas (somente leitura)
  - Ações/Obras (somente leitura)
- **Gestão Documental**
  - Documentos (somente leitura)
- **Acompanhamento**
  - Notificações
  - Histórico de Etapas

### 🏛️ **Admin Secretaria** (`admin_secretaria`)
**Gestão da Sua Secretaria** - Vê apenas dados da sua organização:

- **Dashboard**
- **Gestão Organizacional**
  - Termos de Adesão (apenas da sua organização)
- **Demandas e Ações**
  - Cadastro Demanda GMS (apenas da sua organização)
  - Demandas (apenas da sua organização)
  - Ações/Obras (apenas da sua organização)
- **Gestão Documental**
  - Documentos (apenas da sua organização)
- **Acompanhamento**
  - Notificações
  - Histórico de Etapas
- **Informações do Usuário**
  - Minha Organização

### 👤 **Técnico Secretaria** (`tecnico_secretaria`)
**Visualização da Sua Secretaria** - Vê apenas dados da sua organização (somente leitura):

- **Dashboard**
- **Gestão Organizacional**
  - Termos de Adesão (somente leitura, apenas da sua organização)
- **Demandas e Ações**
  - Cadastro Demanda GMS (somente leitura, apenas da sua organização)
  - Demandas (somente leitura, apenas da sua organização)
  - Ações/Obras (somente leitura, apenas da sua organização)
- **Gestão Documental**
  - Documentos (somente leitura, apenas da sua organização)
- **Acompanhamento**
  - Notificações
  - Histórico de Etapas
- **Informações do Usuário**
  - Minha Organização

## Implementação Técnica

### Filtro de Menu Customizado

O arquivo `app/Menu/Filters/RoleFilter.php` implementa a lógica de filtragem:

```php
// Verifica se o usuário tem o role necessário
if (isset($item['can']) && auth()->check()) {
    $user = auth()->user();
    $allowedRoles = is_array($item['can']) ? $item['can'] : [$item['can']];
    
    foreach ($allowedRoles as $role) {
        if ($user->hasRole($role)) {
            $hasPermission = true;
            break;
        }
    }
    
    if (!$hasPermission) {
        return false; // Remove item do menu
    }
}
```

### Configuração no AdminLTE

No arquivo `config/adminlte.php`, cada item do menu tem a propriedade `can`:

```php
[
    'text' => 'Organizações',
    'url'  => 'organizacoes',
    'icon' => 'fas fa-building',
    'can'  => ['admin', 'admin_paranacidade'], // Apenas estes roles veem
],
```

### Provider de Menu Dinâmico

O `MenuServiceProvider` adiciona informações contextuais:

- Contador de notificações não lidas
- Informações da organização do usuário
- Display dos roles do usuário

## Segurança em Camadas

### 1. **Filtro de Menu**
Remove itens que o usuário não pode ver

### 2. **Middleware de Rotas**
Bloqueia acesso mesmo se o usuário tentar acessar diretamente

### 3. **Controle de Organização**
Filtra dados baseado na organização do usuário

### 4. **Controle de Método HTTP**
Técnicos só podem usar GET/HEAD (visualização)

## Personalização

### Adicionar Novo Item ao Menu

1. **Adicionar no `config/adminlte.php`:**
```php
[
    'text' => 'Novo Item',
    'url'  => 'novo-item',
    'icon' => 'fas fa-star',
    'can'  => ['admin', 'admin_paranacidade'], // Roles permitidos
],
```

2. **Criar rota protegida:**
```php
Route::middleware(['role:admin|admin_paranacidade'])->group(function () {
    Route::get('novo-item', [NovoController::class, 'index']);
});
```

### Adicionar Novo Role

1. **Atualizar `RoleSeeder.php`**
2. **Executar:** `php artisan db:seed --class=RoleSeeder`
3. **Adicionar aos filtros de menu conforme necessário**

## Testando o Sistema

### Usuários de Teste Disponíveis

Todos com senha `123456`:

1. **admin@sistema.gov.br** - Admin Sistema
2. **admin@paranacidade.pr.gov.br** - Admin Paranacidade
3. **tecnico@paranacidade.pr.gov.br** - Técnico Paranacidade
4. **admin@seed.pr.gov.br** - Admin SEED
5. **tecnico@seed.pr.gov.br** - Técnico SEED
6. **admin@sesa.pr.gov.br** - Admin SESA
7. **tecnico@sesa.pr.gov.br** - Técnico SESA
8. **admin@secid.pr.gov.br** - Admin SECID
9. **admin@sesp.pr.gov.br** - Admin SESP

### Como Testar

1. **Faça login com diferentes usuários**
2. **Observe as diferenças no menu lateral**
3. **Tente acessar URLs diretamente** (deve ser bloqueado)
4. **Verifique se técnicos não conseguem editar** (apenas visualizar)

## Benefícios

- ✅ **Interface Limpa**: Usuários veem apenas o que podem usar
- ✅ **Segurança**: Múltiplas camadas de proteção
- ✅ **Experiência do Usuário**: Menu contextual por role
- ✅ **Manutenibilidade**: Fácil de adicionar novos roles/itens
- ✅ **Escalabilidade**: Sistema preparado para crescer 