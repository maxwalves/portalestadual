<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoDocumento;

class TipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposDocumento = [
            [
                'codigo' => 'PROJETO_BASICO',
                'nome' => 'Projeto Básico',
                'descricao' => 'Projeto básico de arquitetura ou engenharia conforme NBR',
                'extensoes_permitidas' => 'pdf,doc,docx,dwg',
                'tamanho_maximo_mb' => 50,
                'requer_assinatura' => true,
                'categoria' => 'PROJETO',
                'is_ativo' => true,
            ],
            [
                'codigo' => 'PROJETO_EXECUTIVO',
                'nome' => 'Projeto Executivo',
                'descricao' => 'Projeto executivo detalhado para execução da obra',
                'extensoes_permitidas' => 'pdf,dwg,doc,docx',
                'tamanho_maximo_mb' => 100,
                'requer_assinatura' => true,
                'categoria' => 'PROJETO',
                'is_ativo' => true,
            ],
            [
                'codigo' => 'ORCAMENTO',
                'nome' => 'Planilha Orçamentária',
                'descricao' => 'Planilha orçamentária detalhada com composições unitárias',
                'extensoes_permitidas' => 'xls,xlsx,pdf',
                'tamanho_maximo_mb' => 20,
                'requer_assinatura' => false,
                'categoria' => 'FINANCEIRO',
                'is_ativo' => true,
            ],
            [
                'codigo' => 'CRONOGRAMA',
                'nome' => 'Cronograma Físico-Financeiro',
                'descricao' => 'Cronograma de execução física e financeira da obra',
                'extensoes_permitidas' => 'xls,xlsx,pdf,mpp',
                'tamanho_maximo_mb' => 20,
                'requer_assinatura' => false,
                'categoria' => 'FINANCEIRO',
                'is_ativo' => true,
            ],
            [
                'codigo' => 'ART_RRT',
                'nome' => 'ART/RRT',
                'descricao' => 'Anotação de Responsabilidade Técnica ou Registro de Responsabilidade Técnica',
                'extensoes_permitidas' => 'pdf',
                'tamanho_maximo_mb' => 10,
                'requer_assinatura' => true,
                'categoria' => 'TECNICO',
                'is_ativo' => true,
            ],
            [
                'codigo' => 'LICENCA_AMBIENTAL',
                'nome' => 'Licença Ambiental',
                'descricao' => 'Licenças ambientais necessárias para execução da obra',
                'extensoes_permitidas' => 'pdf',
                'tamanho_maximo_mb' => 10,
                'requer_assinatura' => true,
                'categoria' => 'LICENCA',
                'is_ativo' => true,
            ],
            [
                'codigo' => 'MEMORIAL_DESCRITIVO',
                'nome' => 'Memorial Descritivo',
                'descricao' => 'Memorial descritivo detalhado do projeto e especificações técnicas',
                'extensoes_permitidas' => 'pdf,doc,docx',
                'tamanho_maximo_mb' => 30,
                'requer_assinatura' => true,
                'categoria' => 'TECNICO',
                'is_ativo' => true,
            ],
            [
                'codigo' => 'ESPECIFICACAO_TECNICA',
                'nome' => 'Especificação Técnica',
                'descricao' => 'Especificações técnicas de materiais e serviços',
                'extensoes_permitidas' => 'pdf,doc,docx',
                'tamanho_maximo_mb' => 25,
                'requer_assinatura' => true,
                'categoria' => 'TECNICO',
                'is_ativo' => true,
            ],
            [
                'codigo' => 'ESTUDO_VIABILIDADE',
                'nome' => 'Estudo de Viabilidade',
                'descricao' => 'Estudo de viabilidade técnica e econômica',
                'extensoes_permitidas' => 'pdf,doc,docx,xls,xlsx',
                'tamanho_maximo_mb' => 40,
                'requer_assinatura' => true,
                'categoria' => 'FINANCEIRO',
                'is_ativo' => true,
            ],
            [
                'codigo' => 'LAUDO_TECNICO',
                'nome' => 'Laudo Técnico',
                'descricao' => 'Laudo técnico de vistoria ou avaliação',
                'extensoes_permitidas' => 'pdf,doc,docx',
                'tamanho_maximo_mb' => 30,
                'requer_assinatura' => true,
                'categoria' => 'TECNICO',
                'is_ativo' => true,
            ],
            [
                'codigo' => 'CONTRATO',
                'nome' => 'Contrato',
                'descricao' => 'Documentos contratuais e termos aditivos',
                'extensoes_permitidas' => 'pdf,doc,docx',
                'tamanho_maximo_mb' => 20,
                'requer_assinatura' => true,
                'categoria' => 'JURIDICO',
                'is_ativo' => true,
            ],
            [
                'codigo' => 'DOCUMENTACAO_LEGAL',
                'nome' => 'Documentação Legal',
                'descricao' => 'Documentos legais diversos (certidões, registros, etc.)',
                'extensoes_permitidas' => 'pdf',
                'tamanho_maximo_mb' => 15,
                'requer_assinatura' => false,
                'categoria' => 'JURIDICO',
                'is_ativo' => true,
            ],
            [
                'codigo' => 'RELATORIO_EXECUCAO',
                'nome' => 'Relatório de Execução',
                'descricao' => 'Relatórios periódicos de acompanhamento da execução',
                'extensoes_permitidas' => 'pdf,doc,docx',
                'tamanho_maximo_mb' => 25,
                'requer_assinatura' => false,
                'categoria' => 'ADMINISTRATIVO',
                'is_ativo' => true,
            ],
            [
                'codigo' => 'FOTO_OBRA',
                'nome' => 'Fotografias da Obra',
                'descricao' => 'Registros fotográficos do andamento da obra',
                'extensoes_permitidas' => 'jpg,jpeg,png,pdf',
                'tamanho_maximo_mb' => 50,
                'requer_assinatura' => false,
                'categoria' => 'ADMINISTRATIVO',
                'is_ativo' => true,
            ],
            [
                'codigo' => 'MEDICAO',
                'nome' => 'Medição de Serviços',
                'descricao' => 'Planilhas de medição de serviços executados',
                'extensoes_permitidas' => 'xls,xlsx,pdf',
                'tamanho_maximo_mb' => 15,
                'requer_assinatura' => true,
                'categoria' => 'FINANCEIRO',
                'is_ativo' => true,
            ],
        ];

        foreach ($tiposDocumento as $tipo) {
            TipoDocumento::create($tipo);
        }
    }
} 