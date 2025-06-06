<?php

use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users/roles', [UserRoleController::class, 'index'])->name('admin.users.roles');
    Route::put('/admin/users/{user}/roles', [UserRoleController::class, 'update'])->name('admin.users.roles.update');
    Route::post('/admin/users/roles/mass-update', [UserRoleController::class, 'massUpdate'])->name('admin.users.roles.mass-update');
    Route::put('/admin/users/{user}/toggle-active', [UserRoleController::class, 'toggleActive'])->name('admin.users.toggle-active');
    Route::post('/admin/users/sync-ldap', [UserRoleController::class, 'syncLdapUsers'])->name('admin.users.sync-ldap');
    Route::post('/admin/users/sync-cidades', [UserRoleController::class, 'syncCidades'])->name('admin.users.sync-cidades');
    Route::post('/admin/users/store', [UserRoleController::class, 'store'])->name('admin.users.store');
    Route::delete('/admin/users/{user}', [UserRoleController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/api/check-email', [UserController::class, 'checkEmail'])->name('api.check-email');

    // Rotas de Impersonate
    Route::post('/admin/impersonate/{user}', [ImpersonateController::class, 'impersonate'])->name('admin.impersonate');
});

// Rota para o Dashboard de Obras
Route::get('/dashboard/obras', function () {
    return view('planejamento.demo_sistema_obras');
})->middleware(['auth', 'verified'])->name('dashboard.obras');

// Rota para depuração (apenas em ambiente de desenvolvimento)
if (app()->environment('local')) {
    Route::get('/debug/auth', function () {
        $output = [
            'auth_guards'      => config('auth.guards'),
            'auth_providers'   => config('auth.providers'),
            'ldap_config'      => config('ldap.connections'),
            'ldap_auth_config' => config('ldap_auth'),
        ];

        return response()->json($output);
    });
}

Route::middleware(['auth'])->group(function () {
    Route::resource('organizacoes', \App\Http\Controllers\OrganizacaoController::class)->parameters([
        'organizacoes' => 'organizacao'
    ]);
    Route::resource('termos-adesao', \App\Http\Controllers\TermoAdesaoController::class)->parameters([
        'termos-adesao' => 'termo'
    ]);
    Route::resource('cadastros-demanda-gms', \App\Http\Controllers\CadastroDemandaGmsController::class);
    Route::post('cadastros-demanda-gms/sync', [\App\Http\Controllers\CadastroDemandaGmsController::class, 'sync'])->name('cadastros-demanda-gms.sync');
    Route::resource('demandas', \App\Http\Controllers\DemandaController::class);
    Route::resource('acoes', \App\Http\Controllers\AcaoController::class)->parameters([
        'acoes' => 'acao'
    ]);
    
    // API para buscar cidades do Paraná
    Route::get('api/cidades-parana', [\App\Http\Controllers\AcaoController::class, 'getCidadesParana'])->name('api.cidades-parana');
});

require __DIR__ . '/auth.php';
