<?php

namespace App\Http\Controllers;

use App\Models\TemplateDocumento;
use App\Models\TipoDocumento;
use App\Models\GrupoExigencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemplateDocumentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TemplateDocumento::with(['tipoDocumento', 'grupoExigencia']);

        // Filtros
        if ($request->filled('tipo_documento_id')) {
            $query->where('tipo_documento_id', $request->tipo_documento_id);
        }

        if ($request->filled('grupo_exigencia_id')) {
            $query->where('grupo_exigencia_id', $request->grupo_exigencia_id);
        }

        if ($request->filled('is_obrigatorio')) {
            $query->where('is_obrigatorio', $request->is_obrigatorio);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderBy('grupo_exigencia_id')
                          ->orderBy('ordem')
                          ->orderBy('nome')
                          ->paginate(15);

        // Para filtros
        $tiposDocumento = TipoDocumento::ativos()->orderBy('nome')->get();
        $gruposExigencia = GrupoExigencia::ativos()->orderBy('nome')->get();

        return view('template_documentos.index', compact('templates', 'tiposDocumento', 'gruposExigencia'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tiposDocumento = TipoDocumento::ativos()->orderBy('nome')->get();
        $gruposExigencia = GrupoExigencia::ativos()->orderBy('nome')->get();

        return view('template_documentos.create', compact('tiposDocumento', 'gruposExigencia'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'grupo_exigencia_id' => 'required|exists:grupo_exigencia,id',
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'is_obrigatorio' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
            'instrucoes_preenchimento' => 'nullable|string',
            'validacoes_customizadas' => 'nullable|json',
            'arquivo_modelo' => 'nullable|file|max:51200', // 50MB
            'arquivo_exemplo' => 'nullable|file|max:51200', // 50MB
        ]);

        // Determinar ordem se não fornecida
        if (!isset($validated['ordem'])) {
            $validated['ordem'] = TemplateDocumento::where('grupo_exigencia_id', $validated['grupo_exigencia_id'])
                                                  ->max('ordem') + 1;
        }

        // Upload dos arquivos
        if ($request->hasFile('arquivo_modelo')) {
            $validated['caminho_modelo_storage'] = $request->file('arquivo_modelo')
                ->store('templates/modelos', 'public');
        }

        if ($request->hasFile('arquivo_exemplo')) {
            $validated['exemplo_preenchido'] = $request->file('arquivo_exemplo')
                ->store('templates/exemplos', 'public');
        }

        $validated['created_by'] = auth()->id();

        TemplateDocumento::create($validated);

        return redirect()->route('template-documentos.index')
                       ->with('success', 'Template de documento criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(TemplateDocumento $templateDocumento)
    {
        $templateDocumento->load(['tipoDocumento', 'grupoExigencia']);
        
        return view('template_documentos.show', compact('templateDocumento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TemplateDocumento $templateDocumento)
    {
        $tiposDocumento = TipoDocumento::ativos()->orderBy('nome')->get();
        $gruposExigencia = GrupoExigencia::ativos()->orderBy('nome')->get();

        return view('template_documentos.edit', compact('templateDocumento', 'tiposDocumento', 'gruposExigencia'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TemplateDocumento $templateDocumento)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'grupo_exigencia_id' => 'required|exists:grupo_exigencia,id',
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'is_obrigatorio' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
            'instrucoes_preenchimento' => 'nullable|string',
            'validacoes_customizadas' => 'nullable|json',
            'arquivo_modelo' => 'nullable|file|max:51200',
            'arquivo_exemplo' => 'nullable|file|max:51200',
        ]);

        // Upload dos novos arquivos se fornecidos
        if ($request->hasFile('arquivo_modelo')) {
            // Remover arquivo antigo
            if ($templateDocumento->caminho_modelo_storage && Storage::disk('public')->exists($templateDocumento->caminho_modelo_storage)) {
                Storage::disk('public')->delete($templateDocumento->caminho_modelo_storage);
            }
            
            $validated['caminho_modelo_storage'] = $request->file('arquivo_modelo')
                ->store('templates/modelos', 'public');
        }

        if ($request->hasFile('arquivo_exemplo')) {
            // Remover arquivo antigo
            if ($templateDocumento->exemplo_preenchido && Storage::disk('public')->exists($templateDocumento->exemplo_preenchido)) {
                Storage::disk('public')->delete($templateDocumento->exemplo_preenchido);
            }
            
            $validated['exemplo_preenchido'] = $request->file('arquivo_exemplo')
                ->store('templates/exemplos', 'public');
        }

        $validated['updated_by'] = auth()->id();

        $templateDocumento->update($validated);

        return redirect()->route('template-documentos.index')
                       ->with('success', 'Template de documento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TemplateDocumento $templateDocumento)
    {
        // Remover arquivos associados
        if ($templateDocumento->caminho_modelo_storage && Storage::disk('public')->exists($templateDocumento->caminho_modelo_storage)) {
            Storage::disk('public')->delete($templateDocumento->caminho_modelo_storage);
        }

        if ($templateDocumento->exemplo_preenchido && Storage::disk('public')->exists($templateDocumento->exemplo_preenchido)) {
            Storage::disk('public')->delete($templateDocumento->exemplo_preenchido);
        }

        $templateDocumento->delete();

        return redirect()->route('template-documentos.index')
                       ->with('success', 'Template de documento excluído com sucesso!');
    }

    /**
     * Download do arquivo modelo
     */
    public function downloadModelo(TemplateDocumento $templateDocumento)
    {
        if (!$templateDocumento->caminho_modelo_storage || !Storage::disk('public')->exists($templateDocumento->caminho_modelo_storage)) {
            abort(404, 'Arquivo modelo não encontrado.');
        }

        return Storage::disk('public')->download(
            $templateDocumento->caminho_modelo_storage,
            'Modelo_' . $templateDocumento->nome . '.' . pathinfo($templateDocumento->caminho_modelo_storage, PATHINFO_EXTENSION)
        );
    }

    /**
     * Download do arquivo exemplo
     */
    public function downloadExemplo(TemplateDocumento $templateDocumento)
    {
        if (!$templateDocumento->exemplo_preenchido || !Storage::disk('public')->exists($templateDocumento->exemplo_preenchido)) {
            abort(404, 'Arquivo exemplo não encontrado.');
        }

        return Storage::disk('public')->download(
            $templateDocumento->exemplo_preenchido,
            'Exemplo_' . $templateDocumento->nome . '.' . pathinfo($templateDocumento->exemplo_preenchido, PATHINFO_EXTENSION)
        );
    }

    /**
     * Reordenar templates
     */
    public function reordenar(Request $request)
    {
        $validated = $request->validate([
            'templates' => 'required|array',
            'templates.*' => 'required|integer|exists:template_documentos,id',
        ]);

        foreach ($validated['templates'] as $ordem => $templateId) {
            TemplateDocumento::where('id', $templateId)
                           ->update(['ordem' => $ordem + 1]);
        }

        return response()->json(['success' => true]);
    }
} 