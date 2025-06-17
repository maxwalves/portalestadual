<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Acao;
use App\Models\Demanda;
use App\Models\Organizacao;
use App\Models\TipoFluxo;
use App\Models\TermoAdesao;

class AcaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar dados necessários
        $paranacidade = Organizacao::where('tipo', 'PARANACIDADE')->first();
        $seed = Organizacao::where('tipo', 'SEED')->first();
        
        if (!$paranacidade || !$seed) {
            $this->command->warn('Organizações não encontradas. Execute primeiro o OrganizacaoSeeder.');
            return;
        }

        // Buscar tipos de fluxo
        $fluxoEscola = TipoFluxo::where('nome', 'Fluxo Escola')->first();
        
        if (!$fluxoEscola) {
            $this->command->warn('Tipos de fluxo não encontrados. Execute primeiro os seeders de TipoFluxo e EtapaFluxo.');
            return;
        }

        // Verificar se já existe um termo de adesão
        $termo = TermoAdesao::where('organizacao_id', $seed->id)->first();
        if (!$termo) {
            $this->command->warn('Termo de adesão não encontrado para SEED. É necessário criar um termo primeiro.');
            return;
        }

        // Verificar se já existe uma demanda
        $demanda = Demanda::where('termo_adesao_id', $termo->id)->first();
        if (!$demanda) {
            $this->command->warn('Demanda não encontrada. É necessário criar uma demanda primeiro.');
            return;
        }

        // Criar ação de exemplo
        $acao = Acao::firstOrCreate([
            'codigo_referencia' => 'ESCOLA-TESTE-2025-001',
        ], [
            'demanda_id' => $demanda->id,
            'tipo_fluxo_id' => $fluxoEscola->id,
            'nome' => 'Escola Estadual Teste - Workflow',
            'descricao' => 'Ação de teste para demonstrar o funcionamento do workflow',
            'valor_estimado' => 1000000.00,
            'localizacao' => 'Curitiba/PR - Endereço de teste',
            'coordenadas_lat' => -25.4808,
            'coordenadas_lng' => -49.2914,
            'data_inicio_previsto' => now()->addDays(30),
            'data_fim_previsto' => now()->addMonths(18),
            'status' => 'PLANEJAMENTO',
        ]);

        $this->command->info('Ação de teste criada com sucesso! ID: ' . $acao->id);
        $this->command->info('Acesse o workflow em: /workflow/acao/' . $acao->id);
    }
} 