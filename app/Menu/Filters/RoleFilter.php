<?php

namespace App\Menu\Filters;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Transforms a menu item. Add, remove or change properties.
     *
     * @param  array  $item  A menu item
     * @return array|false
     */
    public function transform($item)
    {
        // Se não há usuário logado, remover item que requer autenticação
        if (!auth()->check() && isset($item['can'])) {
            return false;
        }

        // Se o item tem restrição de role
        if (isset($item['can']) && auth()->check()) {
            $user = auth()->user();
            $allowedRoles = is_array($item['can']) ? $item['can'] : [$item['can']];
            
            // Verificar se o usuário tem pelo menos um dos roles permitidos
            $hasPermission = false;
            foreach ($allowedRoles as $role) {
                if ($user->hasRole($role)) {
                    $hasPermission = true;
                    break;
                }
            }
            
            // Se não tem permissão, remover item
            if (!$hasPermission) {
                return false;
            }
        }

        // Processar textos dinâmicos
        if (isset($item['text']) && is_callable($item['text'])) {
            $item['text'] = call_user_func($item['text']);
        }

        // Processar submenu recursivamente
        if (isset($item['submenu'])) {
            $filteredSubmenu = [];
            foreach ($item['submenu'] as $subitem) {
                $filteredSubitem = $this->transform($subitem);
                if ($filteredSubitem !== false) {
                    $filteredSubmenu[] = $filteredSubitem;
                }
            }
            
            // Se não há itens no submenu após filtrar, remover o item pai
            if (empty($filteredSubmenu)) {
                return false;
            }
            
            $item['submenu'] = $filteredSubmenu;
        }

        return $item;
    }
} 