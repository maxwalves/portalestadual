<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    protected $table = 'modulo';

    protected $fillable = [
        'nome',
        'tipo',
        'descricao',
        'icone',
        'cor',
        'campos_customizaveis',
        'configuracao_padrao',
        'is_ativo'
    ];

    protected $casts = [
        'campos_customizaveis' => 'array',
        'configuracao_padrao' => 'array',
        'is_ativo' => 'boolean'
    ];

    // ===== RELACIONAMENTOS =====

    public function etapasFluxo()
    {
        return $this->hasMany(EtapaFluxo::class);
    }

    // ===== SCOPES =====

    public function scopeAtivos($query)
    {
        return $query->where('is_ativo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}
