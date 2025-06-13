<?php

namespace App\Http\Controllers;

use App\Models\HistoricoEtapa;
use App\Models\ExecucaoEtapa;
use App\Models\User;
use Illuminate\Http\Request;

class HistoricoEtapaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = HistoricoEtapa::with([
            'execucaoEtapa.etapaFluxo',
            'execucaoEtapa.acao',
            'usuario',
            'statusAnterior',
            'statusNovo'
        ]);

        // Filtros
        if ($request->filled('execucao_etapa_id')) {
            $query->porExecucaoEtapa($request->execucao_etapa_id);
        }

        if ($request->filled('usuario_id')) {
            $query->porUsuario($request->usuario_id);
        }

        if ($request->filled('acao')) {
            $query->porAcao($request->acao);
        }

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->porPeriodo($request->data_inicio, $request->data_fim . ' 23:59:59');
        }

        $historicos = $query->recente()->paginate(30);

        // Dados para filtros
        $usuarios = User::orderBy('name')->get(['id', 'name']);
        $acoes = HistoricoEtapa::distinct('acao')->pluck('acao')->sort();

        return view('historico_etapas.index', compact('historicos', 'usuarios', 'acoes'));
    }

    /**
     * Display the specified resource.
     */
    public function show(HistoricoEtapa $historico)
    {
        $historico->load([
            'execucaoEtapa.etapaFluxo',
            'execucaoEtapa.acao',
            'usuario',
            'statusAnterior',
            'statusNovo'
        ]);

        return view('historico_etapas.show', compact('historico'));
    }

    /**
     * Histórico de uma execução específica
     */
    public function porExecucao(ExecucaoEtapa $execucaoEtapa)
    {
        $historicos = HistoricoEtapa::with([
            'usuario',
            'statusAnterior',
            'statusNovo'
        ])
        ->porExecucaoEtapa($execucaoEtapa->id)
        ->recente()
        ->get();

        $execucaoEtapa->load([
            'etapaFluxo',
            'acao',
            'status'
        ]);

        return view('historico_etapas.por_execucao', compact('historicos', 'execucaoEtapa'));
    }

    /**
     * Relatório de atividades por usuário
     */
    public function relatorioUsuario(Request $request, User $usuario)
    {
        $dataInicio = $request->data_inicio ?? now()->subMonth()->toDateString();
        $dataFim = $request->data_fim ?? now()->toDateString();

        $historicos = HistoricoEtapa::with([
            'execucaoEtapa.etapaFluxo',
            'execucaoEtapa.acao',
            'statusAnterior',
            'statusNovo'
        ])
        ->porUsuario($usuario->id)
        ->porPeriodo($dataInicio, $dataFim . ' 23:59:59')
        ->recente()
        ->get();

        // Estatísticas
        $estatisticas = [
            'total_acoes' => $historicos->count(),
            'mudancas_status' => $historicos->where('acao', 'MUDANCA_STATUS')->count(),
            'envios_documento' => $historicos->where('acao', 'ENVIO_DOCUMENTO')->count(),
            'aprovacoes' => $historicos->where('acao', 'APROVACAO')->count(),
            'reprovacoes' => $historicos->where('acao', 'REPROVACAO')->count(),
        ];

        // Ações por dia
        $acoesPorDia = $historicos->groupBy(function($item) {
            return $item->data_acao->format('Y-m-d');
        })->map->count();

        return view('historico_etapas.relatorio_usuario', compact(
            'usuario', 
            'historicos', 
            'estatisticas', 
            'acoesPorDia',
            'dataInicio',
            'dataFim'
        ));
    }

    /**
     * API: Timeline de uma execução
     */
    public function apiTimeline(ExecucaoEtapa $execucaoEtapa)
    {
        $historicos = HistoricoEtapa::with([
            'usuario:id,name',
            'statusAnterior:id,nome,cor',
            'statusNovo:id,nome,cor'
        ])
        ->porExecucaoEtapa($execucaoEtapa->id)
        ->recente()
        ->get([
            'id', 'acao', 'descricao_acao', 'observacao', 
            'data_acao', 'usuario_id', 'status_anterior_id', 'status_novo_id'
        ])
        ->map(function($historico) {
            return [
                'id' => $historico->id,
                'acao' => $historico->acao,
                'descricao' => $historico->getResumoAcao(),
                'data' => $historico->data_acao->format('d/m/Y H:i'),
                'usuario' => $historico->usuario->name ?? 'Sistema',
                'icone' => $historico->getIconeAcao(),
                'cor' => $historico->getCorAcao(),
                'status_anterior' => $historico->statusAnterior ? [
                    'nome' => $historico->statusAnterior->nome,
                    'cor' => $historico->statusAnterior->cor
                ] : null,
                'status_novo' => $historico->statusNovo ? [
                    'nome' => $historico->statusNovo->nome,
                    'cor' => $historico->statusNovo->cor
                ] : null,
            ];
        });

        return response()->json($historicos);
    }

    /**
     * API: Estatísticas de atividades
     */
    public function apiEstatisticas(Request $request)
    {
        $dataInicio = $request->data_inicio ?? now()->subMonth()->toDateString();
        $dataFim = $request->data_fim ?? now()->toDateString();

        $query = HistoricoEtapa::porPeriodo($dataInicio, $dataFim . ' 23:59:59');

        $estatisticas = [
            'total_acoes' => $query->count(),
            'usuarios_ativos' => $query->distinct('usuario_id')->count(),
            'acoes_por_tipo' => $query->groupBy('acao')
                ->select('acao', \DB::raw('count(*) as total'))
                ->pluck('total', 'acao'),
            'atividade_diaria' => $query->selectRaw('DATE(data_acao) as data, count(*) as total')
                ->groupBy('data')
                ->orderBy('data')
                ->pluck('total', 'data'),
        ];

        return response()->json($estatisticas);
    }

    /**
     * Exportar histórico (CSV)
     */
    public function exportar(Request $request)
    {
        $query = HistoricoEtapa::with([
            'execucaoEtapa.etapaFluxo',
            'execucaoEtapa.acao',
            'usuario',
            'statusAnterior',
            'statusNovo'
        ]);

        // Aplicar mesmos filtros do index
        if ($request->filled('execucao_etapa_id')) {
            $query->porExecucaoEtapa($request->execucao_etapa_id);
        }

        if ($request->filled('usuario_id')) {
            $query->porUsuario($request->usuario_id);
        }

        if ($request->filled('acao')) {
            $query->porAcao($request->acao);
        }

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->porPeriodo($request->data_inicio, $request->data_fim . ' 23:59:59');
        }

        $historicos = $query->recente()->get();

        $filename = 'historico_etapas_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($historicos) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalho
            fputcsv($file, [
                'Data/Hora',
                'Ação',
                'Descrição',
                'Usuário',
                'Etapa',
                'Ação/Obra',
                'Status Anterior',
                'Status Novo',
                'Observação'
            ], ';');

            // Dados
            foreach ($historicos as $historico) {
                fputcsv($file, [
                    $historico->data_acao->format('d/m/Y H:i:s'),
                    $historico->acao,
                    $historico->descricao_acao ?: '',
                    $historico->usuario->name ?? '',
                    $historico->execucaoEtapa->etapaFluxo->nome_etapa ?? '',
                    $historico->execucaoEtapa->acao->nome ?? '',
                    $historico->statusAnterior->nome ?? '',
                    $historico->statusNovo->nome ?? '',
                    $historico->observacao ?: ''
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
