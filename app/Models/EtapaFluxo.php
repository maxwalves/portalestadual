<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtapaFluxo extends Model
{
    use HasFactory;

    protected $table = 'etapa_fluxo';

    protected $fillable = [
        'tipo_fluxo_id',
        'modulo_id',
        'grupo_exigencia_id',
        'organizacao_solicitante_id',
        'organizacao_executora_id',
        'nome_etapa',
        'descricao_customizada',
        'ordem_execucao',
        'prazo_dias',
        'tipo_prazo',
        'is_obrigatoria',
        'permite_pular',
        'permite_retorno',
        'tipo_etapa',
        'configuracoes',
    ];

    protected $casts = [
        'configuracoes' => 'array',
        'is_obrigatoria' => 'boolean',
        'permite_pular' => 'boolean',
        'permite_retorno' => 'boolean',
    ];

    public function tipoFluxo()
    {
        return $this->belongsTo(TipoFluxo::class, 'tipo_fluxo_id');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'modulo_id');
    }

    public function grupoExigencia()
    {
        return $this->belongsTo(GrupoExigencia::class, 'grupo_exigencia_id');
    }

    public function organizacaoSolicitante()
    {
        return $this->belongsTo(Organizacao::class, 'organizacao_solicitante_id');
    }

    public function organizacaoExecutora()
    {
        return $this->belongsTo(Organizacao::class, 'organizacao_executora_id');
    }

    // Novos relacionamentos para as tabelas implementadas

    /**
     * Execuções desta etapa
     */
    public function execucaoEtapas()
    {
        return $this->hasMany(ExecucaoEtapa::class, 'etapa_fluxo_id');
    }

    /**
     * Transições onde esta etapa é origem
     */
    public function transicoesOrigem()
    {
        return $this->hasMany(TransicaoEtapa::class, 'etapa_fluxo_origem_id');
    }

    /**
     * Transições onde esta etapa é destino
     */
    public function transicoesDestino()
    {
        return $this->hasMany(TransicaoEtapa::class, 'etapa_fluxo_destino_id');
    }

    /**
     * Status disponíveis para esta etapa
     */
    public function statusOpcoes()
    {
        return $this->hasMany(EtapaStatusOpcao::class, 'etapa_fluxo_id');
    }

    /**
     * Status disponíveis (many-to-many através de pivot)
     */
    public function statusDisponiveis()
    {
        return $this->belongsToMany(Status::class, 'etapa_status_opcoes', 'etapa_fluxo_id', 'status_id')
                    ->withPivot(['ordem', 'is_padrao', 'mostra_para_responsavel', 'requer_justificativa'])
                    ->withTimestamps();
    }
}
