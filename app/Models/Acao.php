<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acao extends Model
{
    use HasFactory;

    protected $table = 'acoes';

    protected $fillable = [
        'demanda_id',
        'tipo_fluxo_id',
        'codigo_referencia',
        'projeto_sam',
        'nome',
        'descricao',
        'valor_estimado',
        'valor_contratado',
        'valor_executado',
        'percentual_execucao',
        'localizacao',
        'coordenadas_lat',
        'coordenadas_lng',
        'data_inicio_previsto',
        'data_fim_previsto',
        'data_inicio_real',
        'data_fim_real',
        'status',
        'is_finalizado',
        'data_finalizacao',
        'usuario_finalizacao_id',
        'observacao_finalizacao',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'valor_estimado' => 'decimal:2',
        'valor_contratado' => 'decimal:2',
        'valor_executado' => 'decimal:2',
        'percentual_execucao' => 'decimal:2',
        'coordenadas_lat' => 'decimal:8',
        'coordenadas_lng' => 'decimal:8',
        'data_inicio_previsto' => 'date',
        'data_fim_previsto' => 'date',
        'data_inicio_real' => 'date',
        'data_fim_real' => 'date',
        'data_finalizacao' => 'datetime',
        'is_finalizado' => 'boolean',
    ];

    /**
     * Relacionamento com Demanda
     */
    public function demanda()
    {
        return $this->belongsTo(Demanda::class);
    }

    /**
     * Relacionamento com TipoFluxo
     */
    public function tipoFluxo()
    {
        return $this->belongsTo(TipoFluxo::class);
    }

    /**
     * Get the user who created this acao.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this acao.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relacionamento com usuário que finalizou o projeto
     */
    public function usuarioFinalizacao()
    {
        return $this->belongsTo(User::class, 'usuario_finalizacao_id');
    }

    /**
     * Relacionamento com ExecucaoEtapa
     */
    public function execucoesEtapa()
    {
        return $this->hasMany(ExecucaoEtapa::class);
    }

    // ===== MÉTODOS AUXILIARES PARA FINALIZAÇÃO =====

    /**
     * Verificar se o projeto está finalizado
     */
    public function isFinalizado(): bool
    {
        return $this->is_finalizado;
    }

    /**
     * Verificar se o projeto pode ser finalizado
     */
    public function podeFinalizar(): bool
    {
        // Só pode finalizar se não estiver já finalizado
        if ($this->is_finalizado) {
            return false;
        }

        // NOVA ABORDAGEM: Flexibilidade total - pode finalizar na última etapa
        // independente do status das etapas anteriores
        return $this->isNaUltimaEtapa();
    }

    /**
     * Verificar se todas as etapas foram concluídas
     */
    public function todasEtapasConcluidas(): bool
    {
        $etapasFluxo = EtapaFluxo::where('tipo_fluxo_id', $this->tipo_fluxo_id)
            ->orderBy('ordem_execucao')
            ->get();

        foreach ($etapasFluxo as $etapa) {
            $execucao = $this->execucoesEtapa()
                ->where('etapa_fluxo_id', $etapa->id)
                ->first();

            // Se a etapa não foi executada ou não foi aprovada, retorna false
            if (!$execucao || $execucao->status->codigo !== 'APROVADO') {
                return false;
            }
        }

        return true;
    }

    /**
     * Verificar se está na última etapa do fluxo (considerando apenas execuções realizadas)
     */
    public function isNaUltimaEtapa(): bool
    {
        // Buscar todas as execuções desta ação, ordenadas por data de criação
        $execucoes = $this->execucoesEtapa()
            ->with('etapaFluxo')
            ->orderBy('created_at')
            ->get();

        if ($execucoes->isEmpty()) {
            return false;
        }

        // A última execução realizada é a "etapa atual" do caminho percorrido
        $ultimaExecucao = $execucoes->last();
        $etapaAtual = $ultimaExecucao->etapaFluxo;

        // Buscar todas as execuções em aberto (PENDENTE, EM_ANALISE, DEVOLVIDO)
        $emAberto = $execucoes->filter(function($exec) {
            return in_array($exec->status->codigo, ['PENDENTE', 'EM_ANALISE', 'DEVOLVIDO']);
        });

        // Se só existe uma execução em aberto, e ela é a última execução realizada, estamos na última etapa do caminho percorrido
        if ($emAberto->count() === 1 && $emAberto->first()->id === $ultimaExecucao->id) {
            return true;
        }

        // Se não há execuções em aberto, verificar se a última execução foi aprovada/finalizada
        if ($emAberto->isEmpty() && in_array($ultimaExecucao->status->codigo, ['APROVADO', 'FINALIZADO'])) {
            return true;
        }

        return false;
    }

    /**
     * Finalizar o projeto
     */
    public function finalizar(User $usuario, string $observacao = null): bool
    {

        \Log::info('Finalizando ação', ['id' => $this->id]);
        // Permite finalizar se não estiver já finalizado
        if ($this->is_finalizado) {
            return false;
        }

        // Buscar todas as etapas do fluxo para verificar se há etapas puladas
        $todasEtapas = \App\Models\EtapaFluxo::where('tipo_fluxo_id', $this->tipo_fluxo_id)
            ->orderBy('ordem_execucao')
            ->get();

        // Buscar todas as execuções existentes
        $execucoesExistentes = \App\Models\ExecucaoEtapa::where('acao_id', $this->id)
            ->with('status')
            ->get()
            ->keyBy('etapa_fluxo_id');

        // Buscar status "Não Aplicável" para etapas puladas
        $statusNaoAplicavel = \App\Models\Status::where('codigo', 'NAO_APLICAVEL')->first();
        if (!$statusNaoAplicavel) {
            // Criar o status se não existir
            $statusNaoAplicavel = \App\Models\Status::create([
                'nome' => 'Não Aplicável',
                'codigo' => 'NAO_APLICAVEL',
                'descricao' => 'Etapa não aplicável no fluxo condicional',
                'categoria' => 'GERAL',
                'cor' => '#6c757d',
                'icone' => 'fas fa-minus-circle',
                'is_ativo' => true,
                'ordem' => 99
            ]);
        }

        // Marcar etapas não executadas como "Não Aplicável"
        $etapasPuladas = 0;
        foreach ($todasEtapas as $etapa) {
            $execucao = $execucoesExistentes->get($etapa->id);
            
            if (!$execucao) {
                // Etapa não foi executada - criar execução como "Não Aplicável"
                \App\Models\ExecucaoEtapa::create([
                    'acao_id' => $this->id,
                    'etapa_fluxo_id' => $etapa->id,
                    'status_id' => $statusNaoAplicavel->id,
                    'usuario_responsavel_id' => $usuario->id,
                    'data_inicio' => now(),
                    'data_conclusao' => now(),
                    'observacoes' => 'Etapa marcada como não aplicável na finalização do projeto',
                    'created_by' => $usuario->id,
                    'updated_by' => $usuario->id
                ]);
                $etapasPuladas++;
            }
        }

        // Adicionar informação sobre etapas puladas na observação
        $observacaoFinal = $observacao;
        if ($etapasPuladas > 0) {
            $observacaoFinal = ($observacao ? $observacao . ' | ' : '') . "{$etapasPuladas} etapas marcadas como não aplicáveis";
        }

        $this->update([
            'is_finalizado' => true,
            'data_finalizacao' => now(),
            'usuario_finalizacao_id' => $usuario->id,
            'observacao_finalizacao' => $observacaoFinal,
            'status' => 'FINALIZADO',
        ]);

        \Log::info('Ação finalizada', [
            'id' => $this->id,
            'is_finalizado' => $this->fresh()->is_finalizado,
            'etapas_puladas' => $etapasPuladas,
            'etapas_total' => $todasEtapas->count()
        ]);
        
        return true;
    }

    /**
     * Reabrir projeto (apenas admins)
     */
    public function reabrir(User $usuario, string $motivo = null): bool
    {
        if (!$usuario->hasRole(['admin', 'admin_paranacidade'])) {
            return false;
        }

        $this->update([
            'is_finalizado' => false,
            'data_finalizacao' => null,
            'usuario_finalizacao_id' => null,
            'observacao_finalizacao' => $motivo ? "REABERTO: {$motivo}" : 'Projeto reaberto pelo administrador',
            'status' => 'EM_EXECUCAO',
        ]);

        return true;
    }

    /**
     * Scope para projetos finalizados
     */
    public function scopeFinalizados($query)
    {
        return $query->where('is_finalizado', true);
    }

    /**
     * Scope para projetos em andamento
     */
    public function scopeEmAndamento($query)
    {
        return $query->where('is_finalizado', false);
    }
}
