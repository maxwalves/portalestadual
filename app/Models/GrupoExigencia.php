<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GrupoExigencia extends Model
{
    use HasFactory;

    protected $table = 'grupo_exigencia';

    protected $fillable = [
        'nome',
        'descricao',
        'is_ativo'
    ];

    protected $casts = [
        'is_ativo' => 'boolean'
    ];

    // ===== RELACIONAMENTOS =====

    public function etapasFluxo()
    {
        return $this->hasMany(EtapaFluxo::class);
    }

    /**
     * Relacionamento com templates de documentos (many-to-many)
     */
    public function templatesDocumento(): BelongsToMany
    {
        return $this->belongsToMany(TemplateDocumento::class, 'grupo_exigencia_template_documento')
                    ->withPivot(['is_obrigatorio', 'ordem', 'observacoes'])
                    ->withTimestamps()
                    ->orderByPivot('ordem');
    }

    /**
     * Relacionamento direto com tabela pivot (para gerenciamento)
     */
    public function templatesPivot(): HasMany
    {
        return $this->hasMany(GrupoExigenciaTemplateDocumento::class);
    }

    // ===== SCOPES =====

    public function scopeAtivos($query)
    {
        return $query->where('is_ativo', true);
    }

    public function scopeComTemplates($query)
    {
        return $query->has('templatesDocumento');
    }

    public function scopeSemTemplates($query)
    {
        return $query->doesntHave('templatesDocumento');
    }

    // ===== MÉTODOS AUXILIARES =====

    /**
     * Verifica se o grupo tem templates obrigatórios
     */
    public function hasTemplatesObrigatorios(): bool
    {
        return $this->templatesDocumento()->wherePivot('is_obrigatorio', true)->exists();
    }

    /**
     * Obtém apenas templates obrigatórios
     */
    public function getTemplatesObrigatorios()
    {
        return $this->templatesDocumento()->wherePivot('is_obrigatorio', true)->orderByPivot('ordem')->get();
    }

    /**
     * Obtém apenas templates opcionais
     */
    public function getTemplatesOpcionais()
    {
        return $this->templatesDocumento()->wherePivot('is_obrigatorio', false)->orderByPivot('ordem')->get();
    }

    /**
     * Verifica se pode ser excluído
     */
    public function podeSerExcluido(): bool
    {
        return $this->templatesDocumento()->count() === 0 && 
               $this->etapasFluxo()->count() === 0;
    }

    /**
     * Obtém estatísticas do grupo
     */
    public function getEstatisticas(): array
    {
        return [
            'total_templates' => $this->templatesDocumento()->count(),
            'templates_obrigatorios' => $this->templatesDocumento()->wherePivot('is_obrigatorio', true)->count(),
            'templates_opcionais' => $this->templatesDocumento()->wherePivot('is_obrigatorio', false)->count(),
            'etapas_fluxo' => $this->etapasFluxo()->count(),
            'tipos_documento_diferentes' => $this->templatesDocumento()->distinct('template_documentos.tipo_documento_id')->count(),
        ];
    }

    /**
     * Obtém status formatado
     */
    public function getStatusFormatado(): string
    {
        return $this->is_ativo ? 'Ativo' : 'Inativo';
    }

    /**
     * Obtém cor do badge de status
     */
    public function getCorStatus(): string
    {
        return $this->is_ativo ? 'success' : 'secondary';
    }

    /**
     * Obtém ícone do status
     */
    public function getIconeStatus(): string
    {
        return $this->is_ativo ? 'fas fa-check' : 'fas fa-times';
    }
}
