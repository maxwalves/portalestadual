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
        'ordem_execucao',
        'nome_etapa',
        'descricao_customizada',
        'prazo_dias',
        'tipo_prazo',
        'is_obrigatoria',
        'permite_pular',
        'permite_retorno',
        'tipo_etapa',
        'configuracoes'
    ];

    protected $casts = [
        'configuracoes' => 'array',
        'is_obrigatoria' => 'boolean',
        'permite_pular' => 'boolean',
        'permite_retorno' => 'boolean'
    ];

    // ===== RELACIONAMENTOS =====

    public function tipoFluxo()
    {
        return $this->belongsTo(TipoFluxo::class);
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class);
    }

    public function grupoExigencia()
    {
        return $this->belongsTo(GrupoExigencia::class);
    }

    public function organizacaoSolicitante()
    {
        return $this->belongsTo(Organizacao::class, 'organizacao_solicitante_id');
    }

    public function organizacaoExecutora()
    {
        return $this->belongsTo(Organizacao::class, 'organizacao_executora_id');
    }

    public function execucoes()
    {
        return $this->hasMany(ExecucaoEtapa::class);
    }

    public function execucoesEtapa()
    {
        return $this->hasMany(ExecucaoEtapa::class);
    }

    public function statusOpcoes()
    {
        return $this->belongsToMany(Status::class, 'etapa_status_opcoes')
                    ->withPivot('ordem', 'is_padrao', 'mostra_para_responsavel', 'requer_justificativa')
                    ->orderBy('etapa_status_opcoes.ordem');
    }

    public function etapaStatusOpcoes()
    {
        return $this->hasMany(EtapaStatusOpcao::class);
    }

    /**
     * Transições que partem desta etapa
     */
    public function transicoesOrigem()
    {
        return $this->hasMany(TransicaoEtapa::class, 'etapa_fluxo_origem_id');
    }

    /**
     * Transições que chegam nesta etapa
     */
    public function transicoesDestino()
    {
        return $this->hasMany(TransicaoEtapa::class, 'etapa_fluxo_destino_id');
    }

    // ===== SCOPES =====

    public function scopeObrigatorias($query)
    {
        return $query->where('is_obrigatoria', true);
    }

    public function scopeSequenciais($query)
    {
        return $query->where('tipo_etapa', 'SEQUENCIAL');
    }

    public function scopeCondicionais($query)
    {
        return $query->where('tipo_etapa', 'CONDICIONAL');
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('ordem_execucao');
    }

    // ===== ACCESSORS =====

    public function getPodeSerIniciadaAttribute()
    {
        // Se for a primeira etapa, sempre pode ser iniciada
        if ($this->ordem_execucao <= 1) {
            return true;
        }

        // Verificar se etapa anterior foi concluída
        $etapaAnterior = self::where('tipo_fluxo_id', $this->tipo_fluxo_id)
            ->where('ordem_execucao', $this->ordem_execucao - 1)
            ->first();

        if (!$etapaAnterior) {
            return true;
        }

        // Verificar se existe execução concluída da etapa anterior
        // Isso seria verificado no contexto de uma ação específica
        return true; // Por enquanto, sempre permite
    }

    public function getProximaEtapaAttribute()
    {
        return self::where('tipo_fluxo_id', $this->tipo_fluxo_id)
            ->where('ordem_execucao', '>', $this->ordem_execucao)
            ->orderBy('ordem_execucao')
            ->first();
    }

    public function getEtapaAnteriorAttribute()
    {
        return self::where('tipo_fluxo_id', $this->tipo_fluxo_id)
            ->where('ordem_execucao', '<', $this->ordem_execucao)
            ->orderBy('ordem_execucao', 'desc')
            ->first();
    }

    public function getTemDocumentosObrigatoriosAttribute()
    {
        return $this->grupoExigencia && 
               $this->grupoExigencia->templatesDocumento()
                   ->wherePivot('is_obrigatorio', true)
                   ->exists();
    }

    // ===== MÉTODOS =====

    public function podeSerExecutadaPor($organizacaoId)
    {
        return $this->organizacao_solicitante_id === $organizacaoId || 
               $this->organizacao_executora_id === $organizacaoId;
    }

    public function getStatusPadrao()
    {
        return $this->statusOpcoes()
            ->wherePivot('is_padrao', true)
            ->first() ?? Status::where('codigo', 'PENDENTE')->first();
    }

    public function getStatusDisponiveis($paraResponsavel = true)
    {
        $query = $this->statusOpcoes();
        
        if ($paraResponsavel) {
            $query->wherePivot('mostra_para_responsavel', true);
        }

        return $query->orderBy('etapa_status_opcoes.ordem')->get();
    }

    public function calcularDataPrazo($dataInicio = null)
    {
        $dataInicio = $dataInicio ?? now();
        
        if ($this->tipo_prazo === 'UTEIS') {
            return $dataInicio->addWeekdays($this->prazo_dias);
        } else {
            return $dataInicio->addDays($this->prazo_dias);
        }
    }

    public function validarTransicao($statusOrigemId, $statusDestinoId)
    {
        // Aqui seria implementada a lógica de validação de transições
        // baseada na tabela transicao_etapa
        return true; // Por enquanto, permite todas as transições
    }
}
