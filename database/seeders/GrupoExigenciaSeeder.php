<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GrupoExigencia;

class GrupoExigenciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grupos = [
            [
                'nome' => 'Documentação Básica',
                'descricao' => 'Documentos fundamentais exigidos para todas as obras, incluindo registros profissionais, ARTs e RRTs.',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Licenciamento Ambiental',
                'descricao' => 'Documentação relacionada ao licenciamento ambiental para obras com impacto ambiental.',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Projetos Técnicos',
                'descricao' => 'Projetos técnicos detalhados incluindo projetos básicos, executivos e complementares.',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Orçamento e Cronograma',
                'descricao' => 'Planilhas orçamentárias detalhadas e cronogramas físico-financeiros.',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Documentação Jurídica',
                'descricao' => 'Documentos jurídicos necessários para a execução da obra.',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Licenças e Alvarás',
                'descricao' => 'Licenças municipais, alvarás de construção e demais autorizações necessárias.',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Estudos Geotécnicos',
                'descricao' => 'Sondagens, estudos de solo e demais análises geotécnicas.',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Documentação de Segurança',
                'descricao' => 'PCMAT, laudos de segurança e documentação relacionada à segurança do trabalho.',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Aprovações Concessionárias',
                'descricao' => 'Aprovações das concessionárias de energia, água, esgoto e telecomunicações.',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Documentação Histórica (INATIVO)',
                'descricao' => 'Grupo exemplo de documentação que não é mais utilizada.',
                'is_ativo' => false,
            ],
        ];

        foreach ($grupos as $grupo) {
            GrupoExigencia::create($grupo);
        }

        $this->command->info('Grupos de exigência criados com sucesso!');
    }
}
