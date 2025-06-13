<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtapaStatusOpcao extends Model
{
    use HasFactory;

    protected $table = 'etapa_status_opcoes';

    // Não tem auto-incrementing ID pois usa chave composta
    public $incrementing = false;
    
    // Chave primária composta
    protected $primaryKey = ['etapa_fluxo_id', 'status_id'];
    
    // Tipo da chave primária
    protected $keyType = 'array';

    // Não tem updated_at, apenas created_at
    public $timestamps = false;

    protected $fillable = [
        'etapa_fluxo_id',
        'status_id',
        'ordem',
        'is_padrao',
        'mostra_para_responsavel',
        'requer_justificativa',
    ];

    protected $casts = [
        'ordem' => 'integer',
        'is_padrao' => 'boolean',
        'mostra_para_responsavel' => 'boolean',
        'requer_justificativa' => 'boolean',
        'created_at' => 'datetime',
    ];

    protected $dates = [
        'created_at',
    ];

    // Relacionamentos

    /**
     * Etapa do fluxo
     */
    public function etapaFluxo()
    {
        return $this->belongsTo(EtapaFluxo::class, 'etapa_fluxo_id');
    }

    /**
     * Status disponível
     */
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    // Scopes

    /**
     * Scope por etapa
     */
    public function scopePorEtapa($query, $etapaId)
    {
        return $query->where('etapa_fluxo_id', $etapaId);
    }

    /**
     * Scope para status padrão
     */
    public function scopePadrao($query)
    {
        return $query->where('is_padrao', true);
    }

    /**
     * Scope para status visíveis ao responsável
     */
    public function scopeVisivelResponsavel($query)
    {
        return $query->where('mostra_para_responsavel', true);
    }

    /**
     * Scope ordenado
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('status_id');
    }

    // Métodos auxiliares

    /**
     * Verifica se é status padrão
     */
    public function isPadrao(): bool
    {
        return $this->is_padrao;
    }

    /**
     * Verifica se é visível ao responsável
     */
    public function isVisivelResponsavel(): bool
    {
        return $this->mostra_para_responsavel;
    }

    /**
     * Verifica se requer justificativa
     */
    public function requerJustificativa(): bool
    {
        return $this->requer_justificativa;
    }

    /**
     * Override do método getKey para chave composta
     */
    public function getKey()
    {
        $attributes = [];
        foreach ($this->getKeyName() as $key) {
            $attributes[$key] = $this->getAttribute($key);
        }
        return $attributes;
    }

    /**
     * Override do método getKeyName para chave composta
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }
}
