<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testar se os roles estão funcionando';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testando roles...');
        
        $users = User::with('roles', 'organizacao')->get();
        
        if ($users->isEmpty()) {
            $this->error('Nenhum usuário encontrado');
            return;
        }
        
        foreach ($users as $user) {
            $this->info("=== Usuário: {$user->name} ({$user->email}) ===");
            
            $roles = $user->roles->pluck('name')->toArray();
            $this->info("Roles: " . (empty($roles) ? 'Nenhum' : implode(', ', $roles)));
            
            $organizacao = $user->organizacao ? $user->organizacao->nome : 'Sem organização';
            $this->info("Organização: {$organizacao}");
            
            // Testar hasRole com admin
            $hasAdmin = $user->hasRole('admin');
            $this->info("hasRole('admin'): " . ($hasAdmin ? 'true' : 'false'));
            
            $this->line('');
        }
    }
}
