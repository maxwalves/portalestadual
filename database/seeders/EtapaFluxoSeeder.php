<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EtapaFluxo;
use App\Models\TipoFluxo;
use App\Models\Modulo;
use App\Models\GrupoExigencia;
use App\Models\Organizacao;

class EtapaFluxoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar dados necessários
        $tipoFluxoEscola = TipoFluxo::where('nome', 'Construção de Escola')->first();
        $tipoFluxoSaude = TipoFluxo::where('nome', 'Unidade Básica de Saúde')->first();
        
        $moduloEnvio = Modulo::where('nome', 'Envio de Documentos')->first();
        $moduloAnalise = Modulo::where('nome', 'Análise Técnica')->first();
        $moduloAssinatura = Modulo::where('nome', 'Assinatura Digital')->first();
        
        $grupoBasico = GrupoExigencia::where('nome', 'Documentação Básica')->first();
        $grupoTecnico = GrupoExigencia::where('nome', 'Documentação Técnica')->first();
        
        $paranacidade = Organizacao::where('nome', 'PARANACIDADE')->first();
        $secid = Organizacao::where('nome', 'SECID')->first();
        $seed = Organizacao::where('nome', 'SEED')->first();
        $sesa = Organizacao::where('nome', 'SESA')->first();

        if (!$tipoFluxoEscola || !$moduloEnvio || !$paranacidade) {
            return; // Dependências não encontradas
        }

        $etapas = [
            // Fluxo para Escola
            [
                'tipo_fluxo_id' => $tipoFluxoEscola->id,
                'modulo_id' => $moduloEnvio->id,
                'grupo_exigencia_id' => $grupoBasico ? $grupoBasico->id : null,
                'organizacao_solicitante_id' => $seed ? $seed->id : $paranacidade->id,
                'organizacao_executora_id' => $paranacidade->id,
                'ordem_execucao' => 1,
                'nome_etapa' => 'Envio da Documentação Inicial',
                'descricao_customizada' => 'Município envia documentação básica para análise inicial',
                'prazo_dias' => 10,
                'tipo_prazo' => 'UTEIS',
                'is_obrigatoria' => true,
                'permite_pular' => false,
                'permite_retorno' => true,
                'tipo_etapa' => 'SEQUENCIAL',
            ],
            [
                'tipo_fluxo_id' => $tipoFluxoEscola->id,
                'modulo_id' => $moduloAnalise ? $moduloAnalise->id : $moduloEnvio->id,
                'grupo_exigencia_id' => $grupoBasico ? $grupoBasico->id : null,
                'organizacao_solicitante_id' => $paranacidade->id,
                'organizacao_executora_id' => $paranacidade->id,
                'ordem_execucao' => 2,
                'nome_etapa' => 'Análise Documental Inicial',
                'descricao_customizada' => 'PARANACIDADE analisa documentação básica enviada',
                'prazo_dias' => 15,
                'tipo_prazo' => 'UTEIS',
                'is_obrigatoria' => true,
                'permite_pular' => false,
                'permite_retorno' => true,
                'tipo_etapa' => 'SEQUENCIAL',
            ],
            [
                'tipo_fluxo_id' => $tipoFluxoEscola->id,
                'modulo_id' => $moduloEnvio->id,
                'grupo_exigencia_id' => $grupoTecnico ? $grupoTecnico->id : null,
                'organizacao_solicitante_id' => $seed ? $seed->id : $paranacidade->id,
                'organizacao_executora_id' => $paranacidade->id,
                'ordem_execucao' => 3,
                'nome_etapa' => 'Envio do Projeto Executivo',
                'descricao_customizada' => 'Envio do projeto executivo e documentação técnica completa',
                'prazo_dias' => 30,
                'tipo_prazo' => 'UTEIS',
                'is_obrigatoria' => true,
                'permite_pular' => false,
                'permite_retorno' => true,
                'tipo_etapa' => 'SEQUENCIAL',
            ],
            [
                'tipo_fluxo_id' => $tipoFluxoEscola->id,
                'modulo_id' => $moduloAssinatura ? $moduloAssinatura->id : $moduloEnvio->id,
                'grupo_exigencia_id' => null,
                'organizacao_solicitante_id' => $paranacidade->id,
                'organizacao_executora_id' => $seed ? $seed->id : $paranacidade->id,
                'ordem_execucao' => 4,
                'nome_etapa' => 'Aprovação Final SEED',
                'descricao_customizada' => 'Aprovação final pela Secretaria de Educação',
                'prazo_dias' => 10,
                'tipo_prazo' => 'UTEIS',
                'is_obrigatoria' => true,
                'permite_pular' => false,
                'permite_retorno' => true,
                'tipo_etapa' => 'SEQUENCIAL',
            ],
        ];

        // Fluxo para Saúde (se existir)
        if ($tipoFluxoSaude && $sesa) {
            $etapas = array_merge($etapas, [
                [
                    'tipo_fluxo_id' => $tipoFluxoSaude->id,
                    'modulo_id' => $moduloEnvio->id,
                                         'grupo_exigencia_id' => $grupoBasico ? $grupoBasico->id : null,
                    'organizacao_solicitante_id' => $sesa->id,
                    'organizacao_executora_id' => $paranacidade->id,
                    'ordem_execucao' => 1,
                    'nome_etapa' => 'Solicitação Inicial UBS',
                    'descricao_customizada' => 'SESA solicita construção de nova UBS',
                    'prazo_dias' => 5,
                    'tipo_prazo' => 'UTEIS',
                    'is_obrigatoria' => true,
                    'permite_pular' => false,
                    'permite_retorno' => true,
                    'tipo_etapa' => 'SEQUENCIAL',
                ],
                [
                    'tipo_fluxo_id' => $tipoFluxoSaude->id,
                                         'modulo_id' => $moduloAnalise ? $moduloAnalise->id : $moduloEnvio->id,
                     'grupo_exigencia_id' => $grupoTecnico ? $grupoTecnico->id : null,
                    'organizacao_solicitante_id' => $paranacidade->id,
                    'organizacao_executora_id' => $paranacidade->id,
                    'ordem_execucao' => 2,
                    'nome_etapa' => 'Análise de Viabilidade',
                    'descricao_customizada' => 'Análise técnica e financeira da viabilidade',
                    'prazo_dias' => 20,
                    'tipo_prazo' => 'UTEIS',
                    'is_obrigatoria' => true,
                    'permite_pular' => false,
                    'permite_retorno' => true,
                    'tipo_etapa' => 'SEQUENCIAL',
                ],
            ]);
        }

        foreach ($etapas as $etapa) {
            EtapaFluxo::create($etapa);
        }
    }
} 