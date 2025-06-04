<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demanda;
use App\Models\TermoAdesao;
use App\Models\CadastroDemandaGms;

class DemandaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $demandas = Demanda::with(['termoAdesao', 'cadastroDemandaGms'])->paginate(10);
        return view('demandas.index', compact('demandas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $termosAdesao = TermoAdesao::all();
        $cadastrosDemandaGms = CadastroDemandaGms::all();
        return view('demandas.create', compact('termosAdesao', 'cadastrosDemandaGms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'prioridade_sam' => 'required|string|max:45',
            'termo_adesao_id' => 'required|exists:termos_adesao,id',
            'cadastro_demanda_gms_id' => 'required|exists:cadastro_demanda_gms,id',
        ]);

        Demanda::create($request->all());
        return redirect()->route('demandas.index')->with('success', 'Demanda criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Demanda $demanda)
    {
        $demanda->load(['termoAdesao', 'cadastroDemandaGms']);
        return view('demandas.show', compact('demanda'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Demanda $demanda)
    {
        $termosAdesao = TermoAdesao::all();
        $cadastrosDemandaGms = CadastroDemandaGms::all();
        return view('demandas.edit', compact('demanda', 'termosAdesao', 'cadastrosDemandaGms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Demanda $demanda)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'prioridade_sam' => 'required|string|max:45',
            'termo_adesao_id' => 'required|exists:termos_adesao,id',
            'cadastro_demanda_gms_id' => 'required|exists:cadastro_demanda_gms,id',
        ]);

        $demanda->update($request->all());
        return redirect()->route('demandas.index')->with('success', 'Demanda atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Demanda $demanda)
    {
        $demanda->delete();
        return redirect()->route('demandas.index')->with('success', 'Demanda removida com sucesso!');
    }
}
