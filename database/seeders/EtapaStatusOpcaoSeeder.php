<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EtapaFluxo;
use App\Models\Status;
use App\Models\EtapaStatusOpcao;

class EtapaStatusOpcaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar status existentes
        $statusPendente = Status::where('codigo', 'PENDENTE')->first();
        $statusAnalise = Status::where('codigo', 'EM_ANALISE')->first();
        $statusAprovado = Status::where('codigo', 'APROVADO')->first();
        $statusReprovado = Status::where('codigo', 'REPROVADO')->first();
        $statusDevolvido = Status::where('codigo', 'DEVOLVIDO')->first();
        $statusCancelado = Status::where('codigo', 'CANCELADO')->first();

        if (!$statusPendente || !$statusAnalise || !$statusAprovado || !$statusReprovado || !$statusDevolvido || !$statusCancelado) {
            $this->command->warn('Status básicos não encontrados. Execute StatusSeeder primeiro.');
            return;
        }

        // Buscar todas as etapas de fluxo
        $etapas = EtapaFluxo::all();

        foreach ($etapas as $etapa) {
            // Configurações padrão para todas as etapas
            $configuracoes = [
                // Status padrão - sempre presente
                [
                    'status_id' => $statusPendente->id,
                    'ordem' => 1,
                    'is_padrao' => true,
                    'mostra_para_responsavel' => false, // Status inicial, não precisa mostrar
                    'requer_justificativa' => false
                ],
                
                // Em análise - para etapas de análise/revisão
                [
                    'status_id' => $statusAnalise->id,
                    'ordem' => 2,
                    'is_padrao' => false,
                    'mostra_para_responsavel' => true,
                    'requer_justificativa' => false
                ],
                
                // Aprovado - sempre disponível para conclusão
                [
                    'status_id' => $statusAprovado->id,
                    'ordem' => 3,
                    'is_padrao' => false,
                    'mostra_para_responsavel' => true,
                    'requer_justificativa' => false
                ],
                
                // Reprovado - para quando há problemas
                [
                    'status_id' => $statusReprovado->id,
                    'ordem' => 4,
                    'is_padrao' => false,
                    'mostra_para_responsavel' => true,
                    'requer_justificativa' => true
                ],
                
                // Devolvido para correção
                [
                    'status_id' => $statusDevolvido->id,
                    'ordem' => 5,
                    'is_padrao' => false,
                    'mostra_para_responsavel' => true,
                    'requer_justificativa' => true
                ],
                
                // Cancelado - em casos extremos
                [
                    'status_id' => $statusCancelado->id,
                    'ordem' => 6,
                    'is_padrao' => false,
                    'mostra_para_responsavel' => false, // Apenas admins podem cancelar
                    'requer_justificativa' => true
                ]
            ];

            // Inserir configurações para esta etapa
            foreach ($configuracoes as $config) {
                \DB::table('etapa_status_opcoes')->updateOrInsert(
                    [
                        'etapa_fluxo_id' => $etapa->id,
                        'status_id' => $config['status_id']
                    ],
                    [
                        'etapa_fluxo_id' => $etapa->id,
                        'status_id' => $config['status_id'],
                        'ordem' => $config['ordem'],
                        'is_padrao' => $config['is_padrao'],
                        'mostra_para_responsavel' => $config['mostra_para_responsavel'],
                        'requer_justificativa' => $config['requer_justificativa']
                    ]
                );
            }
        }

        $this->command->info('Configurações de status das etapas criadas com sucesso!');
        $this->command->info('Total de etapas configuradas: ' . $etapas->count());
    }
} 