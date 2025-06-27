<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GrupoExigencia;
use App\Models\TemplateDocumento;
use App\Models\EtapaFluxo;
use Illuminate\Support\Facades\DB;

class GrupoExigenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = GrupoExigencia::query();

        // Busca por nome ou descrição
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        // Filtro por status ativo/inativo
        if ($request->filled('status')) {
            $query->where('is_ativo', $request->status === 'ativo');
        }

        $gruposExigencia = $query->withCount(['templatesDocumento', 'etapasFluxo'])
                                ->orderBy('nome')
                                ->paginate(15);

        return view('grupo_exigencias.index', compact('gruposExigencia'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('grupo_exigencias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:grupo_exigencia,nome',
            'descricao' => 'nullable|string|max:1000',
            'is_ativo' => 'boolean',
        ], [
            'nome.required' => 'O nome do grupo é obrigatório.',
            'nome.unique' => 'Já existe um grupo de exigência com este nome.',
            'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
            'descricao.max' => 'A descrição não pode ter mais de 1000 caracteres.',
        ]);

        $validated['is_ativo'] = $validated['is_ativo'] ?? true;

        GrupoExigencia::create($validated);

        return redirect()->route('grupo-exigencias.index')
                       ->with('success', 'Grupo de exigência criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(GrupoExigencia $grupoExigencia)
    {
        $grupoExigencia->loadCount(['templatesDocumento', 'etapasFluxo']);
        
        // Carregar templates associados com informações completas
        $templates = $grupoExigencia->templatesDocumento()
                                   ->with(['tipoDocumento'])
                                   ->orderBy('ordem')
                                   ->orderBy('nome')
                                   ->get();
        
        // Carregar etapas de fluxo que usam este grupo
        $etapasFluxo = $grupoExigencia->etapasFluxo()
                                     ->with(['tipoFluxo', 'organizacaoSolicitante', 'organizacaoExecutora'])
                                     ->orderBy('tipo_fluxo_id')
                                     ->orderBy('ordem_execucao')
                                     ->get();

        // Estatísticas
        $estatisticas = [
            'total_templates' => $templates->count(),
            'templates_obrigatorios' => $templates->where('is_obrigatorio', true)->count(),
            'templates_opcionais' => $templates->where('is_obrigatorio', false)->count(),
            'total_etapas' => $etapasFluxo->count(),
            'tipos_documento_diferentes' => $templates->pluck('tipo_documento_id')->unique()->count(),
        ];

        return view('grupo_exigencias.show', compact('grupoExigencia', 'templates', 'etapasFluxo', 'estatisticas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GrupoExigencia $grupoExigencia)
    {
        $grupoExigencia->loadCount(['templatesDocumento', 'etapasFluxo']);
        $grupoExigencia->load(['templatesDocumento.tipoDocumento']);
        
        return view('grupo_exigencias.edit', compact('grupoExigencia'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GrupoExigencia $grupoExigencia)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:grupo_exigencia,nome,' . $grupoExigencia->id,
            'descricao' => 'nullable|string|max:1000',
            'is_ativo' => 'boolean',
        ], [
            'nome.required' => 'O nome do grupo é obrigatório.',
            'nome.unique' => 'Já existe um grupo de exigência com este nome.',
            'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
            'descricao.max' => 'A descrição não pode ter mais de 1000 caracteres.',
        ]);

        $validated['is_ativo'] = $validated['is_ativo'] ?? false;

        $grupoExigencia->update($validated);

        return redirect()->route('grupo-exigencias.index')
                       ->with('success', 'Grupo de exigência atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GrupoExigencia $grupoExigencia)
    {
        try {
            // Verificar se há dependências que impedem a exclusão
            $templatesCount = $grupoExigencia->templatesDocumento()->count();
            $etapasCount = $grupoExigencia->etapasFluxo()->count();

            if ($templatesCount > 0 || $etapasCount > 0) {
                return redirect()->route('grupo-exigencias.index')
                               ->with('error', "Não é possível excluir este grupo. Existem {$templatesCount} template(s) de documento e {$etapasCount} etapa(s) de fluxo associados.");
            }

            $grupoExigencia->delete();

            return redirect()->route('grupo-exigencias.index')
                           ->with('success', 'Grupo de exigência excluído com sucesso!');

        } catch (\Exception $e) {
            return redirect()->route('grupo-exigencias.index')
                           ->with('error', 'Erro ao excluir o grupo de exigência: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of the resource.
     */
    public function toggleAtivo(GrupoExigencia $grupoExigencia)
    {
        $grupoExigencia->update([
            'is_ativo' => !$grupoExigencia->is_ativo
        ]);

        $status = $grupoExigencia->is_ativo ? 'ativado' : 'desativado';

        return redirect()->back()
                       ->with('success', "Grupo de exigência {$status} com sucesso!");
    }

    /**
     * API para listar grupos ativos (usado em selects)
     */
    public function apiGruposAtivos()
    {
        $grupos = GrupoExigencia::ativos()
                               ->orderBy('nome')
                               ->get(['id', 'nome', 'descricao']);

        return response()->json($grupos);
    }

    /**
     * API para obter estatísticas de um grupo
     */
    public function apiEstatisticas(GrupoExigencia $grupoExigencia)
    {
        $estatisticas = [
            'templates_total' => $grupoExigencia->templatesDocumento()->count(),
            'templates_obrigatorios' => $grupoExigencia->templatesDocumento()->wherePivot('is_obrigatorio', true)->count(),
            'templates_opcionais' => $grupoExigencia->templatesDocumento()->wherePivot('is_obrigatorio', false)->count(),
            'etapas_fluxo' => $grupoExigencia->etapasFluxo()->count(),
            'tipos_documento' => $grupoExigencia->templatesDocumento()
                                               ->distinct('template_documentos.tipo_documento_id')
                                               ->count(),
        ];

        return response()->json($estatisticas);
    }

    /**
     * Duplicar um grupo de exigência com todos os seus templates
     */
    public function duplicar(Request $request, GrupoExigencia $grupoExigencia)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:grupo_exigencia,nome',
            'descricao' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($grupoExigencia, $validated) {
                // Criar novo grupo
                $novoGrupo = GrupoExigencia::create([
                    'nome' => $validated['nome'],
                    'descricao' => $validated['descricao'] ?? $grupoExigencia->descricao,
                    'is_ativo' => true,
                ]);

                // Duplicar todos os templates
                $templates = $grupoExigencia->templatesDocumento()->get();
                foreach ($templates as $template) {
                    TemplateDocumento::create([
                        'grupo_exigencia_id' => $novoGrupo->id,
                        'tipo_documento_id' => $template->tipo_documento_id,
                        'nome' => $template->nome,
                        'descricao' => $template->descricao,
                        'is_obrigatorio' => $template->is_obrigatorio,
                        'ordem' => $template->ordem,
                        'instrucoes_preenchimento' => $template->instrucoes_preenchimento,
                        'validacoes_customizadas' => $template->validacoes_customizadas,
                        'created_by' => auth()->id(),
                    ]);
                }
            });

            return redirect()->route('grupo-exigencias.index')
                           ->with('success', 'Grupo de exigência duplicado com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Erro ao duplicar o grupo: ' . $e->getMessage());
        }
    }

    /**
     * Página para gerenciar templates do grupo
     */
    public function gerenciarTemplates(GrupoExigencia $grupoExigencia)
    {
        $grupoExigencia->load(['templatesDocumento.tipoDocumento']);
        
        // Templates já vinculados ao grupo
        $templatesVinculados = $grupoExigencia->templatesDocumento()
                                            ->with('tipoDocumento')
                                            ->orderBy('grupo_exigencia_template_documento.ordem')
                                            ->get();
        
        // Templates disponíveis para vincular (não vinculados a este grupo)
        $templatesDisponiveis = TemplateDocumento::with('tipoDocumento')
                                                ->whereNotIn('id', $templatesVinculados->pluck('id'))
                                                ->orderBy('nome')
                                                ->get();

        return view('grupo_exigencias.templates', compact('grupoExigencia', 'templatesVinculados', 'templatesDisponiveis'));
    }

    /**
     * Vincular um template ao grupo
     */
    public function vincularTemplate(Request $request, GrupoExigencia $grupoExigencia)
    {
        $validated = $request->validate([
            'template_documento_id' => 'required|exists:template_documentos,id',
            'is_obrigatorio' => 'boolean',
            'ordem' => 'integer|min:0',
            'observacoes' => 'nullable|string|max:1000',
        ]);

        // Verificar se já está vinculado
        if ($grupoExigencia->templatesDocumento()->where('template_documento_id', $validated['template_documento_id'])->exists()) {
            return redirect()->back()
                           ->with('error', 'Este template já está vinculado ao grupo.');
        }

        // Definir ordem automaticamente se não informada
        if (!isset($validated['ordem'])) {
            $ultimaOrdem = $grupoExigencia->templatesDocumento()->max('grupo_exigencia_template_documento.ordem') ?? 0;
            $validated['ordem'] = $ultimaOrdem + 1;
        }

        $validated['is_obrigatorio'] = $validated['is_obrigatorio'] ?? true;

        $grupoExigencia->templatesDocumento()->attach($validated['template_documento_id'], [
            'is_obrigatorio' => $validated['is_obrigatorio'],
            'ordem' => $validated['ordem'],
            'observacoes' => $validated['observacoes'] ?? null,
        ]);

        $template = TemplateDocumento::find($validated['template_documento_id']);

        return redirect()->back()
                       ->with('success', "Template '{$template->nome}' vinculado com sucesso!");
    }

    /**
     * Desvincular um template do grupo
     */
    public function desvincularTemplate(GrupoExigencia $grupoExigencia, TemplateDocumento $templateDocumento)
    {
        if (!$grupoExigencia->templatesDocumento()->where('template_documento_id', $templateDocumento->id)->exists()) {
            return redirect()->back()
                           ->with('error', 'Este template não está vinculado ao grupo.');
        }

        $grupoExigencia->templatesDocumento()->detach($templateDocumento->id);

        return redirect()->back()
                       ->with('success', "Template '{$templateDocumento->nome}' desvinculado com sucesso!");
    }

    /**
     * Atualizar configurações do vínculo template-grupo
     */
    public function atualizarVinculo(Request $request, GrupoExigencia $grupoExigencia, TemplateDocumento $templateDocumento)
    {
        $validated = $request->validate([
            'is_obrigatorio' => 'boolean',
            'ordem' => 'integer|min:0',
            'observacoes' => 'nullable|string|max:1000',
        ]);

        if (!$grupoExigencia->templatesDocumento()->where('template_documento_id', $templateDocumento->id)->exists()) {
            return redirect()->back()
                           ->with('error', 'Este template não está vinculado ao grupo.');
        }

        $validated['is_obrigatorio'] = $validated['is_obrigatorio'] ?? false;

        $grupoExigencia->templatesDocumento()->updateExistingPivot($templateDocumento->id, $validated);

        return redirect()->back()
                       ->with('success', "Configurações do template '{$templateDocumento->nome}' atualizadas com sucesso!");
    }
} 