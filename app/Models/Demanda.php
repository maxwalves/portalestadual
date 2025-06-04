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

    /**
     * Get the organizacao through the termo adesao.
     */
    public function organizacao()
    {
        return $this->hasOneThrough(
            Organizacao::class,
            TermoAdesao::class,
            'id', // Foreign key on termo_adesao table
            'id', // Foreign key on organizacao table
            'termo_adesao_id', // Local key on demandas table
            'organizacao_id' // Local key on termo_adesao table
        );
    }

    /**
     * Get all acoes for this demanda.
     */
    public function acoes()
    {
        return $this->hasMany(Acao::class);
    }
}
