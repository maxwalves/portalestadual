<?php

namespace App\Http\Controllers;

use App\Models\Acao;
use App\Models\Demanda;
use App\Models\TipoFluxo;
use Illuminate\Http\Request;

class AcaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $acoes = Acao::with(['demanda', 'tipoFluxo'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('acoes.index', compact('acoes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $demandas = Demanda::orderBy('descricao')->get();
        $tipoFluxos = TipoFluxo::where('ativo', true)->orderBy('nome')->get();

        return view('acoes.create', compact('demandas', 'tipoFluxos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'demanda_id' => 'required|exists:demandas,id',
            'projeto_sam' => 'nullable|string|max:255',
            'tipo_fluxo_id' => 'required|exists:tipo_fluxos,id',
            'valor_estimado' => 'nullable|numeric|min:0|max:999999999999.99',
            'valor_contratado' => 'nullable|numeric|min:0|max:999999999999.99',
            'localizacao' => 'nullable|string|max:255',
        ], [
            'valor_estimado.max' => 'O valor estimado não pode ser superior a R$ 999.999.999.999,99.',
            'valor_contratado.max' => 'O valor contratado não pode ser superior a R$ 999.999.999.999,99.',
            'valor_estimado.numeric' => 'O valor estimado deve ser um número válido.',
            'valor_contratado.numeric' => 'O valor contratado deve ser um número válido.',
        ]);

        Acao::create($request->all());

        return redirect()->route('acoes.index')
            ->with('success', 'Ação criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Acao $acao)
    {
        // TODO: Descomentar quando ExecucaoEtapa for implementado
        // $acao->load(['demanda.organizacao', 'tipoFluxo', 'execucoesEtapa']);
        $acao->load(['demanda.termoAdesao.organizacao', 'demanda.organizacao', 'tipoFluxo']);

        return view('acoes.show', compact('acao'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Acao $acao)
    {
        $demandas = Demanda::orderBy('descricao')->get();
        $tipoFluxos = TipoFluxo::where('ativo', true)->orderBy('nome')->get();

        return view('acoes.edit', compact('acao', 'demandas', 'tipoFluxos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Acao $acao)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'demanda_id' => 'required|exists:demandas,id',
            'projeto_sam' => 'nullable|string|max:255',
            'tipo_fluxo_id' => 'required|exists:tipo_fluxos,id',
            'valor_estimado' => 'nullable|numeric|min:0|max:999999999999.99',
            'valor_contratado' => 'nullable|numeric|min:0|max:999999999999.99',
            'localizacao' => 'nullable|string|max:255',
        ], [
            'valor_estimado.max' => 'O valor estimado não pode ser superior a R$ 999.999.999.999,99.',
            'valor_contratado.max' => 'O valor contratado não pode ser superior a R$ 999.999.999.999,99.',
            'valor_estimado.numeric' => 'O valor estimado deve ser um número válido.',
            'valor_contratado.numeric' => 'O valor contratado deve ser um número válido.',
        ]);

        $acao->update($request->all());

        return redirect()->route('acoes.index')
            ->with('success', 'Ação atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Acao $acao)
    {
        try {
            // TODO: Descomentar quando ExecucaoEtapa for implementado
            // Verificar se existem execuções de etapa associadas
            // $hasExecucoes = $acao->execucoesEtapa()->count() > 0;
            // 
            // if ($hasExecucoes) {
            //     return redirect()->route('acoes.index')
            //         ->with('error', 'Não é possível excluir esta ação pois existem execuções de etapa associadas.');
            // }
            
            $acao->delete();
            
            return redirect()->route('acoes.index')
                ->with('success', 'Ação removida com sucesso!');
                
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir ação: ' . $e->getMessage(), [
                'acao_id' => $acao->id ?? 'null',
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('acoes.index')
                ->with('error', 'Erro ao excluir ação: ' . $e->getMessage());
        }
    }

    /**
     * Buscar cidades do Paraná no banco de dados local
     */
    public function getCidadesParana(Request $request)
    {
        try {
            $term = $request->get('term', '');
            
            if (strlen($term) < 2) {
                return response()->json([]);
            }
            
            $cidades = \App\Models\Cidade::searchForAutocomplete($term, 10);
            
            return response()->json($cidades);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar cidades do Paraná: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar cidades'], 500);
        }
    }
}
