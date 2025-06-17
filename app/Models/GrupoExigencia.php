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

    public function templatesDocumento()
    {
        return $this->hasMany(TemplateDocumento::class);
    }

    // ===== SCOPES =====

    public function scopeAtivos($query)
    {
        return $query->where('is_ativo', true);
    }
}
