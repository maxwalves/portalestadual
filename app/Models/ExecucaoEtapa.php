<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExecucaoEtapa extends Model
{
    use HasFactory;

    protected $table = 'execucao_etapas';

    protected $fillable = [
        'acao_id',
        'etapa_fluxo_id',
        'usuario_responsavel_id',
        'status_id',
        'etapa_anterior_id',
        'data_inicio',
        'data_prazo',
        'data_conclusao',
        'dias_em_atraso',
        'observacoes',
        'justificativa',
        'motivo_transicao',
        'dados_especificos',
        'percentual_conclusao',
        'notificacao_enviada',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_prazo' => 'datetime',
        'data_conclusao' => 'datetime',
        'dados_especificos' => 'array',
        'percentual_conclusao' => 'decimal:2',
        'notificacao_enviada' => 'boolean',
        'dias_em_atraso' => 'integer',
    ];

    /**
     * Relacionamento com ação
     */
    public function acao(): BelongsTo
    {
        return $this->belongsTo(Acao::class);
    }

    /**
     * Relacionamento com etapa do fluxo
     */
    public function etapaFluxo(): BelongsTo
    {
        return $this->belongsTo(EtapaFluxo::class);
    }

    /**
     * Relacionamento com usuário responsável
     */
    public function usuarioResponsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_responsavel_id');
    }

    /**
     * Relacionamento com status
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Relacionamento com etapa anterior
     */
    public function etapaAnterior(): BelongsTo
    {
        return $this->belongsTo(ExecucaoEtapa::class, 'etapa_anterior_id');
    }

    /**
     * Relacionamento com documentos
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * Relacionamento com histórico de etapas
     */
    public function historicos(): HasMany
    {
        return $this->hasMany(HistoricoEtapa::class);
    }

    /**
     * Relacionamento com notificações
     */
    public function notificacoes(): HasMany
    {
        return $this->hasMany(Notificacao::class);
    }

    /**
     * Verifica se a etapa está em atraso
     */
    public function isEmAtraso(): bool
    {
        if (is_null($this->data_prazo) || !is_null($this->data_conclusao)) {
            return false;
        }

        return $this->data_prazo < now();
    }

    /**
     * Verifica se a etapa foi concluída
     */
    public function isConcluida(): bool
    {
        return !is_null($this->data_conclusao);
    }

    /**
     * Calcula os dias em atraso
     */
    public function calcularDiasAtraso(): int
    {
        if (!$this->isEmAtraso()) {
            return 0;
        }

        return now()->diffInDays($this->data_prazo);
    }
} 