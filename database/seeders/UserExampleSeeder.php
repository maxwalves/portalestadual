<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Organizacao;
use Illuminate\Support\Facades\Hash;

class UserExampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar organizações
        $paranacidade = Organizacao::where('tipo', 'PARANACIDADE')->first();
        $seed = Organizacao::where('tipo', 'SEED')->first();
        $sesa = Organizacao::where('tipo', 'SESA')->first();
        $secid = Organizacao::where('tipo', 'SECID')->first();
        $sesp = Organizacao::where('tipo', 'SESP')->first();

        // Buscar roles
        $adminRole = Role::where('name', 'admin')->first();
        $adminParanacidadeRole = Role::where('name', 'admin_paranacidade')->first();
        $tecnicoParanacidadeRole = Role::where('name', 'tecnico_paranacidade')->first();
        $adminSecretariaRole = Role::where('name', 'admin_secretaria')->first();
        $tecnicoSecretariaRole = Role::where('name', 'tecnico_secretaria')->first();

        // ===== USUÁRIOS DE EXEMPLO =====

        // 1. Admin do Sistema
        $adminSistema = User::firstOrCreate(
            ['email' => 'admin@sistema.gov.br'],
            [
                'name' => 'Administrador do Sistema',
                'password' => Hash::make('123456'),
                'organizacao_id' => $paranacidade->id,
                'isExterno' => false,
                'active' => true,
            ]
        );
        $adminSistema->roles()->sync([$adminRole->id]);

        // 2. Admin Paranacidade
        $adminParanacidade = User::firstOrCreate(
            ['email' => 'admin@paranacidade.pr.gov.br'],
            [
                'name' => 'João Silva - Admin Paranacidade',
                'password' => Hash::make('123456'),
                'organizacao_id' => $paranacidade->id,
                'isExterno' => false,
                'active' => true,
            ]
        );
        $adminParanacidade->roles()->sync([$adminParanacidadeRole->id]);

        // 3. Técnico Paranacidade
        $tecnicoParanacidade = User::firstOrCreate(
            ['email' => 'tecnico@paranacidade.pr.gov.br'],
            [
                'name' => 'Maria Santos - Técnico Paranacidade',
                'password' => Hash::make('123456'),
                'organizacao_id' => $paranacidade->id,
                'isExterno' => false,
                'active' => true,
            ]
        );
        $tecnicoParanacidade->roles()->sync([$tecnicoParanacidadeRole->id]);

        // 4. Admin SEED
        $adminSeed = User::firstOrCreate(
            ['email' => 'admin@seed.pr.gov.br'],
            [
                'name' => 'Carlos Oliveira - Admin SEED',
                'password' => Hash::make('123456'),
                'organizacao_id' => $seed->id,
                'isExterno' => false,
                'active' => true,
            ]
        );
        $adminSeed->roles()->sync([$adminSecretariaRole->id]);

        // 5. Técnico SEED
        $tecnicoSeed = User::firstOrCreate(
            ['email' => 'tecnico@seed.pr.gov.br'],
            [
                'name' => 'Ana Costa - Técnico SEED',
                'password' => Hash::make('123456'),
                'organizacao_id' => $seed->id,
                'isExterno' => false,
                'active' => true,
            ]
        );
        $tecnicoSeed->roles()->sync([$tecnicoSecretariaRole->id]);

        // 6. Admin SESA
        $adminSesa = User::firstOrCreate(
            ['email' => 'admin@sesa.pr.gov.br'],
            [
                'name' => 'Pedro Almeida - Admin SESA',
                'password' => Hash::make('123456'),
                'organizacao_id' => $sesa->id,
                'isExterno' => false,
                'active' => true,
            ]
        );
        $adminSesa->roles()->sync([$adminSecretariaRole->id]);

        // 7. Técnico SESA
        $tecnicoSesa = User::firstOrCreate(
            ['email' => 'tecnico@sesa.pr.gov.br'],
            [
                'name' => 'Lucia Ferreira - Técnico SESA',
                'password' => Hash::make('123456'),
                'organizacao_id' => $sesa->id,
                'isExterno' => false,
                'active' => true,
            ]
        );
        $tecnicoSesa->roles()->sync([$tecnicoSecretariaRole->id]);

        // 8. Admin SECID
        $adminSecid = User::firstOrCreate(
            ['email' => 'admin@secid.pr.gov.br'],
            [
                'name' => 'Roberto Lima - Admin SECID',
                'password' => Hash::make('123456'),
                'organizacao_id' => $secid->id,
                'isExterno' => false,
                'active' => true,
            ]
        );
        $adminSecid->roles()->sync([$adminSecretariaRole->id]);

        // 9. Admin SESP
        $adminSesp = User::firstOrCreate(
            ['email' => 'admin@sesp.pr.gov.br'],
            [
                'name' => 'Marcos Souza - Admin SESP',
                'password' => Hash::make('123456'),
                'organizacao_id' => $sesp->id,
                'isExterno' => false,
                'active' => true,
            ]
        );
        $adminSesp->roles()->sync([$adminSecretariaRole->id]);

        $this->command->info('Usuários de exemplo criados com sucesso!');
        $this->command->info('');
        $this->command->info('=== CREDENCIAIS DE ACESSO ===');
        $this->command->info('Todos os usuários têm senha: 123456');
        $this->command->info('');
        $this->command->info('1. Admin Sistema: admin@sistema.gov.br');
        $this->command->info('2. Admin Paranacidade: admin@paranacidade.pr.gov.br');
        $this->command->info('3. Técnico Paranacidade: tecnico@paranacidade.pr.gov.br');
        $this->command->info('4. Admin SEED: admin@seed.pr.gov.br');
        $this->command->info('5. Técnico SEED: tecnico@seed.pr.gov.br');
        $this->command->info('6. Admin SESA: admin@sesa.pr.gov.br');
        $this->command->info('7. Técnico SESA: tecnico@sesa.pr.gov.br');
        $this->command->info('8. Admin SECID: admin@secid.pr.gov.br');
        $this->command->info('9. Admin SESP: admin@sesp.pr.gov.br');
    }
}
