<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\EtapaFluxo;
use App\Models\Status;

class EtapaStatusOpcaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar status disponíveis
        $statusPendente = Status::where('codigo', 'PENDENTE')->first();
        $statusEmAnalise = Status::where('codigo', 'EM_ANALISE')->first();
        $statusAprovado = Status::where('codigo', 'APROVADO')->first();
        $statusReprovado = Status::where('codigo', 'REPROVADO')->first();
        $statusDevolvido = Status::where('codigo', 'DEVOLVIDO')->first();

        // Buscar todas as etapas de fluxo
        $etapasFluxo = EtapaFluxo::all();

        foreach ($etapasFluxo as $etapa) {
            // Configurações padrão para todas as etapas
            $opcoes = [
                [
                    'etapa_fluxo_id' => $etapa->id,
                    'status_id' => $statusPendente->id,
                    'ordem' => 1,
                    'is_padrao' => true,
                    'mostra_para_responsavel' => false, // Status inicial, não mostra como opção
                    'requer_justificativa' => false,
                ],
                [
                    'etapa_fluxo_id' => $etapa->id,
                    'status_id' => $statusEmAnalise->id,
                    'ordem' => 2,
                    'is_padrao' => false,
                    'mostra_para_responsavel' => false, // Status automático
                    'requer_justificativa' => false,
                ],
                [
                    'etapa_fluxo_id' => $etapa->id,
                    'status_id' => $statusAprovado->id,
                    'ordem' => 3,
                    'is_padrao' => false,
                    'mostra_para_responsavel' => true, // Demandante pode aprovar
                    'requer_justificativa' => false,
                ],
                [
                    'etapa_fluxo_id' => $etapa->id,
                    'status_id' => $statusReprovado->id,
                    'ordem' => 4,
                    'is_padrao' => false,
                    'mostra_para_responsavel' => true, // Demandante pode reprovar
                    'requer_justificativa' => true, // Reprovação requer justificativa
                ],
                [
                    'etapa_fluxo_id' => $etapa->id,
                    'status_id' => $statusDevolvido->id,
                    'ordem' => 5,
                    'is_padrao' => false,
                    'mostra_para_responsavel' => true, // Demandante pode devolver para correção
                    'requer_justificativa' => true, // Devolução requer justificativa
                ],
            ];

            foreach ($opcoes as $opcao) {
                DB::table('etapa_status_opcoes')->updateOrInsert(
                    [
                        'etapa_fluxo_id' => $opcao['etapa_fluxo_id'],
                        'status_id' => $opcao['status_id']
                    ],
                    $opcao
                );
            }
        }

        $this->command->info('Opções de status das etapas criadas com sucesso!');
    }
} 