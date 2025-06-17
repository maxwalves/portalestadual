<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransicaoEtapa extends Model
{
    use HasFactory;

    protected $table = 'transicao_etapas';

    protected $fillable = [
        'etapa_fluxo_origem_id',
        'etapa_fluxo_destino_id',
        'status_condicao_id',
        'condicao_tipo',
        'condicao_operador',
        'condicao_valor',
        'condicao_campo',
        'logica_adicional',
        'prioridade',
        'descricao',
        'mensagem_transicao',
        'is_ativo',
    ];

    protected $casts = [
        'is_ativo' => 'boolean',
        'prioridade' => 'integer',
        'condicao_valor' => 'json', // Pode ser JSON para condições complexas
    ];

    // Relacionamentos

    /**
     * Relacionamento com etapa de origem
     */
    public function etapaOrigem(): BelongsTo
    {
        return $this->belongsTo(EtapaFluxo::class, 'etapa_fluxo_origem_id');
    }

    /**
     * Relacionamento com etapa de destino
     */
    public function etapaDestino(): BelongsTo
    {
        return $this->belongsTo(EtapaFluxo::class, 'etapa_fluxo_destino_id');
    }

    /**
     * Relacionamento com status que dispara a transição
     */
    public function statusCondicao(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_condicao_id');
    }

    // Scopes

    /**
     * Scope para transições ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('is_ativo', true);
    }

    /**
     * Scope para ordenar por prioridade
     */
    public function scopeOrdenadaPorPrioridade($query)
    {
        return $query->orderBy('prioridade', 'desc');
    }

    /**
     * Scope para filtrar por etapa de origem
     */
    public function scopePorEtapaOrigem($query, $etapaId)
    {
        return $query->where('etapa_fluxo_origem_id', $etapaId);
    }

    /**
     * Scope para filtrar por status
     */
    public function scopePorStatus($query, $statusId)
    {
        return $query->where('status_condicao_id', $statusId);
    }

    // Métodos auxiliares

    /**
     * Verifica se a transição está ativa
     */
    public function isAtiva(): bool
    {
        return $this->is_ativo;
    }

    /**
     * Avalia se a condição da transição é atendida
     */
    public function avaliarCondicao($execucaoEtapa): bool
    {
        if ($this->condicao_tipo === 'SEMPRE') {
            return true;
        }

        if ($this->condicao_tipo === 'STATUS') {
            return $execucaoEtapa->status_id === $this->status_condicao_id;
        }

        // TODO: Implementar outras condições conforme necessário
        return false;
    }

    /**
     * Retorna descrição resumida da condição
     */
    public function getDescricaoCondicao(): string
    {
        switch ($this->condicao_tipo) {
            case 'SEMPRE':
                return 'Sempre executa';
            case 'STATUS':
                return $this->statusCondicao 
                    ? "Quando status for: {$this->statusCondicao->nome}"
                    : 'Condição de status não definida';
            default:
                return "Condição: {$this->condicao_tipo}";
        }
    }

    /**
     * Verificar se a transição deve ser executada baseada no status
     */
    public function deveExecutar($statusAtual = null): bool
    {
        if (!$this->is_ativo) {
            return false;
        }

        // Se não tem condição de status, sempre executa
        if (!$this->status_condicao_id) {
            return true;
        }

        // Verificar se o status atual corresponde à condição
        return $this->status_condicao_id == $statusAtual;
    }

    /**
     * Buscar próxima etapa baseada no status atual
     */
    public static function buscarProximaEtapa($etapaAtualId, $statusAtual = null)
    {
        $transicoes = static::where('etapa_fluxo_origem_id', $etapaAtualId)
            ->ativas()
            ->ordenadaPorPrioridade()
            ->with('etapaDestino')
            ->get();

        foreach ($transicoes as $transicao) {
            if ($transicao->deveExecutar($statusAtual)) {
                return $transicao->etapaDestino;
            }
        }

        return null;
    }

    /**
     * Verificar se existe transição para um status específico
     */
    public static function existeTransicaoPara($etapaOrigemId, $statusId)
    {
        return static::where('etapa_fluxo_origem_id', $etapaOrigemId)
            ->where('status_condicao_id', $statusId)
            ->ativas()
            ->exists();
    }
}
