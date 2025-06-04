<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CadastroDemandaGms extends Model
{
    protected $table = 'cadastro_demanda_gms';

    protected $fillable = [
        'descricao',
        'codigoGMS',
        'protocolo'
    ];

    /**
     * Get the demandas associated with this cadastro.
     */
    public function demandas()
    {
        return $this->hasMany(Demanda::class, 'cadastro_demanda_gms_id');
    }
}
