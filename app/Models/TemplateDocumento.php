<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TemplateDocumento extends Model
{
    use HasFactory;

    protected $table = 'template_documentos';

    protected $fillable = [
        'grupo_exigencia_id',
        'tipo_documento_id',
        'nome',
        'descricao',
        'caminho_modelo_storage',
        'exemplo_preenchido',
        'is_obrigatorio',
        'ordem',
        'instrucoes_preenchimento',
        'validacoes_customizadas',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_obrigatorio' => 'boolean',
        'ordem' => 'integer',
        'validacoes_customizadas' => 'array',
    ];

    /**
     * Relacionamento com grupo de exigência (legacy - manter por compatibilidade)
     */
    public function grupoExigencia(): BelongsTo
    {
        return $this->belongsTo(GrupoExigencia::class);
    }

    /**
     * Relacionamento com grupos de exigências (many-to-many)
     */
    public function gruposExigencia(): BelongsToMany
    {
        return $this->belongsToMany(GrupoExigencia::class, 'grupo_exigencia_template_documento')
                    ->withPivot(['is_obrigatorio', 'ordem', 'observacoes'])
                    ->withTimestamps()
                    ->orderByPivot('ordem');
    }

    /**
     * Relacionamento com tipo de documento
     */
    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class);
    }

    /**
     * Relacionamento com documentos que usaram este template
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * Scope para templates obrigatórios (apenas para tabela direta)
     */
    public function scopeObrigatorios($query)
    {
        return $query->where('template_documentos.is_obrigatorio', true);
    }

    /**
     * Scope para templates opcionais (apenas para tabela direta)
     */
    public function scopeOpcionais($query)
    {
        return $query->where('template_documentos.is_obrigatorio', false);
    }

    /**
     * Scope para ordenar por ordem de apresentação
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem');
    }

    /**
     * Scope para filtrar por grupo de exigência
     */
    public function scopeGrupoExigencia($query, $grupoExigenciaId)
    {
        return $query->where('grupo_exigencia_id', $grupoExigenciaId);
    }

    /**
     * Verifica se o template tem arquivo modelo
     */
    public function hasArquivoModelo(): bool
    {
        return !empty($this->caminho_modelo_storage);
    }

    /**
     * Verifica se o template tem exemplo preenchido
     */
    public function hasExemploPreenchido(): bool
    {
        return !empty($this->exemplo_preenchido);
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

    /**
     * Verifica se tem validações customizadas
     */
    public function hasValidacoesCustomizadas(): bool
    {
        return !empty($this->validacoes_customizadas);
    }

    /**
     * Obtém validações customizadas formatadas
     */
    public function getValidacoesFormatadas(): array
    {
        if (!$this->hasValidacoesCustomizadas()) {
            return [];
        }

        return $this->validacoes_customizadas;
    }

    /**
     * Verifica se uma validação específica existe
     */
    public function hasValidacao(string $nomeValidacao): bool
    {
        if (!$this->hasValidacoesCustomizadas()) {
            return false;
        }

        return array_key_exists($nomeValidacao, $this->validacoes_customizadas);
    }

    /**
     * Obtém uma validação específica
     */
    public function getValidacao(string $nomeValidacao, $default = null)
    {
        if (!$this->hasValidacao($nomeValidacao)) {
            return $default;
        }

        return $this->validacoes_customizadas[$nomeValidacao];
    }
} 