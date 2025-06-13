<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demanda;
use App\Models\TermoAdesao;
use App\Models\CadastroDemandaGms;
use App\Traits\HasOrganizacaoAccess;

class DemandaController extends Controller
{
    use HasOrganizacaoAccess;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Construir query baseada no role do usuário
        $query = Demanda::with(['termoAdesao.organizacao', 'cadastroDemandaGms']);
        
        // Filtrar por organização baseado no role
        if ($user->hasRole(['admin', 'admin_paranacidade', 'tecnico_paranacidade'])) {
            // Admin sistema e Paranacidade veem todas
            // Não aplicar filtro
        } elseif ($user->hasRole(['admin_secretaria', 'tecnico_secretaria'])) {
            // Usuários de secretaria veem apenas da sua organização
            $query->whereHas('termoAdesao', function($q) use ($user) {
                $q->where('organizacao_id', $user->organizacao_id);
            });
        } else {
            // Outros usuários não veem nada
            $query->whereRaw('1 = 0');
        }
        
        $demandas = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('demandas.index', compact('demandas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!$this->canCreate()) {
            abort(403, 'Você não tem permissão para criar demandas.');
        }

        $user = auth()->user();
        
        // Filtrar termos de adesão baseado na organização
        $termosQuery = TermoAdesao::with('organizacao');
        
        if ($user->hasRole(['admin_secretaria', 'tecnico_secretaria'])) {
            $termosQuery->where('organizacao_id', $user->organizacao_id);
        }
        
        $termosAdesao = $termosQuery->get();
        $cadastrosDemandaGms = CadastroDemandaGms::all();
        
        return view('demandas.create', compact('termosAdesao', 'cadastrosDemandaGms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$this->canCreate()) {
            abort(403, 'Você não tem permissão para criar demandas.');
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'justificativa' => 'nullable|string',
            'valor_estimado' => 'nullable|numeric|min:0',
            'localizacao' => 'nullable|string|max:500',
            'beneficiarios_estimados' => 'nullable|integer|min:0',
            'termo_adesao_id' => 'required|exists:termo_adesao,id',
            'cadastro_demanda_gms_id' => 'nullable|exists:cadastro_demanda_gms,id',
        ]);

        // Verificar se o usuário pode criar demanda para este termo
        $termo = TermoAdesao::findOrFail($request->termo_adesao_id);
        $user = auth()->user();
        
        if ($user->hasRole(['admin_secretaria', 'tecnico_secretaria'])) {
            if ($termo->organizacao_id != $user->organizacao_id) {
                abort(403, 'Você só pode criar demandas para sua organização.');
            }
        }

        $data = $request->all();
        $data['created_by'] = auth()->id();
        
        Demanda::create($data);
        
        return redirect()->route('demandas.index')->with('success', 'Demanda criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Demanda $demanda)
    {
        $demanda->load(['termoAdesao.organizacao', 'cadastroDemandaGms']);
        
        // Verificar acesso organizacional
        if (!$this->canAccessOrganizacao($demanda->termoAdesao->organizacao_id)) {
            abort(403, 'Você não tem permissão para visualizar esta demanda.');
        }
        
        return view('demandas.show', compact('demanda'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Demanda $demanda)
    {
        if (!$this->canEdit()) {
            abort(403, 'Você não tem permissão para editar demandas.');
        }

        $demanda->load(['termoAdesao.organizacao']);
        
        // Verificar acesso organizacional
        if (!$this->canAccessOrganizacao($demanda->termoAdesao->organizacao_id)) {
            abort(403, 'Você não tem permissão para editar esta demanda.');
        }

        $user = auth()->user();
        
        // Filtrar termos de adesão baseado na organização
        $termosQuery = TermoAdesao::with('organizacao');
        
        if ($user->hasRole(['admin_secretaria', 'tecnico_secretaria'])) {
            $termosQuery->where('organizacao_id', $user->organizacao_id);
        }
        
        $termosAdesao = $termosQuery->get();
        $cadastrosDemandaGms = CadastroDemandaGms::all();
        
        return view('demandas.edit', compact('demanda', 'termosAdesao', 'cadastrosDemandaGms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Demanda $demanda)
    {
        if (!$this->canEdit()) {
            abort(403, 'Você não tem permissão para editar demandas.');
        }

        $demanda->load(['termoAdesao']);
        
        // Verificar acesso organizacional
        if (!$this->canAccessOrganizacao($demanda->termoAdesao->organizacao_id)) {
            abort(403, 'Você não tem permissão para editar esta demanda.');
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'justificativa' => 'nullable|string',
            'valor_estimado' => 'nullable|numeric|min:0',
            'localizacao' => 'nullable|string|max:500',
            'beneficiarios_estimados' => 'nullable|integer|min:0',
            'termo_adesao_id' => 'required|exists:termo_adesao,id',
            'cadastro_demanda_gms_id' => 'nullable|exists:cadastro_demanda_gms,id',
        ]);

        $data = $request->all();
        $data['updated_by'] = auth()->id();
        
        $demanda->update($data);
        
        return redirect()->route('demandas.index')->with('success', 'Demanda atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Demanda $demanda)
    {
        if (!$this->canDelete()) {
            abort(403, 'Você não tem permissão para excluir demandas.');
        }

        $demanda->load(['termoAdesao']);
        
        // Verificar acesso organizacional
        if (!$this->canAccessOrganizacao($demanda->termoAdesao->organizacao_id)) {
            abort(403, 'Você não tem permissão para excluir esta demanda.');
        }

        $demanda->delete();
        
        return redirect()->route('demandas.index')->with('success', 'Demanda removida com sucesso!');
    }
}
