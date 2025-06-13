<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoDocumento extends Model
{
    use HasFactory;

    protected $table = 'tipo_documentos';

    protected $fillable = [
        'codigo',
        'nome',
        'descricao',
        'extensoes_permitidas',
        'tamanho_maximo_mb',
        'requer_assinatura',
        'categoria',
        'is_ativo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'requer_assinatura' => 'boolean',
        'is_ativo' => 'boolean',
        'tamanho_maximo_mb' => 'integer',
    ];

    /**
     * Relacionamento com templates de documento
     */
    public function templatesDocumento(): HasMany
    {
        return $this->hasMany(TemplateDocumento::class);
    }

    /**
     * Relacionamento com documentos
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * Scope para tipos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('is_ativo', true);
    }

    /**
     * Scope para filtrar por categoria
     */
    public function scopeCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Obtém as extensões permitidas como array
     */
    public function getExtensoesPermitidas(): array
    {
        if (empty($this->extensoes_permitidas)) {
            return [];
        }
        
        return explode(',', strtolower($this->extensoes_permitidas));
    }

    /**
     * Verifica se uma extensão é permitida
     */
    public function isExtensaoPermitida(string $extensao): bool
    {
        $extensoesPermitidas = $this->getExtensoesPermitidas();
        return in_array(strtolower($extensao), $extensoesPermitidas);
    }

    /**
     * Verifica se o tamanho em bytes é permitido
     */
    public function isTamanhoPermitido(int $tamanhoBytes): bool
    {
        $tamanhoMaximoBytes = $this->tamanho_maximo_mb * 1024 * 1024;
        return $tamanhoBytes <= $tamanhoMaximoBytes;
    }

    /**
     * Obtém o tamanho máximo em bytes
     */
    public function getTamanhoMaximoBytes(): int
    {
        return $this->tamanho_maximo_mb * 1024 * 1024;
    }

    /**
     * Formata o tamanho máximo para exibição
     */
    public function getTamanhoMaximoFormatado(): string
    {
        if ($this->tamanho_maximo_mb >= 1024) {
            return number_format($this->tamanho_maximo_mb / 1024, 1) . ' GB';
        }
        
        return $this->tamanho_maximo_mb . ' MB';
    }

    /**
     * Obtém ícone baseado na categoria
     */
    public function getIconeCategoria(): string
    {
        $icones = [
            'PROJETO' => 'fas fa-drafting-compass',
            'FINANCEIRO' => 'fas fa-file-invoice-dollar',
            'LICENCA' => 'fas fa-certificate',
            'JURIDICO' => 'fas fa-gavel',
            'TECNICO' => 'fas fa-cogs',
            'ADMINISTRATIVO' => 'fas fa-file-alt',
        ];

        return $icones[$this->categoria] ?? 'fas fa-file';
    }

    /**
     * Obtém cor baseada na categoria
     */
    public function getCorCategoria(): string
    {
        $cores = [
            'PROJETO' => 'primary',
            'FINANCEIRO' => 'success',
            'LICENCA' => 'warning',
            'JURIDICO' => 'danger',
            'TECNICO' => 'info',
            'ADMINISTRATIVO' => 'secondary',
        ];

        return $cores[$this->categoria] ?? 'dark';
    }
} 