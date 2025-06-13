<?php

namespace App\Http\Controllers;

use App\Models\Acao;
use App\Models\Demanda;
use App\Models\TipoFluxo;
use Illuminate\Http\Request;
use App\Traits\HasOrganizacaoAccess;

class AcaoController extends Controller
{
    use HasOrganizacaoAccess;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Construir query baseada no role do usuário
        $query = Acao::with(['demanda.termoAdesao.organizacao', 'tipoFluxo']);
        
        // Filtrar por organização baseado no role
        if ($user->hasRole(['admin', 'admin_paranacidade', 'tecnico_paranacidade'])) {
            // Admin sistema e Paranacidade veem todas
            // Não aplicar filtro
        } elseif ($user->hasRole(['admin_secretaria', 'tecnico_secretaria'])) {
            // Usuários de secretaria veem apenas da sua organização
            $query->whereHas('demanda.termoAdesao', function($q) use ($user) {
                $q->where('organizacao_id', $user->organizacao_id);
            });
        } else {
            // Outros usuários não veem nada
            $query->whereRaw('1 = 0');
        }
        
        $acoes = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('acoes.index', compact('acoes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!$this->canCreate()) {
            abort(403, 'Você não tem permissão para criar ações.');
        }

        $user = auth()->user();
        
        // Filtrar demandas baseado na organização
        $demandasQuery = Demanda::with(['termoAdesao.organizacao']);
        
        if ($user->hasRole(['admin_secretaria', 'tecnico_secretaria'])) {
            $demandasQuery->whereHas('termoAdesao', function($q) use ($user) {
                $q->where('organizacao_id', $user->organizacao_id);
            });
        }
        
        $demandas = $demandasQuery->orderBy('nome')->get();
        $tipoFluxos = TipoFluxo::where('is_ativo', true)->orderBy('nome')->get();

        return view('acoes.create', compact('demandas', 'tipoFluxos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$this->canCreate()) {
            abort(403, 'Você não tem permissão para criar ações.');
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'demanda_id' => 'required|exists:demandas,id',
            'projeto_sam' => 'nullable|string|max:255',
            'tipo_fluxo_id' => 'required|exists:tipo_fluxo,id',
            'valor_estimado' => 'nullable|numeric|min:0|max:999999999999.99',
            'valor_contratado' => 'nullable|numeric|min:0|max:999999999999.99',
            'localizacao' => 'nullable|string|max:500',
            'data_inicio_previsto' => 'nullable|date',
            'data_fim_previsto' => 'nullable|date|after_or_equal:data_inicio_previsto',
        ], [
            'valor_estimado.max' => 'O valor estimado não pode ser superior a R$ 999.999.999.999,99.',
            'valor_contratado.max' => 'O valor contratado não pode ser superior a R$ 999.999.999.999,99.',
            'valor_estimado.numeric' => 'O valor estimado deve ser um número válido.',
            'valor_contratado.numeric' => 'O valor contratado deve ser um número válido.',
        ]);

        // Verificar se o usuário pode criar ação para esta demanda
        $demanda = Demanda::with('termoAdesao')->findOrFail($request->demanda_id);
        $user = auth()->user();
        
        if ($user->hasRole(['admin_secretaria', 'tecnico_secretaria'])) {
            if ($demanda->termoAdesao->organizacao_id != $user->organizacao_id) {
                abort(403, 'Você só pode criar ações para demandas da sua organização.');
            }
        }

        $data = $request->all();
        $data['created_by'] = auth()->id();

        Acao::create($data);

        return redirect()->route('acoes.index')
            ->with('success', 'Ação criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Acao $acao)
    {
        $acao->load(['demanda.termoAdesao.organizacao', 'tipoFluxo']);
        
        // Verificar acesso organizacional
        if (!$this->canAccessOrganizacao($acao->demanda->termoAdesao->organizacao_id)) {
            abort(403, 'Você não tem permissão para visualizar esta ação.');
        }

        return view('acoes.show', compact('acao'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Acao $acao)
    {
        if (!$this->canEdit()) {
            abort(403, 'Você não tem permissão para editar ações.');
        }

        $acao->load(['demanda.termoAdesao.organizacao']);
        
        // Verificar acesso organizacional
        if (!$this->canAccessOrganizacao($acao->demanda->termoAdesao->organizacao_id)) {
            abort(403, 'Você não tem permissão para editar esta ação.');
        }

        $user = auth()->user();
        
        // Filtrar demandas baseado na organização
        $demandasQuery = Demanda::with(['termoAdesao.organizacao']);
        
        if ($user->hasRole(['admin_secretaria', 'tecnico_secretaria'])) {
            $demandasQuery->whereHas('termoAdesao', function($q) use ($user) {
                $q->where('organizacao_id', $user->organizacao_id);
            });
        }
        
        $demandas = $demandasQuery->orderBy('nome')->get();
        $tipoFluxos = TipoFluxo::where('is_ativo', true)->orderBy('nome')->get();

        return view('acoes.edit', compact('acao', 'demandas', 'tipoFluxos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Acao $acao)
    {
        if (!$this->canEdit()) {
            abort(403, 'Você não tem permissão para editar ações.');
        }

        $acao->load(['demanda.termoAdesao']);
        
        // Verificar acesso organizacional
        if (!$this->canAccessOrganizacao($acao->demanda->termoAdesao->organizacao_id)) {
            abort(403, 'Você não tem permissão para editar esta ação.');
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'demanda_id' => 'required|exists:demandas,id',
            'projeto_sam' => 'nullable|string|max:255',
            'tipo_fluxo_id' => 'required|exists:tipo_fluxo,id',
            'valor_estimado' => 'nullable|numeric|min:0|max:999999999999.99',
            'valor_contratado' => 'nullable|numeric|min:0|max:999999999999.99',
            'localizacao' => 'nullable|string|max:500',
            'data_inicio_previsto' => 'nullable|date',
            'data_fim_previsto' => 'nullable|date|after_or_equal:data_inicio_previsto',
        ], [
            'valor_estimado.max' => 'O valor estimado não pode ser superior a R$ 999.999.999.999,99.',
            'valor_contratado.max' => 'O valor contratado não pode ser superior a R$ 999.999.999.999,99.',
            'valor_estimado.numeric' => 'O valor estimado deve ser um número válido.',
            'valor_contratado.numeric' => 'O valor contratado deve ser um número válido.',
        ]);

        $data = $request->all();
        $data['updated_by'] = auth()->id();

        $acao->update($data);

        return redirect()->route('acoes.index')
            ->with('success', 'Ação atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Acao $acao)
    {
        if (!$this->canDelete()) {
            abort(403, 'Você não tem permissão para excluir ações.');
        }

        $acao->load(['demanda.termoAdesao']);
        
        // Verificar acesso organizacional
        if (!$this->canAccessOrganizacao($acao->demanda->termoAdesao->organizacao_id)) {
            abort(403, 'Você não tem permissão para excluir esta ação.');
        }

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
