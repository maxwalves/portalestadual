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

    /**
     * Obtém templates obrigatórios da etapa
     */
    public function getDocumentosObrigatoriosAttribute()
    {
        if (!$this->etapaFluxo || !$this->etapaFluxo->grupoExigencia) {
            return collect();
        }

        return $this->etapaFluxo->grupoExigencia->templatesDocumento()
            ->with('tipoDocumento')
            ->wherePivot('is_obrigatorio', true)
            ->orderByPivot('ordem')
            ->get();
    }

    /**
     * Obtém documentos enviados agrupados por tipo
     */
    public function getDocumentosEnviadosAttribute()
    {
        return $this->documentos()->with('tipoDocumento', 'usuarioUpload')->get();
    }

    /**
     * Verifica se pode enviar documento
     */
    public function podeEnviarDocumento(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        // NOVA ABORDAGEM: Flexibilidade total
        // Deve ser da organização executora OU solicitante (ambas podem enviar documentos)
        // Apenas etapas canceladas não permitem envio
        return ($user->organizacao_id === $this->etapaFluxo->organizacao_executora_id || 
                $user->organizacao_id === $this->etapaFluxo->organizacao_solicitante_id) &&
               $this->status->codigo !== 'CANCELADO';
    }

    /**
     * Verifica se pode concluir a etapa
     */
    public function podeConcluir(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        // NOVA ABORDAGEM: Flexibilidade total
        // Deve ser da organização solicitante E etapa não pode estar cancelada
        return $user->organizacao_id === $this->etapaFluxo->organizacao_solicitante_id && 
               $this->status->codigo !== 'CANCELADO';
    }

    /**
     * Verifica se todos documentos obrigatórios estão aprovados
     */
    public function getTodosDocumentosAprovadosAttribute(): bool
    {
        $grupoExigencia = $this->etapaFluxo->grupoExigencia;
        if (!$grupoExigencia) {
            return true; // Etapa sem documentos obrigatórios
        }

        $templatesObrigatorios = $grupoExigencia->templatesDocumento()
            ->where('is_obrigatorio', true)
            ->get();

        if ($templatesObrigatorios->isEmpty()) {
            return true;
        }

        foreach ($templatesObrigatorios as $template) {
            $documentoAprovado = $this->documentos()
                ->where('tipo_documento_id', $template->tipo_documento_id)
                ->where('status_documento', \App\Models\Documento::STATUS_APROVADO)
                ->exists();

            if (!$documentoAprovado) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verifica se está atrasada
     */
    public function getEstaAtrasadaAttribute(): bool
    {
        return $this->isEmAtraso();
    }

    /**
     * Verifica se está próxima do vencimento (3 dias)
     */
    public function getEstaProximaDoVencimentoAttribute(): bool
    {
        if (is_null($this->data_prazo) || !is_null($this->data_conclusao)) {
            return false;
        }

        $diasRestantes = now()->diffInDays($this->data_prazo, false);
        return $diasRestantes <= 3 && $diasRestantes > 0;
    }

    /**
     * Obtém cor do status
     */
    public function getStatusCorAttribute(): string
    {
        $cores = [
            'PENDENTE' => 'secondary',
            'EM_ANALISE' => 'warning',
            'APROVADO' => 'success',
            'REPROVADO' => 'danger',
            'DEVOLVIDO' => 'warning',
            'CANCELADO' => 'dark'
        ];

        return $cores[$this->status->codigo] ?? 'secondary';
    }
} 