<?php

namespace Database\Seeders;

use App\Models\TipoFluxo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoFluxoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipoFluxos = [
            [
                'nome' => 'Obras de Infraestrutura',
                'descricao' => 'Fluxo para obras de infraestrutura urbana',
                'versao' => '1.0',
                'ativo' => true,
            ],
            [
                'nome' => 'Manutenção e Reforma',
                'descricao' => 'Fluxo para manutenção e reforma de equipamentos públicos',
                'versao' => '1.1',
                'ativo' => true,
            ],
            [
                'nome' => 'Projetos Habitacionais',
                'descricao' => 'Fluxo específico para projetos de habitação social',
                'versao' => '2.0',
                'ativo' => true,
            ],
            [
                'nome' => 'Saneamento Básico',
                'descricao' => 'Fluxo para obras de saneamento e tratamento de água',
                'versao' => '1.0',
                'ativo' => true,
            ],
            [
                'nome' => 'Mobilidade Urbana',
                'descricao' => 'Fluxo para projetos de mobilidade e transporte público',
                'versao' => '1.2',
                'ativo' => true,
            ],
            [
                'nome' => 'Equipamentos Públicos',
                'descricao' => 'Fluxo para construção de escolas, postos de saúde, etc.',
                'versao' => '1.0',
                'ativo' => true,
            ],
            [
                'nome' => 'Fluxo Experimental',
                'descricao' => 'Fluxo em fase de testes - desativado',
                'versao' => '0.1',
                'ativo' => false,
            ],
        ];

        foreach ($tipoFluxos as $tipoFluxo) {
            TipoFluxo::create($tipoFluxo);
        }
    }
}
