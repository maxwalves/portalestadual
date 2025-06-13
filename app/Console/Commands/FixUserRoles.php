<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class FixUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:user-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrigir roles dos usuários';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Corrigindo roles dos usuários...');
        
        // Encontrar usuário sem roles
        $user = User::where('email', 'tecnico@sesa.pr.gov.br')->first();
        if ($user && $user->roles->isEmpty()) {
            $role = Role::where('name', 'tecnico_secretaria')->first();
            if ($role) {
                $user->roles()->attach($role->id);
                $this->info("Role 'tecnico_secretaria' adicionado ao usuário {$user->email}");
            }
        }
        
        $this->info('Correção concluída!');
    }
}
