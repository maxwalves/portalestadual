<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CadastroDemandaGms;
use Illuminate\Support\Facades\Http;

class CadastroDemandaGmsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cadastros = CadastroDemandaGms::paginate(10);
        return view('cadastros_demanda_gms.index', compact('cadastros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('cadastros_demanda_gms.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'codigoGMS' => 'required|string|max:45',
            'protocolo' => 'required|string|max:45',
        ]);

        CadastroDemandaGms::create($request->all());
        return redirect()->route('cadastros-demanda-gms.index')->with('success', 'Cadastro GMS criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(CadastroDemandaGms $cadastroDemandaGms)
    {
        return view('cadastros_demanda_gms.show', compact('cadastroDemandaGms'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CadastroDemandaGms $cadastroDemandaGms)
    {
        return view('cadastros_demanda_gms.edit', compact('cadastroDemandaGms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CadastroDemandaGms $cadastroDemandaGms)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'codigoGMS' => 'required|string|max:45',
            'protocolo' => 'required|string|max:45',
        ]);

        $cadastroDemandaGms->update($request->all());
        return redirect()->route('cadastros-demanda-gms.index')->with('success', 'Cadastro GMS atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CadastroDemandaGms $cadastroDemandaGms)
    {
        $cadastroDemandaGms->delete();
        return redirect()->route('cadastros-demanda-gms.index')->with('success', 'Cadastro GMS removido com sucesso!');
    }

    /**
     * Sincroniza os dados com o sistema GMS
     */
    public function sync()
    {
        try {
            // TODO: Implementar chamada real à API do GMS
            // $response = Http::get(config('services.gms.api_url') . '/demandas');
            // $demandas = $response->json();

            // Simulação de dados da API
            $demandas = [
                [
                    'descricao' => 'Demanda GMS 1',
                    'codigoGMS' => 'GMS001',
                    'protocolo' => 'PROT001'
                ],
                [
                    'descricao' => 'Demanda GMS 2',
                    'codigoGMS' => 'GMS002',
                    'protocolo' => 'PROT002'
                ],
                [
                    'descricao' => 'Demanda GMS 3',
                    'codigoGMS' => 'GMS003',
                    'protocolo' => 'PROT003'
                ]
            ];

            foreach ($demandas as $demanda) {
                CadastroDemandaGms::updateOrCreate(
                    ['codigoGMS' => $demanda['codigoGMS']],
                    [
                        'descricao' => $demanda['descricao'],
                        'protocolo' => $demanda['protocolo']
                    ]
                );
            }

            return redirect()->route('cadastros-demanda-gms.index')
                ->with('success', 'Sincronização com o GMS realizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('cadastros-demanda-gms.index')
                ->with('error', 'Erro ao sincronizar com o GMS: ' . $e->getMessage());
        }
    }
}
