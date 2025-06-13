<?php

namespace App\Http\Controllers;

use App\Models\Notificacao;
use App\Models\TipoNotificacao;
use App\Models\ExecucaoEtapa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Notificacao::with(['tipoNotificacao', 'usuarioDestinatario'])
            ->porUsuario(Auth::id());

        // Filtros
        if ($request->filled('status_envio')) {
            $query->porStatusEnvio($request->status_envio);
        }

        if ($request->filled('canal')) {
            $query->porCanal($request->canal);
        }

        if ($request->filled('prioridade')) {
            $query->porPrioridade($request->prioridade);
        }

        if ($request->filled('lidas')) {
            if ($request->lidas === '1') {
                $query->lidas();
            } else {
                $query->naoLidas();
            }
        }

        $notificacoes = $query->recente()->paginate(20);

        // Estatísticas
        $estatisticas = [
            'total' => Notificacao::porUsuario(Auth::id())->count(),
            'nao_lidas' => Notificacao::porUsuario(Auth::id())->naoLidas()->count(),
            'urgentes' => Notificacao::porUsuario(Auth::id())->porPrioridade('URGENTE')->naoLidas()->count(),
        ];

        return view('notificacoes.index', compact('notificacoes', 'estatisticas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Notificacao $notificacao)
    {
        // Verificar se o usuário pode ver esta notificação
        if ($notificacao->usuario_destinatario_id !== Auth::id()) {
            abort(403, 'Acesso negado.');
        }

        // Marcar como lida
        $notificacao->marcarComoLida();

        $notificacao->load([
            'execucaoEtapa.acao',
            'execucaoEtapa.etapaFluxo',
            'tipoNotificacao',
            'usuarioDestinatario'
        ]);

        return view('notificacoes.show', compact('notificacao'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notificacao $notificacao)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notificacao $notificacao)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notificacao $notificacao)
    {
        //
    }

    /**
     * Marcar notificação como lida
     */
    public function marcarLida(Notificacao $notificacao)
    {
        if ($notificacao->usuario_destinatario_id !== Auth::id()) {
            abort(403, 'Acesso negado.');
        }

        $notificacao->marcarComoLida();

        return response()->json(['success' => true]);
    }

    /**
     * Marcar todas as notificações como lidas
     */
    public function marcarTodasLidas()
    {
        Notificacao::porUsuario(Auth::id())
            ->naoLidas()
            ->update([
                'data_leitura' => now(),
                'status_envio' => 'LIDO'
            ]);

        return redirect()
            ->route('notificacoes.index')
            ->with('success', 'Todas as notificações foram marcadas como lidas.');
    }

    /**
     * API: Retorna contadores de notificações
     */
    public function apiContadores()
    {
        $contadores = [
            'total' => Notificacao::porUsuario(Auth::id())->count(),
            'nao_lidas' => Notificacao::porUsuario(Auth::id())->naoLidas()->count(),
            'urgentes' => Notificacao::porUsuario(Auth::id())->porPrioridade('URGENTE')->naoLidas()->count(),
        ];

        return response()->json($contadores);
    }

    /**
     * API: Retorna notificações recentes
     */
    public function apiRecentes(Request $request)
    {
        $limite = $request->limite ?? 10;

        $notificacoes = Notificacao::with(['tipoNotificacao'])
            ->porUsuario(Auth::id())
            ->recente()
            ->limit($limite)
            ->get(['id', 'mensagem', 'prioridade', 'data_envio', 'data_leitura', 'tipo_notificacao_id']);

        return response()->json($notificacoes);
    }

    /**
     * Enviar notificação (para uso interno/admin)
     */
    public function enviar(Request $request)
    {
        $request->validate([
            'execucao_etapa_id' => 'required|exists:execucao_etapas,id',
            'usuario_destinatario_id' => 'required|exists:users,id',
            'tipo_notificacao_codigo' => 'required|string',
            'canal' => 'required|in:EMAIL,SISTEMA,SMS,WHATSAPP',
            'prioridade' => 'required|in:BAIXA,MEDIA,ALTA,URGENTE',
            'variaveis' => 'nullable|array',
            'assunto' => 'nullable|string|max:500',
        ]);

        $execucaoEtapa = ExecucaoEtapa::findOrFail($request->execucao_etapa_id);
        $destinatario = User::findOrFail($request->usuario_destinatario_id);
        $tipoNotificacao = TipoNotificacao::buscarPorCodigo($request->tipo_notificacao_codigo);

        if (!$tipoNotificacao) {
            return response()->json(['error' => 'Tipo de notificação não encontrado'], 404);
        }

        $variaveis = $request->variaveis ?? [];

        try {
            if ($request->canal === 'EMAIL') {
                $notificacao = Notificacao::criarNotificacaoEmail(
                    $execucaoEtapa,
                    $destinatario,
                    $tipoNotificacao,
                    $request->assunto ?? $tipoNotificacao->nome,
                    $variaveis,
                    $request->prioridade
                );
            } else {
                $notificacao = Notificacao::criarNotificacaoSistema(
                    $execucaoEtapa,
                    $destinatario,
                    $tipoNotificacao,
                    $variaveis,
                    $request->prioridade
                );
            }

            return response()->json([
                'success' => true,
                'notificacao_id' => $notificacao->id,
                'message' => 'Notificação enviada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao enviar notificação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gerenciamento de notificações (admin)
     */
    public function gerenciar(Request $request)
    {
        $query = Notificacao::with([
            'tipoNotificacao',
            'usuarioDestinatario',
            'execucaoEtapa.acao'
        ]);

        // Filtros administrativos
        if ($request->filled('usuario_id')) {
            $query->porUsuario($request->usuario_id);
        }

        if ($request->filled('status_envio')) {
            $query->porStatusEnvio($request->status_envio);
        }

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('data_envio', [
                $request->data_inicio,
                $request->data_fim . ' 23:59:59'
            ]);
        }

        $notificacoes = $query->recente()->paginate(30);

        $usuarios = User::orderBy('name')->get(['id', 'name']);
        $tiposNotificacao = TipoNotificacao::ativosOrdenados();

        return view('admin.notificacoes.gerenciar', compact('notificacoes', 'usuarios', 'tiposNotificacao'));
    }

    /**
     * Reenviar notificação com erro
     */
    public function reenviar(Notificacao $notificacao)
    {
        if ($notificacao->status_envio !== 'ERRO') {
            return response()->json(['error' => 'Apenas notificações com erro podem ser reenviadas'], 400);
        }

        try {
            // Reset dos campos de erro
            $notificacao->update([
                'status_envio' => 'PENDENTE',
                'erro_mensagem' => null,
                'data_envio' => now(),
            ]);

            // Aqui você adicionaria a lógica de reenvio conforme o canal
            // Por enquanto, apenas marcamos como enviada
            $notificacao->marcarComoEnviada();

            return response()->json(['success' => true, 'message' => 'Notificação reenviada com sucesso!']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao reenviar: ' . $e->getMessage()], 500);
        }
    }
}
