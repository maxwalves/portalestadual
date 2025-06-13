<?php

namespace App\Http\Controllers;

use App\Models\TipoFluxo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TipoFluxoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tipoFluxos = TipoFluxo::orderBy('nome')->paginate(15);
        
        return view('tipos_fluxo.index', compact('tipoFluxos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tipos_fluxo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:tipo_fluxo,nome',
            'descricao' => 'nullable|string',
            'categoria' => 'nullable|string|max:100',
            'versao' => 'required|string|max:20',
            'is_ativo' => 'boolean',
        ]);

        $data = $request->all();
        $data['is_ativo'] = $request->has('ativo');
        $data['created_by'] = Auth::id();

        TipoFluxo::create($data);

        return redirect()->route('tipos-fluxo.index')
            ->with('success', 'Tipo de fluxo criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoFluxo $tipoFluxo)
    {
        $tipoFluxo->load(['acoes', 'etapasFluxo']);
        
        return view('tipos_fluxo.show', compact('tipoFluxo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoFluxo $tipoFluxo)
    {
        return view('tipos_fluxo.edit', compact('tipoFluxo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoFluxo $tipoFluxo)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:tipo_fluxo,nome,' . $tipoFluxo->id,
            'descricao' => 'nullable|string',
            'categoria' => 'nullable|string|max:100',
            'versao' => 'required|string|max:20',
            'is_ativo' => 'boolean',
        ]);

        $data = $request->all();
        $data['is_ativo'] = $request->has('ativo');
        $data['updated_by'] = Auth::id();

        $tipoFluxo->update($data);

        return redirect()->route('tipos-fluxo.index')
            ->with('success', 'Tipo de fluxo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoFluxo $tipoFluxo)
    {
        // Verificar se há ações associadas
        if ($tipoFluxo->acoes()->count() > 0) {
            return redirect()->route('tipos-fluxo.index')
                ->with('error', 'Não é possível excluir este tipo de fluxo pois existem ações associadas a ele.');
        }

        $tipoFluxo->delete();

        return redirect()->route('tipos-fluxo.index')
            ->with('success', 'Tipo de fluxo excluído com sucesso!');
    }

    /**
     * Exibe as etapas vinculadas a um tipo de fluxo específico.
     */
    public function etapas(TipoFluxo $tipo_fluxo)
    {
        $tipo_fluxo->load(['etapasFluxo.modulo', 'etapasFluxo.grupoExigencia']);
        return view('tipos_fluxo.etapas', compact('tipo_fluxo'));
    }
} 