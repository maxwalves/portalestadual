<?php

namespace App\Console\Commands;

use App\Models\Cidade;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SincronizarCidades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cidades:sincronizar {--estado=PR : Estado para sincronizar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar cidades do estado com a API do IBGE';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $estado = $this->option('estado');
        $codigoEstado = $this->getCodigoEstado($estado);
        
        if (!$codigoEstado) {
            $this->error("Estado {$estado} não é suportado ou não foi encontrado.");
            return 1;
        }

        $this->info("Iniciando sincronização das cidades do {$estado}...");

        try {
            // Buscar cidades na API do IBGE
            $url = "https://servicodados.ibge.gov.br/api/v1/localidades/estados/{$codigoEstado}/municipios";
            
            $client = new Client(['timeout' => 30]);
            $response = $client->get($url);
            $cidadesApi = json_decode($response->getBody(), true);

            if (empty($cidadesApi)) {
                $this->error('Nenhuma cidade foi retornada pela API do IBGE.');
                return 1;
            }

            $this->info("Encontradas " . count($cidadesApi) . " cidades na API do IBGE.");

            // Usar transação para garantir consistência
            DB::beginTransaction();

            try {
                // Limpar cidades existentes do estado
                Cidade::where('estado', $estado)->delete();
                $this->info("Cidades antigas do {$estado} removidas.");

                // Inserir novas cidades
                $barra = $this->output->createProgressBar(count($cidadesApi));
                $barra->start();

                foreach ($cidadesApi as $cidadeApi) {
                    Cidade::create([
                        'ibge_id' => $cidadeApi['id'],
                        'nome' => $cidadeApi['nome'],
                        'estado' => $estado,
                    ]);
                    $barra->advance();
                }

                $barra->finish();
                $this->newLine();

                DB::commit();
                
                $total = Cidade::where('estado', $estado)->count();
                $this->info("✅ Sincronização concluída! {$total} cidades do {$estado} foram importadas.");
                
                return 0;

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            $this->error("❌ Erro ao sincronizar cidades: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Obter código do estado para a API do IBGE
     */
    private function getCodigoEstado($estado)
    {
        $estados = [
            'AC' => 12, 'AL' => 17, 'AP' => 16, 'AM' => 13, 'BA' => 29,
            'CE' => 23, 'DF' => 53, 'ES' => 32, 'GO' => 52, 'MA' => 21,
            'MT' => 51, 'MS' => 50, 'MG' => 31, 'PA' => 15, 'PB' => 25,
            'PR' => 41, 'PE' => 26, 'PI' => 22, 'RJ' => 33, 'RN' => 24,
            'RS' => 43, 'RO' => 11, 'RR' => 14, 'SC' => 42, 'SP' => 35,
            'SE' => 28, 'TO' => 17
        ];

        return $estados[$estado] ?? null;
    }
}
