<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organizacao;

class OrganizacaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizacoes = [
            [
                'nome' => 'PARANACIDADE',
                'tipo' => 'PARANACIDADE',
                'cnpj' => '06.973.694/0001-83',
                'email' => 'contato@paranacidade.pr.gov.br',
                'telefone' => '(41) 3219-1000',
                'endereco' => 'Rua Deputado Heitor Alencar Furtado, 2655 - Ecoville - Curitiba/PR',
                'responsavel_nome' => 'Diretor Executivo',
                'responsavel_cargo' => 'Diretor Executivo',
                'is_ativo' => true,
            ],
            [
                'nome' => 'SEED',
                'tipo' => 'SEED',
                'cnpj' => '76.416.957/0001-28',
                'email' => 'seed@seed.pr.gov.br',
                'telefone' => '(41) 3340-1500',
                'endereco' => 'Av. Água Verde, 2140 - Água Verde - Curitiba/PR',
                'responsavel_nome' => 'Secretário de Educação',
                'responsavel_cargo' => 'Secretário',
                'is_ativo' => true,
            ],
            [
                'nome' => 'SESA',
                'tipo' => 'SESA',
                'cnpj' => '76.416.919/0001-73',
                'email' => 'sesa@saude.pr.gov.br',
                'telefone' => '(41) 3330-4400',
                'endereco' => 'Rua Piquiri, 170 - Rebouças - Curitiba/PR',
                'responsavel_nome' => 'Secretário de Saúde',
                'responsavel_cargo' => 'Secretário',
                'is_ativo' => true,
            ],
            [
                'nome' => 'SECID',
                'tipo' => 'SECID',
                'cnpj' => '76.416.906/0001-14',
                'email' => 'secid@secid.pr.gov.br',
                'telefone' => '(41) 3210-2000',
                'endereco' => 'Rua dos Funcionários, 1323 - Cabral - Curitiba/PR',
                'responsavel_nome' => 'Secretário de Cidades',
                'responsavel_cargo' => 'Secretário',
                'is_ativo' => true,
            ],
            [
                'nome' => 'SESP',
                'tipo' => 'SESP',
                'cnpj' => '76.416.932/0001-56',
                'email' => 'sesp@sesp.pr.gov.br',
                'telefone' => '(41) 3270-2500',
                'endereco' => 'Av. Iguaçu, 420 - Rebouças - Curitiba/PR',
                'responsavel_nome' => 'Secretário de Segurança',
                'responsavel_cargo' => 'Secretário',
                'is_ativo' => true,
            ],
        ];

        foreach ($organizacoes as $organizacao) {
            Organizacao::create($organizacao);
        }
    }
} 