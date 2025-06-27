<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrupoExigenciaTemplateDocumento extends Model
{
    use HasFactory;

    protected $table = 'grupo_exigencia_template_documento';

    protected $fillable = [
        'grupo_exigencia_id',
        'template_documento_id',
        'is_obrigatorio',
        'ordem',
        'observacoes',
    ];

    protected $casts = [
        'is_obrigatorio' => 'boolean',
        'ordem' => 'integer',
    ];

    /**
     * Relacionamento com grupo de exigência
     */
    public function grupoExigencia(): BelongsTo
    {
        return $this->belongsTo(GrupoExigencia::class);
    }

    /**
     * Relacionamento com template de documento
     */
    public function templateDocumento(): BelongsTo
    {
        return $this->belongsTo(TemplateDocumento::class);
    }

    /**
     * Scope para ordenar por ordem
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem');
    }

    /**
     * Scope para obrigatórios
     */
    public function scopeObrigatorios($query)
    {
        return $query->where('is_obrigatorio', true);
    }

    /**
     * Scope para opcionais
     */
    public function scopeOpcionais($query)
    {
        return $query->where('is_obrigatorio', false);
    }

    /**
     * Obtém o status de obrigatoriedade formatado
     */
    public function getObrigatoriedadeFormatada(): string
    {
        return $this->is_obrigatorio ? 'Obrigatório' : 'Opcional';
    }

    /**
     * Obtém a cor do badge de obrigatoriedade
     */
    public function getCorObrigatoriedade(): string
    {
        return $this->is_obrigatorio ? 'danger' : 'info';
    }

    /**
     * Obtém o ícone de obrigatoriedade
     */
    public function getIconeObrigatoriedade(): string
    {
        return $this->is_obrigatorio ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
    }
} 