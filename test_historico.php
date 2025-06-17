<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ExecucaoEtapa;
use App\Models\HistoricoEtapa;

try {
    echo "Testando modelo HistoricoEtapa...\n";
    
    // Verificar se há execuções de etapa
    $execucoes = ExecucaoEtapa::count();
    echo "Total de execuções: $execucoes\n";
    
    if ($execucoes > 0) {
        $execucao = ExecucaoEtapa::with(['acao', 'etapaFluxo', 'status'])->first();
        echo "Primeira execução encontrada: ID {$execucao->id}\n";
        echo "Ação: {$execucao->acao->nome}\n";
        echo "Etapa: {$execucao->etapaFluxo->nome_etapa}\n";
        echo "Status: {$execucao->status->nome}\n";
        
        // Verificar históricos desta execução
        $historicos = HistoricoEtapa::where('execucao_etapa_id', $execucao->id)->count();
        echo "Históricos desta execução: $historicos\n";
    }
    
    echo "\nTeste concluído com sucesso!\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
} 