<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $table = 'status';

    protected $fillable = [
        'codigo',
        'nome',
        'descricao',
        'categoria',
        'cor',
        'icone',
        'ordem',
        'is_ativo',
    ];

    protected $casts = [
        'is_ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    // Relacionamentos

    /**
     * Execuções de etapas que possuem este status
     */
    public function execucaoEtapas()
    {
        return $this->hasMany(ExecucaoEtapa::class, 'status_id');
    }

    /**
     * Transições onde este status é condição
     */
    public function transicoesCondicao()
    {
        return $this->hasMany(TransicaoEtapa::class, 'status_condicao_id');
    }

    /**
     * Históricos onde este status era o anterior
     */
    public function historicosStatusAnterior()
    {
        return $this->hasMany(HistoricoEtapa::class, 'status_anterior_id');
    }

    /**
     * Históricos onde este status é o novo
     */
    public function historicosStatusNovo()
    {
        return $this->hasMany(HistoricoEtapa::class, 'status_novo_id');
    }

    /**
     * Etapas que podem usar este status
     */
    public function etapaStatusOpcoes()
    {
        return $this->hasMany(EtapaStatusOpcao::class, 'status_id');
    }

    // Scopes

    /**
     * Scope para status ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('is_ativo', true);
    }

    /**
     * Scope por categoria
     */
    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Scope ordenado
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    // Métodos auxiliares

    /**
     * Verifica se o status é ativo
     */
    public function isAtivo(): bool
    {
        return $this->is_ativo;
    }

    /**
     * Retorna a cor do status ou uma cor padrão
     */
    public function getCorAttribute($value): string
    {
        return $value ?: '#6c757d';
    }

    /**
     * Retorna badge HTML com cor
     */
    public function getBadgeHtml(): string
    {
        return sprintf(
            '<span class="badge" style="background-color: %s">%s</span>',
            $this->cor,
            $this->nome
        );
    }
}
