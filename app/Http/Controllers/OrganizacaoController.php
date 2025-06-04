<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organizacao;

class OrganizacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organizacoes = Organizacao::paginate(10);
        return view('organizacoes.index', compact('organizacoes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('organizacoes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'is_ativo' => 'boolean',
        ]);
        Organizacao::create($request->only('nome', 'is_ativo'));
        return redirect()->route('organizacoes.index')->with('success', 'Organização criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Organizacao $organizacao)
    {
        $organizacao->load(['termosAdesao', 'users']);
        return view('organizacoes.show', compact('organizacao'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organizacao $organizacao)
    {
        return view('organizacoes.edit', compact('organizacao'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organizacao $organizacao)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'is_ativo' => 'boolean',
        ]);
        $organizacao->update($request->only('nome', 'is_ativo'));
        return redirect()->route('organizacoes.index')->with('success', 'Organização atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organizacao $organizacao)
    {
        try {
            // Verificar se existem relacionamentos que impedem a exclusão
            $hasUsers = $organizacao->users()->count() > 0;
            $hasTermosAdesao = $organizacao->termosAdesao()->count() > 0;
            
            if ($hasUsers) {
                return redirect()->route('organizacoes.index')
                    ->with('error', 'Não é possível excluir esta organização pois existem usuários associados a ela.');
            }
            
            if ($hasTermosAdesao) {
                return redirect()->route('organizacoes.index')
                    ->with('error', 'Não é possível excluir esta organização pois existem termos de adesão associados a ela.');
            }
            
            $organizacao->delete();
            return redirect()->route('organizacoes.index')->with('success', 'Organização removida com sucesso!');
            
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir organização: ' . $e->getMessage());
            return redirect()->route('organizacoes.index')
                ->with('error', 'Erro ao excluir organização: ' . $e->getMessage());
        }
    }
}
