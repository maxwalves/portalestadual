<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\TipoDocumento;
use App\Models\ExecucaoEtapa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Documento::with(['tipoDocumento', 'usuarioUpload', 'execucaoEtapa.acao']);

        // Filtros
        if ($request->filled('tipo_documento_id')) {
            $query->where('tipo_documento_id', $request->tipo_documento_id);
        }

        if ($request->filled('status_documento')) {
            $query->where('status_documento', $request->status_documento);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome_arquivo', 'like', "%{$search}%")
                  ->orWhere('observacoes', 'like', "%{$search}%");
            });
        }

        $documentos = $query->orderBy('created_at', 'desc')
                           ->paginate(15);

        // Para filtros
        $tiposDocumento = TipoDocumento::ativos()
                                      ->orderBy('nome')
                                      ->get();

        $statusOptions = [
            'PENDENTE' => 'Pendente',
            'EM_ANALISE' => 'Em Análise', 
            'APROVADO' => 'Aprovado',
            'REPROVADO' => 'Reprovado',
            'EXPIRADO' => 'Expirado',
        ];

        return view('documentos.index', compact('documentos', 'tiposDocumento', 'statusOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tiposDocumento = TipoDocumento::ativos()->orderBy('nome')->get();
        $execucoesEtapa = ExecucaoEtapa::with(['acao', 'etapaFluxo'])
                                      ->orderBy('created_at', 'desc')
                                      ->limit(50)
                                      ->get();

        return view('documentos.create', compact('tiposDocumento', 'execucoesEtapa'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'execucao_etapa_id' => 'required|exists:execucao_etapas,id',
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'arquivo' => 'required|file|max:512000', // 500MB max
            'observacoes' => 'nullable|string',
        ]);

        if ($request->hasFile('arquivo')) {
            $arquivo = $request->file('arquivo');
            $tipoDocumento = TipoDocumento::find($validated['tipo_documento_id']);
            
            // Validar extensão
            $extensao = strtolower($arquivo->getClientOriginalExtension());
            if (!$tipoDocumento->isExtensaoPermitida($extensao)) {
                return back()->withErrors(['arquivo' => 'Extensão de arquivo não permitida para este tipo de documento.']);
            }

            // Validar tamanho
            if (!$tipoDocumento->isTamanhoPermitido($arquivo->getSize())) {
                return back()->withErrors(['arquivo' => 'Arquivo muito grande para este tipo de documento.']);
            }

            // Gerar nome único
            $nomeArquivoSistema = Str::uuid() . '.' . $extensao;
            
            // Fazer upload
            $caminhoStorage = $arquivo->storeAs('documentos', $nomeArquivoSistema, 'public');
            
            // Calcular hash
            $hashArquivo = hash_file('sha256', $arquivo->getRealPath());

            // Criar registro
            Documento::create([
                'execucao_etapa_id' => $validated['execucao_etapa_id'],
                'tipo_documento_id' => $validated['tipo_documento_id'],
                'usuario_upload_id' => auth()->id(),
                'nome_arquivo' => $arquivo->getClientOriginalName(),
                'nome_arquivo_sistema' => $nomeArquivoSistema,
                'tamanho_bytes' => $arquivo->getSize(),
                'mime_type' => $arquivo->getMimeType(),
                'hash_arquivo' => $hashArquivo,
                'caminho_storage' => $caminhoStorage,
                'observacoes' => $validated['observacoes'],
                'status_documento' => 'PENDENTE',
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('documentos.index')
                           ->with('success', 'Documento enviado com sucesso!');
        }

        return back()->withErrors(['arquivo' => 'Erro no upload do arquivo.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Documento $documento)
    {
        $documento->load(['tipoDocumento', 'usuarioUpload', 'execucaoEtapa.acao', 'versoes']);
        
        return view('documentos.show', compact('documento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Documento $documento)
    {
        $tiposDocumento = TipoDocumento::ativos()->orderBy('nome')->get();
        
        return view('documentos.edit', compact('documento', 'tiposDocumento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Documento $documento)
    {
        $validated = $request->validate([
            'observacoes' => 'nullable|string',
            'data_validade' => 'nullable|date|after:today',
        ]);

        $validated['updated_by'] = auth()->id();
        
        $documento->update($validated);

        return redirect()->route('documentos.index')
                       ->with('success', 'Documento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Documento $documento)
    {
        // Remover arquivo físico
        if (Storage::disk('public')->exists($documento->caminho_storage)) {
            Storage::disk('public')->delete($documento->caminho_storage);
        }

        $documento->delete();

        return redirect()->route('documentos.index')
                       ->with('success', 'Documento excluído com sucesso!');
    }

    /**
     * Download do documento
     */
    public function download(Documento $documento)
    {
        if (!Storage::disk('public')->exists($documento->caminho_storage)) {
            abort(404, 'Arquivo não encontrado.');
        }

        return Storage::disk('public')->download(
            $documento->caminho_storage,
            $documento->nome_arquivo
        );
    }

    /**
     * Aprovar documento
     */
    public function aprovar(Request $request, Documento $documento)
    {
        $validated = $request->validate([
            'observacoes' => 'nullable|string',
        ]);

        $documento->update([
            'status_documento' => 'APROVADO',
            'observacoes' => $validated['observacoes'],
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()
                       ->with('success', 'Documento aprovado com sucesso!');
    }

    /**
     * Reprovar documento
     */
    public function reprovar(Request $request, Documento $documento)
    {
        $validated = $request->validate([
            'motivo_reprovacao' => 'required|string',
        ]);

        $documento->update([
            'status_documento' => 'REPROVADO',
            'motivo_reprovacao' => $validated['motivo_reprovacao'],
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()
                       ->with('success', 'Documento reprovado.');
    }

    /**
     * Nova versão do documento
     */
    public function novaVersao(Request $request, Documento $documento)
    {
        $validated = $request->validate([
            'arquivo' => 'required|file|max:512000',
            'observacoes' => 'nullable|string',
        ]);

        if ($request->hasFile('arquivo')) {
            $arquivo = $request->file('arquivo');
            $tipoDocumento = $documento->tipoDocumento;
            
            // Validações similares ao store
            $extensao = strtolower($arquivo->getClientOriginalExtension());
            if (!$tipoDocumento->isExtensaoPermitida($extensao)) {
                return back()->withErrors(['arquivo' => 'Extensão de arquivo não permitida.']);
            }

            if (!$tipoDocumento->isTamanhoPermitido($arquivo->getSize())) {
                return back()->withErrors(['arquivo' => 'Arquivo muito grande.']);
            }

            // Upload
            $nomeArquivoSistema = Str::uuid() . '.' . $extensao;
            $caminhoStorage = $arquivo->storeAs('documentos', $nomeArquivoSistema, 'public');
            $hashArquivo = hash_file('sha256', $arquivo->getRealPath());

            // Criar nova versão
            $novaVersao = max($documento->versao, $documento->versoes()->max('versao') ?? 0) + 1;

            Documento::create([
                'execucao_etapa_id' => $documento->execucao_etapa_id,
                'tipo_documento_id' => $documento->tipo_documento_id,
                'usuario_upload_id' => auth()->id(),
                'nome_arquivo' => $arquivo->getClientOriginalName(),
                'nome_arquivo_sistema' => $nomeArquivoSistema,
                'tamanho_bytes' => $arquivo->getSize(),
                'mime_type' => $arquivo->getMimeType(),
                'hash_arquivo' => $hashArquivo,
                'caminho_storage' => $caminhoStorage,
                'versao' => $novaVersao,
                'documento_pai_id' => $documento->id,
                'observacoes' => $validated['observacoes'],
                'status_documento' => 'PENDENTE',
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('documentos.show', $documento)
                           ->with('success', 'Nova versão do documento enviada com sucesso!');
        }

        return back()->withErrors(['arquivo' => 'Erro no upload do arquivo.']);
    }
} 