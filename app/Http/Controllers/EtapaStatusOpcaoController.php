<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EtapaStatusOpcao;
use Illuminate\Support\Facades\DB;

class EtapaStatusOpcaoController extends Controller
{
    /**
     * Salvar ou atualizar opção de status para uma etapa
     */
    public function salvar(Request $request)
    {
        $request->validate([
            'etapa_fluxo_id' => 'required|exists:etapa_fluxo,id',
            'status_id' => 'required|exists:status,id',
            'ordem' => 'required|integer|min:0|max:10',
            'mostra_para_responsavel' => 'nullable|in:true,false,1,0',
            'requer_justificativa' => 'nullable|in:true,false,1,0'
        ]);

        try {
            DB::beginTransaction();

            // Converter valores para boolean de forma mais robusta
            $mostraParaResponsavel = $this->convertToBoolean($request->input('mostra_para_responsavel'));
            $requerJustificativa = $this->convertToBoolean($request->input('requer_justificativa'));

            $dados = [
                'ordem' => $request->ordem,
                'mostra_para_responsavel' => $mostraParaResponsavel,
                'requer_justificativa' => $requerJustificativa,
                'is_padrao' => false // Por padrão, não é status padrão
            ];

            // Usar o método createOrUpdate que funciona com chave primária composta
            $opcao = EtapaStatusOpcao::createOrUpdate(
                $request->etapa_fluxo_id,
                $request->status_id,
                $dados
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Opção de status salva com sucesso',
                'opcao' => $opcao
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar opção de status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Converter valor para boolean de forma robusta
     */
    private function convertToBoolean($value)
    {
        if (is_null($value)) {
            return false;
        }
        
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
        }
        
        if (is_numeric($value)) {
            return (bool) $value;
        }
        
        return false;
    }

    /**
     * Remover opção de status de uma etapa
     */
    public function remover(Request $request)
    {
        $request->validate([
            'etapa_fluxo_id' => 'required|exists:etapa_fluxo,id',
            'status_id' => 'required|exists:status,id'
        ]);

        try {
            $opcao = EtapaStatusOpcao::where('etapa_fluxo_id', $request->etapa_fluxo_id)
                ->where('status_id', $request->status_id)
                ->first();

            if ($opcao) {
                $opcao->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Opção de status removida com sucesso'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Opção de status não encontrada'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover opção de status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar opções de status de uma etapa
     */
    public function listar($etapaFluxoId)
    {
        try {
            $opcoes = EtapaStatusOpcao::where('etapa_fluxo_id', $etapaFluxoId)
                ->with('status')
                ->orderBy('ordem')
                ->get();

            return response()->json([
                'success' => true,
                'opcoes' => $opcoes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar opções de status: ' . $e->getMessage()
            ], 500);
        }
    }
} 