<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acao extends Model
{
    use HasFactory;

    protected $table = 'acoes';

    protected $fillable = [
        'demanda_id',
        'tipo_fluxo_id',
        'codigo_referencia',
        'projeto_sam',
        'nome',
        'descricao',
        'valor_estimado',
        'valor_contratado',
        'valor_executado',
        'percentual_execucao',
        'localizacao',
        'coordenadas_lat',
        'coordenadas_lng',
        'data_inicio_previsto',
        'data_fim_previsto',
        'data_inicio_real',
        'data_fim_real',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'valor_estimado' => 'decimal:2',
        'valor_contratado' => 'decimal:2',
        'valor_executado' => 'decimal:2',
        'percentual_execucao' => 'decimal:2',
        'coordenadas_lat' => 'decimal:8',
        'coordenadas_lng' => 'decimal:8',
        'data_inicio_previsto' => 'date',
        'data_fim_previsto' => 'date',
        'data_inicio_real' => 'date',
        'data_fim_real' => 'date',
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
     * Get the user who created this acao.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this acao.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
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
