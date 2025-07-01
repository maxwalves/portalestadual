<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Status::query();

        // Filtros
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        if ($request->filled('ativo')) {
            $query->where('is_ativo', $request->ativo === '1');
        }

        $status = $query->ordenado()->paginate(15);

        $categorias = ['EXECUCAO', 'DOCUMENTO', 'GERAL'];

        return view('status.index', compact('status', 'categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = ['EXECUCAO', 'DOCUMENTO', 'GERAL'];
        return view('status.create', compact('categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:50|unique:status,codigo',
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'categoria' => 'required|in:EXECUCAO,DOCUMENTO,GERAL',
            'cor' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icone' => 'nullable|string|max:50',
            'ordem' => 'required|integer|min:0',
            'is_ativo' => 'boolean',
        ]);

        $data = $request->all();
        // Garantir que is_ativo seja tratado corretamente (checkbox)
        $data['is_ativo'] = $request->has('is_ativo') ? 1 : 0;

        $status = Status::create($data);

        return redirect()
            ->route('status.index')
            ->with('success', 'Status criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Status $status)
    {
        // Carregar estatísticas
        $status->load([
            'execucaoEtapas' => function($query) {
                $query->select('status_id', DB::raw('count(*) as total'))
                      ->groupBy('status_id');
            }
        ]);

        // Estatísticas de uso
        $estatisticas = [
            'execucoes_ativas' => $status->execucaoEtapas()->count(),
            'transicoes_configuradas' => $status->transicoesCondicao()->count(),
            'historicos_entrada' => $status->historicosStatusNovo()->count(),
            'historicos_saida' => $status->historicosStatusAnterior()->count(),
        ];

        return view('status.show', compact('status', 'estatisticas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Status $status)
    {
        $categorias = ['EXECUCAO', 'DOCUMENTO', 'GERAL'];
        return view('status.edit', compact('status', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Status $status)
    {
        $request->validate([
            'codigo' => 'required|string|max:50|unique:status,codigo,' . $status->id,
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'categoria' => 'required|in:EXECUCAO,DOCUMENTO,GERAL',
            'cor' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icone' => 'nullable|string|max:50',
            'ordem' => 'required|integer|min:0',
            'is_ativo' => 'boolean',
        ]);

        $data = $request->all();
        // Garantir que is_ativo seja tratado corretamente (checkbox)
        $data['is_ativo'] = $request->has('is_ativo') ? 1 : 0;

        $status->update($data);

        return redirect()
            ->route('status.index')
            ->with('success', 'Status atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Status $status)
    {
        // Verificar se o status está sendo usado
        if ($status->execucaoEtapas()->exists()) {
            return redirect()
                ->route('status.index')
                ->with('error', 'Não é possível excluir este status pois está sendo usado em execuções de etapas.');
        }

        if ($status->transicoesCondicao()->exists()) {
            return redirect()
                ->route('status.index')
                ->with('error', 'Não é possível excluir este status pois está sendo usado em transições.');
        }

        $status->delete();

        return redirect()
            ->route('status.index')
            ->with('success', 'Status excluído com sucesso!');
    }

    /**
     * Ativar/desativar status
     */
    public function toggleAtivo(Status $status)
    {
        $status->update(['is_ativo' => !$status->is_ativo]);

        $mensagem = $status->is_ativo ? 'Status ativado' : 'Status desativado';

        return redirect()
            ->route('status.index')
            ->with('success', $mensagem . ' com sucesso!');
    }

    /**
     * API: Retorna status por categoria
     */
    public function apiPorCategoria(Request $request)
    {
        $categoria = $request->categoria;
        
        $status = Status::ativos()
            ->when($categoria, function($query, $categoria) {
                $query->porCategoria($categoria);
            })
            ->ordenado()
            ->get(['id', 'codigo', 'nome', 'cor']);

        return response()->json($status);
    }

    /**
     * API: Retorna status disponíveis para uma etapa
     */
    public function apiStatusEtapa(Request $request)
    {
        $etapaId = $request->etapa_id;
        
        if (!$etapaId) {
            return response()->json(['error' => 'etapa_id é obrigatório'], 400);
        }

        $status = Status::whereHas('etapaStatusOpcoes', function($query) use ($etapaId) {
            $query->where('etapa_fluxo_id', $etapaId);
        })
        ->with(['etapaStatusOpcoes' => function($query) use ($etapaId) {
            $query->where('etapa_fluxo_id', $etapaId)
                  ->select('etapa_fluxo_id', 'status_id', 'ordem', 'is_padrao', 'requer_justificativa');
        }])
        ->ordenado()
        ->get(['id', 'codigo', 'nome', 'cor']);

        return response()->json($status);
    }
}
