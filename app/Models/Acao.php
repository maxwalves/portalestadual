<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acao extends Model
{
    use HasFactory;

    protected $table = 'acoes';

    protected $fillable = [
        'descricao',
        'demanda_id',
        'projeto_sam',
        'tipo_fluxo_id',
        'valor_estimado',
        'valor_contratado',
        'localizacao',
    ];

    protected $casts = [
        'valor_estimado' => 'decimal:2',
        'valor_contratado' => 'decimal:2',
    ];

    /**
     * Relacionamento com Demanda
     */
    public function demanda()
    {
        return $this->belongsTo(Demanda::class);
    }

    /**
     * Relacionamento com TipoFluxo
     */
    public function tipoFluxo()
    {
        return $this->belongsTo(TipoFluxo::class);
    }

    /**
     * Relacionamento com ExecucaoEtapa
     * TODO: Implementar posteriormente
     */
    // public function execucoesEtapa()
    // {
    //     return $this->hasMany(ExecucaoEtapa::class);
    // }
}
