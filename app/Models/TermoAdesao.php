<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermoAdesao extends Model
{
    protected $table = 'termos_adesao';
    protected $fillable = ['descricao', 'data_criacao', 'path_arquivo', 'organizacao_id'];

    public function organizacao()
    {
        return $this->belongsTo(Organizacao::class);
    }
}
