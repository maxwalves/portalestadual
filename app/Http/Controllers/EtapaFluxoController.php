<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EtapaFluxo;
use App\Models\TipoFluxo;
use App\Models\Modulo;
use App\Models\GrupoExigencia;
use App\Models\Organizacao;

class EtapaFluxoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $etapas = EtapaFluxo::with(['tipoFluxo', 'modulo', 'grupoExigencia', 'organizacaoSolicitante', 'organizacaoExecutora'])
            ->orderBy('tipo_fluxo_id')
            ->orderBy('ordem_execucao')
            ->paginate(15);
        return view('etapas_fluxo.index', compact('etapas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tiposFluxo = TipoFluxo::where('is_ativo', true)->orderBy('nome')->get();
        $modulos = Modulo::where('is_ativo', true)->orderBy('nome')->get();
        $gruposExigencia = GrupoExigencia::where('is_ativo', true)->orderBy('nome')->get();
        $organizacoes = Organizacao::where('is_ativo', true)->orderBy('nome')->get();
        
        return view('etapas_fluxo.create', compact('tiposFluxo', 'modulos', 'gruposExigencia', 'organizacoes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_fluxo_id' => 'required|exists:tipo_fluxo,id',
            'nome_etapa' => 'required|string|max:255',
            'modulo_id' => 'required|exists:modulo,id',
            'organizacao_solicitante_id' => 'required|exists:organizacao,id',
            'organizacao_executora_id' => 'required|exists:organizacao,id',
            'prazo_dias' => 'required|integer|min:1',
            'tipo_prazo' => 'required|in:UTEIS,CORRIDOS',
            'tipo_etapa' => 'required|in:SEQUENCIAL,CONDICIONAL',
            'ordem_execucao' => 'nullable|integer|min:1',
        ]);
        
        $data = $request->all();
        $data['is_obrigatoria'] = $request->has('is_obrigatoria');
        $data['permite_pular'] = $request->has('permite_pular');
        $data['permite_retorno'] = $request->has('permite_retorno');
        
        EtapaFluxo::create($data);
        
        return redirect()->route('etapas-fluxo.index')->with('success', 'Etapa criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $etapaFluxo = EtapaFluxo::with(['tipoFluxo', 'modulo', 'grupoExigencia', 'organizacaoSolicitante', 'organizacaoExecutora'])
            ->findOrFail($id);
        return view('etapas_fluxo.show', compact('etapaFluxo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EtapaFluxo $etapa_fluxo)
    {
        $tiposFluxo = TipoFluxo::where('is_ativo', true)->orderBy('nome')->get();
        $modulos = Modulo::where('is_ativo', true)->orderBy('nome')->get();
        $gruposExigencia = GrupoExigencia::where('is_ativo', true)->orderBy('nome')->get();
        $organizacoes = Organizacao::where('is_ativo', true)->orderBy('nome')->get();
        
        // Carregar dados para configuração de status e transições
        $statusDisponiveis = \App\Models\Status::where('is_ativo', true)
            ->orderBy('ordem')
            ->get();
            
        $etapasDisponiveis = EtapaFluxo::where('tipo_fluxo_id', $etapa_fluxo->tipo_fluxo_id)
            ->orderBy('ordem_execucao')
            ->get();
            
        // Carregar opções de status existentes
        $etapa_fluxo->load(['etapaStatusOpcoes.status', 'transicoesOrigem.statusCondicao', 'transicoesOrigem.etapaDestino']);
        
        return view('etapas_fluxo.edit', [
            'etapaFluxo' => $etapa_fluxo,
            'tiposFluxo' => $tiposFluxo,
            'modulos' => $modulos,
            'gruposExigencia' => $gruposExigencia,
            'organizacoes' => $organizacoes,
            'statusDisponiveis' => $statusDisponiveis,
            'etapasDisponiveis' => $etapasDisponiveis,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EtapaFluxo $etapa_fluxo)
    {
        $request->validate([
            'tipo_fluxo_id' => 'required|exists:tipo_fluxo,id',
            'nome_etapa' => 'required|string|max:255',
            'modulo_id' => 'required|exists:modulo,id',
            'organizacao_solicitante_id' => 'required|exists:organizacao,id',
            'organizacao_executora_id' => 'required|exists:organizacao,id',
            'prazo_dias' => 'required|integer|min:1',
            'tipo_prazo' => 'required|in:UTEIS,CORRIDOS',
            'tipo_etapa' => 'required|in:SEQUENCIAL,CONDICIONAL',
            'ordem_execucao' => 'nullable|integer|min:1',
        ]);
        
        $data = $request->all();
        $data['is_obrigatoria'] = $request->has('is_obrigatoria');
        $data['permite_pular'] = $request->has('permite_pular');
        $data['permite_retorno'] = $request->has('permite_retorno');
        
        $etapa_fluxo->update($data);
        
        return redirect()->route('etapas-fluxo.index')->with('success', 'Etapa atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EtapaFluxo $etapa_fluxo)
    {
        // Adicionar verificação de dependências se necessário
        // Ex: if ($etapa_fluxo->execucoes()->count() > 0) { ... }

        $etapa_fluxo->delete();

        return redirect()->route('etapas-fluxo.index')
            ->with('success', 'Etapa de fluxo excluída com sucesso!');
    }
}
