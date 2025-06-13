<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Etapa de origem da transição
     */
    public function etapaFluxoOrigem()
    {
        return $this->belongsTo(EtapaFluxo::class, 'etapa_fluxo_origem_id');
    }

    /**
     * Etapa de destino da transição
     */
    public function etapaFluxoDestino()
    {
        return $this->belongsTo(EtapaFluxo::class, 'etapa_fluxo_destino_id');
    }

    /**
     * Status que condiciona a transição
     */
    public function statusCondicao()
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
     * Scope por etapa de origem
     */
    public function scopePorEtapaOrigem($query, $etapaId)
    {
        return $query->where('etapa_fluxo_origem_id', $etapaId);
    }

    /**
     * Scope ordenado por prioridade
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('prioridade', 'desc');
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
}
