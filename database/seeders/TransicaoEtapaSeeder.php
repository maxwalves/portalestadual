<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransicaoEtapa;
use App\Models\EtapaFluxo;
use App\Models\Status;

class TransicaoEtapaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar status
        $statusAprovado = Status::where('codigo', 'APROVADO')->first();
        $statusReprovado = Status::where('codigo', 'REPROVADO')->first();
        $statusDevolvido = Status::where('codigo', 'DEVOLVIDO')->first();

        // Buscar etapas de fluxo (assumindo que existem algumas)
        $etapas = EtapaFluxo::orderBy('ordem_execucao')->get();

        if ($etapas->count() < 2) {
            $this->command->info('Não há etapas suficientes para criar transições de exemplo.');
            return;
        }

        // Criar transições de exemplo entre etapas sequenciais
        for ($i = 0; $i < $etapas->count() - 1; $i++) {
            $etapaAtual = $etapas[$i];
            $proximaEtapa = $etapas[$i + 1];

            // Transição quando aprovado -> próxima etapa
            TransicaoEtapa::updateOrCreate(
                [
                    'etapa_fluxo_origem_id' => $etapaAtual->id,
                    'etapa_fluxo_destino_id' => $proximaEtapa->id,
                    'status_condicao_id' => $statusAprovado->id
                ],
                [
                    'condicao_tipo' => 'STATUS',
                    'prioridade' => 10,
                    'descricao' => 'Transição automática quando etapa é aprovada',
                    'is_ativo' => true
                ]
            );

            // Transição quando reprovado -> volta para etapa anterior (se não for a primeira)
            if ($i > 0) {
                $etapaAnterior = $etapas[$i - 1];
                
                TransicaoEtapa::updateOrCreate(
                    [
                        'etapa_fluxo_origem_id' => $etapaAtual->id,
                        'etapa_fluxo_destino_id' => $etapaAnterior->id,
                        'status_condicao_id' => $statusReprovado->id
                    ],
                    [
                        'condicao_tipo' => 'STATUS',
                        'prioridade' => 8,
                        'descricao' => 'Retorna para etapa anterior quando reprovado',
                        'is_ativo' => true
                    ]
                );
            }

            // Transição quando devolvido -> mesma etapa (para correção)
            TransicaoEtapa::updateOrCreate(
                [
                    'etapa_fluxo_origem_id' => $etapaAtual->id,
                    'etapa_fluxo_destino_id' => $etapaAtual->id,
                    'status_condicao_id' => $statusDevolvido->id
                ],
                [
                    'condicao_tipo' => 'STATUS',
                    'prioridade' => 5,
                    'descricao' => 'Mantém na mesma etapa para correção',
                    'is_ativo' => true
                ]
            );
        }

        $this->command->info('Transições de exemplo criadas com sucesso!');
    }
} 