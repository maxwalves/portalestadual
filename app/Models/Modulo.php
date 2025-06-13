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
        'is_ativo',
    ];

    public function etapasFluxo()
    {
        return $this->hasMany(EtapaFluxo::class, 'modulo_id');
    }
}
