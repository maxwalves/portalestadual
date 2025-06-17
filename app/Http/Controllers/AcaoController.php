<?php

namespace App\Http\Controllers;

use App\Models\Acao;
use App\Models\Demanda;
use App\Models\TipoFluxo;
use Illuminate\Http\Request;
use App\Traits\HasOrganizacaoAccess;
use App\Models\Organizacao;

class AcaoController extends Controller
{
    use HasOrganizacaoAccess;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Construir query baseada no role do usuário
        $query = Acao::with(['demanda.termoAdesao.organizacao', 'tipoFluxo']);
        
        // Filtrar por organização baseado no role
        if ($user->hasRole(['admin', 'admin_paranacidade', 'tecnico_paranacidade'])) {
            // Admin sistema e Paranacidade veem todas
            // Não aplicar filtro de organização
        } elseif ($user->hasRole(['admin_secretaria', 'tecnico_secretaria'])) {
            // Usuários de secretaria veem:
            // 1. Ações da sua organização (solicitante)
            // 2. Ações onde sua organização participa como executora de etapas
            $userOrgId = $user->organizacao_id;
            
            $query->where(function($q) use ($userOrgId) {
                // Ações da própria organização (solicitante)
                $q->whereHas('demanda.termoAdesao', function($subQ) use ($userOrgId) {
                    $subQ->where('organizacao_id', $userOrgId);
                })
                // OU ações onde a organização é executora de alguma etapa
                ->orWhereHas('tipoFluxo.etapasFluxo', function($subQ) use ($userOrgId) {
                    $subQ->where('organizacao_executora_id', $userOrgId);
                });
            });
        } else {
            // Outros usuários não veem nada
            $query->whereRaw('1 = 0');
        }
        
        // Aplicar filtros da interface
        if ($request->filled('organizacao_solicitante')) {
            $query->whereHas('demanda.termoAdesao', function($q) use ($request) {
                $q->where('organizacao_id', $request->organizacao_solicitante);
            });
        }
        
        if ($request->filled('tipo_fluxo')) {
            $query->where('tipo_fluxo_id', $request->tipo_fluxo);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('descricao', 'like', "%{$busca}%")
                  ->orWhere('projeto_sam', 'like', "%{$busca}%");
            });
        }

        $acoes = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Dados para os filtros
        $organizacoes = [];
        $tiposFluxo = [];
        
        if ($user->hasRole(['admin', 'admin_paranacidade', 'tecnico_paranacidade'])) {
            $organizacoes = Organizacao::where('is_ativo', true)->orderBy('nome')->get();
            $tiposFluxo = TipoFluxo::where('is_ativo', true)->orderBy('nome')->get();
        } else {
            // Para usuários de secretaria, mostrar apenas organizações relacionadas
            $orgIds = Acao::with(['demanda.termoAdesao.organizacao', 'tipoFluxo.etapasFluxo.organizacaoExecutora'])
                ->where(function($q) use ($user) {
                    $q->whereHas('demanda.termoAdesao', function($subQ) use ($user) {
                        $subQ->where('organizacao_id', $user->organizacao_id);
                    })
                    ->orWhereHas('tipoFluxo.etapasFluxo', function($subQ) use ($user) {
                        $subQ->where('organizacao_executora_id', $user->organizacao_id);
                    });
                })
                ->get()
                ->flatMap(function($acao) {
                    $orgs = collect();
                    if ($acao->demanda && $acao->demanda->termoAdesao) {
                        $orgs->push($acao->demanda->termoAdesao->organizacao);
                    }
                    if ($acao->tipoFluxo) {
                        $acao->tipoFluxo->etapasFluxo->each(function($etapa) use ($orgs) {
                            $orgs->push($etapa->organizacaoExecutora);
                        });
                    }
                    return $orgs;
                })
                ->unique('id');
                
            $organizacoes = $orgIds->sortBy('nome');
            $tiposFluxo = TipoFluxo::where('is_ativo', true)->orderBy('nome')->get();
        }

        return view('acoes.index', compact('acoes', 'organizacoes', 'tiposFluxo'));
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
        
        $demandas = $demandasQuery->orderBy('descricao')->get();
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
     * Verificar se o usuário pode acessar uma ação específica
     * Considera tanto organizações solicitantes quanto executoras
     */
    protected function canAccessAcao(Acao $acao): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Admin de sistema e Paranacidade sempre podem
        if ($user->hasRole(['admin', 'admin_paranacidade', 'tecnico_paranacidade'])) {
            return true;
        }

        // Para usuários de secretaria, verificar se:
        if ($user->hasRole(['admin_secretaria', 'tecnico_secretaria'])) {
            $userOrgId = $user->organizacao_id;
            
            // 1. A organização é solicitante (dona da demanda)
            $acao->load(['demanda.termoAdesao']);
            if ($acao->demanda && $acao->demanda->termoAdesao && 
                $acao->demanda->termoAdesao->organizacao_id == $userOrgId) {
                return true;
            }
            
            // 2. A organização é executora de alguma etapa
            $acao->load(['tipoFluxo.etapasFluxo']);
            if ($acao->tipoFluxo && $acao->tipoFluxo->etapasFluxo) {
                $isExecutora = $acao->tipoFluxo->etapasFluxo
                    ->where('organizacao_executora_id', $userOrgId)
                    ->isNotEmpty();
                
                if ($isExecutora) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Display the specified resource.
     */
    public function show(Acao $acao)
    {
        // Verificar acesso usando a nova lógica
        if (!$this->canAccessAcao($acao)) {
            abort(403, 'Você não tem permissão para visualizar esta ação.');
        }

        // Redirecionar diretamente para o workflow da ação
        return redirect()->route('workflow.acao', $acao);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Acao $acao)
    {
        if (!$this->canEdit()) {
            abort(403, 'Você não tem permissão para editar ações.');
        }

        // Verificar acesso usando a nova lógica
        if (!$this->canAccessAcao($acao)) {
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
        
        $demandas = $demandasQuery->orderBy('descricao')->get();
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

        // Verificar acesso usando a nova lógica
        if (!$this->canAccessAcao($acao)) {
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

        // Verificar acesso usando a nova lógica
        if (!$this->canAccessAcao($acao)) {
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
                ->with('success', 'Ação excluída com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('acoes.index')
                ->with('error', 'Erro ao excluir a ação: ' . $e->getMessage());
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
