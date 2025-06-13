<?php

namespace App\Http\Controllers;

use App\Models\TipoDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TipoDocumentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TipoDocumento::query();

        // Filtros
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('is_ativo')) {
            $query->where('is_ativo', $request->is_ativo);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        $tiposDocumento = $query->orderBy('categoria')
                               ->orderBy('nome')
                               ->paginate(15);

        // Para o select de categorias
        $categorias = TipoDocumento::select('categoria')
                                   ->distinct()
                                   ->whereNotNull('categoria')
                                   ->orderBy('categoria')
                                   ->pluck('categoria');

        return view('tipos_documentos.index', compact('tiposDocumento', 'categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = [
            'PROJETO' => 'Projeto',
            'FINANCEIRO' => 'Financeiro',
            'LICENCA' => 'Licença',
            'JURIDICO' => 'Jurídico',
            'TECNICO' => 'Técnico',
            'ADMINISTRATIVO' => 'Administrativo',
        ];

        return view('tipos_documentos.create', compact('categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:tipo_documentos,codigo',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'extensoes_permitidas' => 'nullable|string|max:255',
            'tamanho_maximo_mb' => 'required|integer|min:1|max:1024',
            'requer_assinatura' => 'boolean',
            'categoria' => 'nullable|string|max:50',
            'is_ativo' => 'boolean',
        ]);

        // Normalizar código
        $validated['codigo'] = Str::upper($validated['codigo']);

        // Normalizar extensões permitidas
        if (!empty($validated['extensoes_permitidas'])) {
            $extensoes = explode(',', $validated['extensoes_permitidas']);
            $extensoes = array_map('trim', $extensoes);
            $extensoes = array_map('strtolower', $extensoes);
            $validated['extensoes_permitidas'] = implode(',', $extensoes);
        }

        $validated['created_by'] = auth()->id();

        TipoDocumento::create($validated);

        return redirect()->route('tipos-documento.index')
                         ->with('success', 'Tipo de documento criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoDocumento $tipoDocumento)
    {
        $tipoDocumento->load(['templatesDocumento.grupoExigencia', 'documentos' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return view('tipos_documentos.show', compact('tipoDocumento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoDocumento $tipoDocumento)
    {
        $categorias = [
            'PROJETO' => 'Projeto',
            'FINANCEIRO' => 'Financeiro',
            'LICENCA' => 'Licença',
            'JURIDICO' => 'Jurídico',
            'TECNICO' => 'Técnico',
            'ADMINISTRATIVO' => 'Administrativo',
        ];

        return view('tipos_documentos.edit', compact('tipoDocumento', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoDocumento $tipoDocumento)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:tipo_documentos,codigo,' . $tipoDocumento->id,
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'extensoes_permitidas' => 'nullable|string|max:255',
            'tamanho_maximo_mb' => 'required|integer|min:1|max:1024',
            'requer_assinatura' => 'boolean',
            'categoria' => 'nullable|string|max:50',
            'is_ativo' => 'boolean',
        ]);

        // Normalizar código
        $validated['codigo'] = Str::upper($validated['codigo']);

        // Normalizar extensões permitidas
        if (!empty($validated['extensoes_permitidas'])) {
            $extensoes = explode(',', $validated['extensoes_permitidas']);
            $extensoes = array_map('trim', $extensoes);
            $extensoes = array_map('strtolower', $extensoes);
            $validated['extensoes_permitidas'] = implode(',', $extensoes);
        }

        $validated['updated_by'] = auth()->id();

        $tipoDocumento->update($validated);

        return redirect()->route('tipos-documento.index')
                         ->with('success', 'Tipo de documento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoDocumento $tipoDocumento)
    {
        // Verificar se existem documentos vinculados
        if ($tipoDocumento->documentos()->count() > 0) {
            return redirect()->route('tipos-documento.index')
                             ->with('error', 'Não é possível excluir este tipo de documento pois existem documentos vinculados a ele.');
        }

        // Verificar se existem templates vinculados
        if ($tipoDocumento->templatesDocumento()->count() > 0) {
            return redirect()->route('tipos-documento.index')
                             ->with('error', 'Não é possível excluir este tipo de documento pois existem templates vinculados a ele.');
        }

        $tipoDocumento->delete();

        return redirect()->route('tipos-documento.index')
                         ->with('success', 'Tipo de documento excluído com sucesso!');
    }

    /**
     * Ativar/desativar tipo de documento
     */
    public function toggleAtivo(TipoDocumento $tipoDocumento)
    {
        $tipoDocumento->update([
            'is_ativo' => !$tipoDocumento->is_ativo,
            'updated_by' => auth()->id(),
        ]);

        $status = $tipoDocumento->is_ativo ? 'ativado' : 'desativado';

        return redirect()->back()
                         ->with('success', "Tipo de documento {$status} com sucesso!");
    }

    /**
     * API para buscar tipos de documento ativos
     */
    public function apiTiposAtivos(Request $request)
    {
        $query = TipoDocumento::ativos();

        if ($request->filled('categoria')) {
            $query->categoria($request->categoria);
        }

        $tipos = $query->select('id', 'codigo', 'nome', 'categoria', 'extensoes_permitidas', 'tamanho_maximo_mb')
                       ->orderBy('categoria')
                       ->orderBy('nome')
                       ->get();

        return response()->json($tipos);
    }

    /**
     * Verificar se arquivo é compatível com tipo de documento
     */
    public function verificarCompatibilidade(Request $request, TipoDocumento $tipoDocumento)
    {
        $validated = $request->validate([
            'nome_arquivo' => 'required|string',
            'tamanho_bytes' => 'required|integer|min:1',
        ]);

        $extensao = strtolower(pathinfo($validated['nome_arquivo'], PATHINFO_EXTENSION));
        
        $compativel = $tipoDocumento->isExtensaoPermitida($extensao) && 
                     $tipoDocumento->isTamanhoPermitido($validated['tamanho_bytes']);

        $detalhes = [
            'compativel' => $compativel,
            'extensao_permitida' => $tipoDocumento->isExtensaoPermitida($extensao),
            'tamanho_permitido' => $tipoDocumento->isTamanhoPermitido($validated['tamanho_bytes']),
            'extensoes_aceitas' => $tipoDocumento->getExtensoesPermitidas(),
            'tamanho_maximo' => $tipoDocumento->getTamanhoMaximoFormatado(),
        ];

        return response()->json($detalhes);
    }
} 