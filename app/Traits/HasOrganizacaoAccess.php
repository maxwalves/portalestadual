<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasOrganizacaoAccess
{
    /**
     * Verificar se o usuário pode acessar dados de uma organização específica
     */
    protected function canAccessOrganizacao($organizacaoId): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Admin de sistema sempre pode
        if ($user->hasRole('admin')) {
            return true;
        }

        // Admin e Técnico Paranacidade podem acessar todas
        if ($user->hasRole(['admin_paranacidade', 'tecnico_paranacidade'])) {
            return true;
        }

        // Usuários de secretaria só podem acessar sua própria organização
        if ($user->hasRole(['admin_secretaria', 'tecnico_secretaria'])) {
            return $user->organizacao_id == $organizacaoId;
        }

        return false;
    }

    /**
     * Filtrar query baseado na organização do usuário
     */
    protected function scopeByUserOrganizacao(Builder $query): Builder
    {
        $user = auth()->user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0'); // Nenhum resultado
        }

        // Admin de sistema vê tudo
        if ($user->hasRole('admin')) {
            return $query;
        }

        // Admin e Técnico Paranacidade veem tudo
        if ($user->hasRole(['admin_paranacidade', 'tecnico_paranacidade'])) {
            return $query;
        }

        // Usuários de secretaria veem apenas sua organização
        if ($user->hasRole(['admin_secretaria', 'tecnico_secretaria'])) {
            if ($user->organizacao_id) {
                return $query->where('organizacao_id', $user->organizacao_id);
            }
        }

        return $query->whereRaw('1 = 0'); // Nenhum resultado por padrão
    }

    /**
     * Verificar se usuário pode editar (não apenas visualizar)
     */
    protected function canEdit(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Admin de sistema sempre pode
        if ($user->hasRole('admin')) {
            return true;
        }

        // Admin Paranacidade pode editar
        if ($user->hasRole('admin_paranacidade')) {
            return true;
        }

        // Admin de Secretaria pode editar dados da sua secretaria
        if ($user->hasRole('admin_secretaria')) {
            return true;
        }

        // Técnicos não podem editar
        return false;
    }

    /**
     * Verificar se usuário pode criar novos registros
     */
    protected function canCreate(): bool
    {
        return $this->canEdit();
    }

    /**
     * Verificar se usuário pode deletar registros
     */
    protected function canDelete(): bool
    {
        return $this->canEdit();
    }

    /**
     * Obter organizações que o usuário pode acessar
     */
    protected function getAccessibleOrganizacoes()
    {
        $user = auth()->user();
        
        if (!$user) {
            return collect();
        }

        // Admin de sistema e Paranacidade veem todas
        if ($user->hasRole(['admin', 'admin_paranacidade', 'tecnico_paranacidade'])) {
            return \App\Models\Organizacao::where('is_ativo', true)->get();
        }

        // Usuários de secretaria veem apenas a sua
        if ($user->hasRole(['admin_secretaria', 'tecnico_secretaria']) && $user->organizacao_id) {
            return \App\Models\Organizacao::where('id', $user->organizacao_id)->get();
        }

        return collect();
    }

    /**
     * Verificar se usuário pode gerenciar tipos de fluxo e etapas
     */
    protected function canManageWorkflow(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Apenas Admin de sistema e Admin Paranacidade podem gerenciar workflow
        return $user->hasRole(['admin', 'admin_paranacidade']);
    }

    /**
     * Verificar se usuário pode gerenciar documentos e templates
     */
    protected function canManageDocuments(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Admin de sistema e Admin Paranacidade podem gerenciar documentos
        return $user->hasRole(['admin', 'admin_paranacidade']);
    }

    /**
     * Verificar se usuário pode gerenciar usuários
     */
    protected function canManageUsers(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Admin de sistema pode gerenciar todos os usuários
        if ($user->hasRole('admin')) {
            return true;
        }

        // Admin de secretaria pode gerenciar usuários da sua secretaria
        if ($user->hasRole('admin_secretaria')) {
            return true;
        }

        return false;
    }
} 