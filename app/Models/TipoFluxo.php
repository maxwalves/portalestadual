<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoFluxo extends Model
{
    use HasFactory;

    protected $table = 'tipo_fluxo';

    protected $fillable = [
        'nome',
        'descricao',
        'categoria',
        'versao',
        'is_ativo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_ativo' => 'boolean',
    ];

    /**
     * Relacionamento com Acao
     */
    public function acoes()
    {
        return $this->hasMany(Acao::class);
    }

    /**
     * Relacionamento com EtapaFluxo
     */
    public function etapasFluxo()
    {
        return $this->hasMany(EtapaFluxo::class);
    }
}
