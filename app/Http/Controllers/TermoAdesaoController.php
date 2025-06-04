<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TermoAdesao;
use App\Models\Organizacao;
use Illuminate\Support\Facades\Storage;

class TermoAdesaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $termos = TermoAdesao::with('organizacao')->paginate(10);
        return view('termos_adesao.index', compact('termos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organizacoes = Organizacao::all();
        return view('termos_adesao.create', compact('organizacoes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'nullable|string|max:255',
            'data_criacao' => 'nullable|date',
            'arquivo' => 'required|file|mimes:pdf,doc,docx|max:10240', // max 10MB
            'organizacao_id' => 'required|exists:organizacao,id',
        ]);

        $data = $request->except('arquivo');
        
        if ($request->hasFile('arquivo')) {
            $arquivo = $request->file('arquivo');
            $path = $arquivo->store('termos_adesao', 'public');
            $data['path_arquivo'] = $path;
        }

        TermoAdesao::create($data);
        return redirect()->route('termos-adesao.index')->with('success', 'Termo de Adesão criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(TermoAdesao $termo)
    {
        return view('termos_adesao.show', compact('termo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TermoAdesao $termo)
    {
        $organizacoes = Organizacao::all();
        return view('termos_adesao.edit', compact('termo', 'organizacoes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TermoAdesao $termo)
    {
        $request->validate([
            'descricao' => 'nullable|string|max:255',
            'data_criacao' => 'nullable|date',
            'arquivo' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // max 10MB
            'organizacao_id' => 'required|exists:organizacao,id',
        ]);

        $data = $request->except('arquivo');
        
        if ($request->hasFile('arquivo')) {
            // Delete old file if exists
            if ($termo->path_arquivo) {
                Storage::disk('public')->delete($termo->path_arquivo);
            }
            
            $arquivo = $request->file('arquivo');
            $path = $arquivo->store('termos_adesao', 'public');
            $data['path_arquivo'] = $path;
        }

        $termo->update($data);
        return redirect()->route('termos-adesao.index')->with('success', 'Termo de Adesão atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TermoAdesao $termo)
    {
        // Delete file if exists
        if ($termo->path_arquivo) {
            Storage::disk('public')->delete($termo->path_arquivo);
        }
        
        $termo->delete();
        return redirect()->route('termos-adesao.index')->with('success', 'Termo de Adesão removido com sucesso!');
    }
}
