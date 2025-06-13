<?php
namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Roles básicos do sistema (manter compatibilidade)
        Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrador do Sistema - Acesso total']
        );
        
        Role::firstOrCreate(
            ['name' => 'manager'],
            ['description' => 'Gerente - Acesso gerencial']
        );
        
        Role::firstOrCreate(
            ['name' => 'external'],
            ['description' => 'Usuário Externo']
        );
        
        Role::firstOrCreate(
            ['name' => 'user'],
            ['description' => 'Usuário Padrão']
        );

        // ===== ROLES ESPECÍFICOS PARA SISTEMA DE OBRAS =====
        
        // 1. Técnico de Secretaria
        Role::firstOrCreate(
            ['name' => 'tecnico_secretaria'],
            ['description' => 'Técnico de Secretaria - Visualiza ações da sua secretaria e acompanha fluxo']
        );

        // 2. Admin de Secretaria  
        Role::firstOrCreate(
            ['name' => 'admin_secretaria'],
            ['description' => 'Admin de Secretaria - Gestão completa da sua secretaria (usuários, termos, demandas, ações)']
        );

        // 3. Técnico Paranacidade
        Role::firstOrCreate(
            ['name' => 'tecnico_paranacidade'],
            ['description' => 'Técnico Paranacidade - Visualiza todas as ações de todas as secretarias e acompanha fluxo']
        );

        // 4. Admin Paranacidade
        Role::firstOrCreate(
            ['name' => 'admin_paranacidade'],
            ['description' => 'Admin Paranacidade - Gestão completa: termos, demandas, ações de todas secretarias, tipos de fluxo, etapas, gestão documental']
        );

        // 5. Admin Sistema (já existe como 'admin', mas vamos manter referência)
        // O role 'admin' já cobre este perfil com acesso total
    }
}
