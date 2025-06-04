<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Organizacao;
use Illuminate\Console\Command;

class AssignUsersToParanacidade extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:assign-paranacidade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atribui todos os usuários sem organização para a organização Paranacidade';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Buscar a organização Paranacidade
        $paranacidade = Organizacao::where('nome', 'LIKE', '%PARANACIDADE%')->first();
        
        if (!$paranacidade) {
            $this->error('Organização Paranacidade não encontrada no banco de dados!');
            return 1;
        }

        $this->info("Organização encontrada: {$paranacidade->nome} (ID: {$paranacidade->id})");

        // Buscar usuários sem organização
        $usersWithoutOrg = User::whereNull('organizacao_id')->get();
        
        $this->info("Encontrados {$usersWithoutOrg->count()} usuários sem organização.");

        if ($usersWithoutOrg->count() === 0) {
            $this->info('Nenhum usuário sem organização encontrado.');
            return 0;
        }

        // Confirmar a ação
        if (!$this->confirm("Deseja atribuir todos estes usuários à organização {$paranacidade->nome}?")) {
            $this->info('Operação cancelada.');
            return 0;
        }

        // Atribuir os usuários
        $updated = 0;
        foreach ($usersWithoutOrg as $user) {
            $user->organizacao_id = $paranacidade->id;
            $user->save();
            $updated++;
            $this->line("✓ {$user->name} ({$user->email})");
        }

        $this->info("Concluído! {$updated} usuários foram atribuídos à organização {$paranacidade->nome}.");
        
        return 0;
    }
}
