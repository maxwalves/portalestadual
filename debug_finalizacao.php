<?php

require_once 'vendor/autoload.php';

use App\Models\TransicaoEtapa;
use App\Models\Status;
use App\Models\ExecucaoEtapa;
use App\Models\EtapaFluxo;
use App\Models\Acao;

// Debug das transições configuradas para status FINALIZADO

echo "=== DEBUG: TRANSIÇÕES CONFIGURADAS PARA STATUS FINALIZADO ===\n\n";

// 1. Buscar status FINALIZADO
$statusFinalizado = Status::where('codigo', 'FINALIZADO')->first();
if ($statusFinalizado) {
    echo "Status FINALIZADO encontrado:\n";
    echo "ID: {$statusFinalizado->id}\n";
    echo "Nome: {$statusFinalizado->nome}\n";
    echo "Código: {$statusFinalizado->codigo}\n\n";
    
    // 2. Buscar todas as transições que usam o status FINALIZADO como condição
    $transicoes = TransicaoEtapa::where('status_condicao_id', $statusFinalizado->id)
        ->with(['etapaOrigem', 'etapaDestino', 'statusCondicao'])
        ->get();
    
    if ($transicoes->count() > 0) {
        echo "PROBLEMA ENCONTRADO! Existem {$transicoes->count()} transições configuradas para o status FINALIZADO:\n\n";
        
        foreach ($transicoes as $transicao) {
            echo "Transição ID: {$transicao->id}\n";
            echo "Etapa Origem: {$transicao->etapaOrigem->nome_etapa} (ID: {$transicao->etapa_fluxo_origem_id})\n";
            echo "Etapa Destino: {$transicao->etapaDestino->nome_etapa} (ID: {$transicao->etapa_fluxo_destino_id})\n";
            echo "Status Condição: {$transicao->statusCondicao->nome}\n";
            echo "Ativa: " . ($transicao->ativa ? 'Sim' : 'Não') . "\n";
            echo "Prioridade: {$transicao->prioridade}\n";
            echo "Descrição: {$transicao->descricao}\n";
            echo "---\n\n";
        }
        
        echo "SOLUÇÃO: Essas transições devem ser removidas ou desativadas, pois quando o status é FINALIZADO, o projeto deve PARAR o fluxo.\n\n";
        
    } else {
        echo "✅ Nenhuma transição configurada para status FINALIZADO. Está correto!\n\n";
    }
} else {
    echo "❌ Status FINALIZADO não encontrado no banco de dados!\n\n";
}

// 3. Verificar também se há etapas da ordem 4 que podem estar causando problema
echo "=== VERIFICAÇÃO DAS ETAPAS DE ORDEM 4 ===\n\n";

$etapasOrdem4 = EtapaFluxo::where('ordem_execucao', 4)->get();

foreach ($etapasOrdem4 as $etapa) {
    echo "Etapa ID: {$etapa->id}\n";
    echo "Nome: {$etapa->nome_etapa}\n";
    echo "Tipo Fluxo ID: {$etapa->tipo_fluxo_id}\n";
    echo "Ordem: {$etapa->ordem_execucao}\n";
    
    // Verificar se há etapas posteriores (ordem 5, 6, etc.)
    $etapasPosteriores = EtapaFluxo::where('tipo_fluxo_id', $etapa->tipo_fluxo_id)
        ->where('ordem_execucao', '>', 4)
        ->count();
    
    echo "Etapas posteriores (ordem > 4): {$etapasPosteriores}\n";
    
    // Verificar transições desta etapa
    $transicoesEtapa = TransicaoEtapa::where('etapa_fluxo_origem_id', $etapa->id)
        ->with(['etapaDestino', 'statusCondicao'])
        ->get();
    
    echo "Transições configuradas: {$transicoesEtapa->count()}\n";
    
    foreach ($transicoesEtapa as $trans) {
        echo "  → Para etapa: {$trans->etapaDestino->nome_etapa} (ordem: {$trans->etapaDestino->ordem_execucao}) quando status: {$trans->statusCondicao->nome}\n";
    }
    
    echo "---\n\n";
}

echo "Verifique se alguma etapa 4 tem transições que estão causando o redirecionamento!\n"; 