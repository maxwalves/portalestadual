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
        $tiposFluxo = [
            [
                'nome' => 'Construção de Escola',
                'descricao' => 'Fluxo para construção de unidades escolares',
                'categoria' => 'ESCOLA',
                'versao' => '1.0',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Reforma de Unidade de Saúde',
                'descricao' => 'Fluxo para reforma e ampliação de postos de saúde',
                'categoria' => 'SAUDE',
                'versao' => '1.0',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Infraestrutura Urbana',
                'descricao' => 'Fluxo para obras de infraestrutura urbana (pavimentação, drenagem, etc.)',
                'categoria' => 'INFRAESTRUTURA',
                'versao' => '1.0',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Centro Esportivo',
                'descricao' => 'Fluxo para construção de centros esportivos e quadras',
                'categoria' => 'ESPORTE',
                'versao' => '1.0',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Segurança Pública',
                'descricao' => 'Fluxo para obras relacionadas à segurança pública',
                'categoria' => 'SEGURANCA',
                'versao' => '1.0',
                'is_ativo' => true,
            ],
        ];

        foreach ($tiposFluxo as $tipo) {
            TipoFluxo::firstOrCreate(
                ['nome' => $tipo['nome'], 'versao' => $tipo['versao']],
                $tipo
            );
        }
    }
}
