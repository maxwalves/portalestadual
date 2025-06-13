<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Modulo;

class ModuloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modulos = [
            [
                'nome' => 'Assinatura Conjunta',
                'tipo' => 'ASSINATURA',
                'descricao' => 'Módulo para múltiplas assinaturas em cadeia ou paralelo',
                'icone' => 'fa-people-arrows',
                'cor' => '#6610f2',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Validação de Orçamento',
                'tipo' => 'ANALISE',
                'descricao' => 'Rotina de checagem de planilhas orçamentárias',
                'icone' => 'fa-file-invoice-dollar',
                'cor' => '#20c997',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Validação Ambiental',
                'tipo' => 'ANALISE',
                'descricao' => 'Validação automática de documentos ambientais',
                'icone' => 'fa-leaf',
                'cor' => '#198754',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Emissão de Relatórios',
                'tipo' => 'ENVIO',
                'descricao' => 'Geração de relatórios consolidados da obra',
                'icone' => 'fa-file-pdf',
                'cor' => '#0dcaf0',
                'is_ativo' => true,
            ],
        ];
        foreach ($modulos as $modulo) {
            Modulo::firstOrCreate([
                'nome' => $modulo['nome'],
            ], $modulo);
        }
    }
}
