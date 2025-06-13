<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app['events']->listen(BuildingMenu::class, function (BuildingMenu $event) {
            $this->addUserInfoToMenu($event);
        });
    }

    /**
     * Adicionar informações do usuário ao menu
     */
    private function addUserInfoToMenu(BuildingMenu $event): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();
        
        // Adicionar informações contextuais para usuários de secretaria
        if ($user->hasRole('admin_secretaria') || $user->hasRole('tecnico_secretaria')) {
            $event->menu->addAfter('header-info', [
                'key' => 'user-org-info',
                'text' => 'Informações do Usuário',
                'icon' => 'fas fa-info-circle',
                'submenu' => [
                    [
                        'text' => $user->organizacao ? 
                            'Organização: ' . $user->organizacao->nome : 
                            'Sem organização',
                        'icon' => 'fas fa-building',
                        'url' => '#',
                    ],
                    [
                        'text' => $this->getUserRoleDisplay($user),
                        'icon' => 'fas fa-user-tag',
                        'url' => '#',
                    ],
                ],
            ]);
        }

        // Adicionar contador de notificações para todos os usuários
        $event->menu->addAfter('dashboard', [
            'key' => 'notifications-counter',
            'text' => 'Notificações',
            'icon' => 'fas fa-bell',
            'url' => 'notificacoes',
            'label' => $this->getUnreadNotificationsCount($user),
            'label_color' => 'danger',
        ]);
    }

    /**
     * Obter display dos roles do usuário
     */
    private function getUserRoleDisplay($user): string
    {
        $roles = $user->roles->pluck('name')->toArray();
        $roleNames = [
            'admin' => 'Admin Sistema',
            'admin_paranacidade' => 'Admin Paranacidade',
            'tecnico_paranacidade' => 'Técnico Paranacidade',
            'admin_secretaria' => 'Admin Secretaria',
            'tecnico_secretaria' => 'Técnico Secretaria',
        ];
        
        $displayRoles = array_map(function($role) use ($roleNames) {
            return $roleNames[$role] ?? $role;
        }, $roles);
        
        return 'Perfil: ' . implode(', ', $displayRoles);
    }

    /**
     * Obter contagem de notificações não lidas
     */
    private function getUnreadNotificationsCount($user): int
    {
        // Implementar lógica para contar notificações não lidas
        // Por enquanto retorna 0
        return 0;
    }
}
