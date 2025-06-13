<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        
        // Configurar paginação para usar Bootstrap
        Paginator::useBootstrap();
        
        /* If (env('APP_ENV') !== 'local') {
            $this->app['request']->server->set('HTTPS', true);
        } */
    }
}
