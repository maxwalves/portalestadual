<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gates para roles originais
        Gate::define('admin', function ($user) {
            return $user->hasRole('admin');
        });

        Gate::define('manager', function ($user) {
            return $user->hasRole('manager');
        });

        Gate::define('user', function ($user) {
            return $user->hasRole('user');
        });

        // Gates para novos roles
        Gate::define('admin_paranacidade', function ($user) {
            return $user->hasRole('admin_paranacidade');
        });

        Gate::define('tecnico_paranacidade', function ($user) {
            return $user->hasRole('tecnico_paranacidade');
        });

        Gate::define('admin_secretaria', function ($user) {
            return $user->hasRole('admin_secretaria');
        });

        Gate::define('tecnico_secretaria', function ($user) {
            return $user->hasRole('tecnico_secretaria');
        });

        // Gates combinados para facilitar uso no menu
        Gate::define('admin_system', function ($user) {
            return $user->hasRole('admin');
        });

        Gate::define('admin_paranacidade_or_system', function ($user) {
            return $user->hasRole(['admin', 'admin_paranacidade']);
        });

        Gate::define('any_admin', function ($user) {
            return $user->hasRole(['admin', 'admin_paranacidade', 'admin_secretaria']);
        });

        Gate::define('any_user', function ($user) {
            return $user->hasRole(['admin', 'admin_paranacidade', 'admin_secretaria', 'tecnico_paranacidade', 'tecnico_secretaria']);
        });

        Gate::define('secretaria_users', function ($user) {
            return $user->hasRole(['admin_secretaria', 'tecnico_secretaria']);
        });
    }
}
