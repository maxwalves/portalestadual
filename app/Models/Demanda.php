<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Demanda extends Model
{
    protected $table = 'demandas';

    protected $fillable = [
        'termo_adesao_id',
        'cadastro_demanda_gms_id',
        'prioridade_sam',
        'nome',
        'descricao',
        'justificativa',
        'valor_estimado',
        'localizacao',
        'coordenadas_lat',
        'coordenadas_lng',
        'beneficiarios_estimados',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'valor_estimado' => 'decimal:2',
        'coordenadas_lat' => 'decimal:8',
        'coordenadas_lng' => 'decimal:8',
        'beneficiarios_estimados' => 'integer',
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

    /**
     * Get the user who created this demanda.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this demanda.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
