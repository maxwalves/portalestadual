<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoExigencia extends Model
{
    use HasFactory;

    protected $table = 'grupo_exigencia';

    protected $fillable = [
        'nome',
        'descricao',
        'is_ativo',
    ];

    protected $casts = [
        'is_ativo' => 'boolean',
    ];

    /**
     * Scope para grupos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('is_ativo', true);
    }

    /**
     * Relacionamento com etapas de fluxo
     */
    public function etapasFluxo()
    {
        return $this->hasMany(EtapaFluxo::class, 'grupo_exigencia_id');
    }

    /**
     * Relacionamento com templates de documento
     */
    public function templatesDocumento()
    {
        return $this->hasMany(TemplateDocumento::class, 'grupo_exigencia_id');
    }
}
