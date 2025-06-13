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
                'descricao' => 'Documentos obrigatórios para início do processo',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Licenciamento Ambiental',
                'descricao' => 'Exigências para obras que impactam o meio ambiente',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Projetos Técnicos',
                'descricao' => 'Projetos, memoriais e ARTs necessários',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Orçamento e Cronograma',
                'descricao' => 'Planilhas orçamentárias e cronogramas físico-financeiros',
                'is_ativo' => true,
            ],
        ];
        foreach ($grupos as $grupo) {
            GrupoExigencia::firstOrCreate([
                'nome' => $grupo['nome'],
            ], $grupo);
        }
    }
}
