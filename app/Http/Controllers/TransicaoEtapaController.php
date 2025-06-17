<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransicaoEtapa;
use Illuminate\Support\Facades\DB;

class TransicaoEtapaController extends Controller
{
    /**
     * Exibir uma transição específica
     */
    public function show(TransicaoEtapa $transicao)
    {
        try {
            $transicao->load(['statusCondicao', 'etapaOrigem', 'etapaDestino']);
            
            return response()->json([
                'success' => true,
                'id' => $transicao->id,
                'etapa_fluxo_origem_id' => $transicao->etapa_fluxo_origem_id,
                'etapa_fluxo_destino_id' => $transicao->etapa_fluxo_destino_id,
                'status_condicao_id' => $transicao->status_condicao_id,
                'prioridade' => $transicao->prioridade,
                'descricao' => $transicao->descricao,
                'condicao_tipo' => $transicao->condicao_tipo,
                'is_ativo' => $transicao->is_ativo,
                'statusCondicao' => $transicao->statusCondicao,
                'etapaOrigem' => $transicao->etapaOrigem,
                'etapaDestino' => $transicao->etapaDestino,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar transição: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar nova transição
     */
    public function store(Request $request)
    {
        $request->validate([
            'etapa_fluxo_origem_id' => 'required|exists:etapa_fluxo,id',
            'etapa_fluxo_destino_id' => 'required|exists:etapa_fluxo,id|different:etapa_fluxo_origem_id',
            'status_condicao_id' => 'nullable|exists:status,id',
            'prioridade' => 'required|integer|min:0|max:100',
            'descricao' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $transicao = TransicaoEtapa::create([
                'etapa_fluxo_origem_id' => $request->etapa_fluxo_origem_id,
                'etapa_fluxo_destino_id' => $request->etapa_fluxo_destino_id,
                'status_condicao_id' => $request->status_condicao_id,
                'condicao_tipo' => $request->status_condicao_id ? 'STATUS' : 'SEMPRE',
                'prioridade' => $request->prioridade,
                'descricao' => $request->descricao,
                'is_ativo' => true
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transição criada com sucesso',
                'transicao' => $transicao->load(['statusCondicao', 'etapaDestino'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar transição: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar transição existente
     */
    public function update(Request $request, TransicaoEtapa $transicao)
    {
        $request->validate([
            'etapa_fluxo_destino_id' => 'required|exists:etapa_fluxo,id|different:' . $transicao->etapa_fluxo_origem_id,
            'status_condicao_id' => 'nullable|exists:status,id',
            'prioridade' => 'required|integer|min:0|max:100',
            'descricao' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $transicao->update([
                'etapa_fluxo_destino_id' => $request->etapa_fluxo_destino_id,
                'status_condicao_id' => $request->status_condicao_id,
                'condicao_tipo' => $request->status_condicao_id ? 'STATUS' : 'SEMPRE',
                'prioridade' => $request->prioridade,
                'descricao' => $request->descricao
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transição atualizada com sucesso',
                'transicao' => $transicao->load(['statusCondicao', 'etapaDestino'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar transição: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir transição
     */
    public function destroy(TransicaoEtapa $transicao)
    {
        try {
            $transicao->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transição excluída com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir transição: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar transições de uma etapa
     */
    public function listarPorEtapa($etapaFluxoId)
    {
        try {
            $transicoes = TransicaoEtapa::where('etapa_fluxo_origem_id', $etapaFluxoId)
                ->with(['statusCondicao', 'etapaDestino'])
                ->orderBy('prioridade', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'transicoes' => $transicoes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar transições: ' . $e->getMessage()
            ], 500);
        }
    }
} 