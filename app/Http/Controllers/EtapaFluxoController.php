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
    public function create(Request $request)
    {
        $tiposFluxo = TipoFluxo::where('is_ativo', true)->orderBy('nome')->get();
        $modulos = Modulo::where('is_ativo', true)->orderBy('nome')->get();
        $gruposExigencia = GrupoExigencia::where('is_ativo', true)->orderBy('nome')->get();
        $organizacoes = Organizacao::where('is_ativo', true)->orderBy('nome')->get();
        
        // Pegar o tipo_fluxo_id da URL se fornecido
        $tipoFluxoPreSelecionado = $request->get('tipo_fluxo_id');
        
        return view('etapas_fluxo.create', compact('tiposFluxo', 'modulos', 'gruposExigencia', 'organizacoes', 'tipoFluxoPreSelecionado'));
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
            'descricao_customizada' => 'nullable|string',
            'grupo_exigencia_id' => 'nullable|exists:grupo_exigencia,id',
        ]);
        
        $data = $request->all();
        
        // Gerar ordem_execucao automaticamente
        $proximaOrdem = EtapaFluxo::where('tipo_fluxo_id', $data['tipo_fluxo_id'])
            ->max('ordem_execucao');
        $data['ordem_execucao'] = ($proximaOrdem ?? 0) + 1;
        
        // Definir valores padrão para campos removidos da interface
        $data['is_obrigatoria'] = true; // Padrão: etapa obrigatória
        $data['permite_pular'] = false; // Padrão: não permite pular
        $data['permite_retorno'] = true; // Padrão: permite retorno
        $data['tipo_etapa'] = 'CONDICIONAL'; // Padrão: fluxo condicional
        
        $etapaFluxo = EtapaFluxo::create($data);
        
        // Armazenar o tipo_fluxo_id na sessão para usar nos redirects subsequentes
        session(['tipo_fluxo_origem' => $data['tipo_fluxo_id']]);
        
        return redirect()->route('etapas-fluxo.edit', $etapaFluxo)
            ->with('success', 'Etapa criada com sucesso! Configure agora os status e transições.');
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
            'descricao_customizada' => 'nullable|string',
            'grupo_exigencia_id' => 'nullable|exists:grupo_exigencia,id',
        ]);
        
        $data = $request->all();
        
        // Se mudou o tipo de fluxo, recalcular ordem_execucao
        if ($data['tipo_fluxo_id'] != $etapa_fluxo->tipo_fluxo_id) {
            $proximaOrdem = EtapaFluxo::where('tipo_fluxo_id', $data['tipo_fluxo_id'])
                ->where('id', '!=', $etapa_fluxo->id)
                ->max('ordem_execucao');
            $data['ordem_execucao'] = ($proximaOrdem ?? 0) + 1;
        }
        // Se não mudou tipo_fluxo, manter ordem_execucao existente
        else {
            unset($data['ordem_execucao']); // Não atualizar
        }
        
        $etapa_fluxo->update($data);
        
        // Verificar se há um tipo de fluxo de origem na sessão para redirecionar corretamente
        $tipoFluxoOrigem = session('tipo_fluxo_origem');
        if ($tipoFluxoOrigem) {
            // Limpar a sessão
            session()->forget('tipo_fluxo_origem');
            
            return redirect()->route('tipos-fluxo.etapas', $tipoFluxoOrigem)
                ->with('success', 'Etapa criada e configurada com sucesso!');
        }
        
        return redirect()->route('etapas-fluxo.index')->with('success', 'Etapa atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EtapaFluxo $etapa_fluxo)
    {
        // Adicionar verificação de dependências se necessário
        // Ex: if ($etapa_fluxo->execucoes()->count() > 0) { ... }

        $tipoFluxoId = $etapa_fluxo->tipo_fluxo_id;
        $ordemExcluida = $etapa_fluxo->ordem_execucao;
        
        $etapa_fluxo->delete();
        
        // Reordenar as etapas restantes do mesmo tipo de fluxo
        EtapaFluxo::where('tipo_fluxo_id', $tipoFluxoId)
            ->where('ordem_execucao', '>', $ordemExcluida)
            ->decrement('ordem_execucao');

        return redirect()->route('etapas-fluxo.index')
            ->with('success', 'Etapa de fluxo excluída com sucesso! As demais etapas foram reordenadas automaticamente.');
    }

    /**
     * Reordena automaticamente todas as etapas de um tipo de fluxo
     */
    public function reordenarEtapas($tipoFluxoId)
    {
        $etapas = EtapaFluxo::where('tipo_fluxo_id', $tipoFluxoId)
            ->orderBy('ordem_execucao')
            ->get();
            
        $ordem = 1;
        foreach ($etapas as $etapa) {
            $etapa->update(['ordem_execucao' => $ordem]);
            $ordem++;
        }
        
        return response()->json(['success' => true, 'message' => 'Etapas reordenadas com sucesso!']);
    }
}
