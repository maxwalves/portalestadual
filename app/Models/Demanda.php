<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Demanda extends Model
{

    protected $fillable = [
        'descricao',
        'prioridade_sam',
        'termo_adesao_id',
        'cadastro_demanda_gms_id'
    ];

    /**
     * Get the termo adesao associated with this demanda.
     */
    public function termoAdesao()
    {
        return $this->belongsTo(TermoAdesao::class, 'termo_adesao_id');
    }

    /**
     * Get the cadastro GMS associated with this demanda.
     */
    public function cadastroDemandaGms()
    {
        return $this->belongsTo(CadastroDemandaGms::class, 'cadastro_demanda_gms_id');
    }
}
