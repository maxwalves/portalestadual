<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EtapaStatusOpcao extends Model
{
    protected $table = 'etapa_status_opcoes';
    
    // Configurar para chave primária composta
    protected $primaryKey = ['etapa_fluxo_id', 'status_id'];
    public $incrementing = false;
    public $timestamps = false;
    
    protected $fillable = [
        'etapa_fluxo_id',
        'status_id',
        'ordem',
        'is_padrao',
        'mostra_para_responsavel',
        'requer_justificativa',
    ];

    protected $casts = [
        'is_padrao' => 'boolean',
        'mostra_para_responsavel' => 'boolean',
        'requer_justificativa' => 'boolean',
        'ordem' => 'integer',
    ];

    /**
     * Override para o método getKeyName() para chave primária composta  
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Override para o método getKey() para chave primária composta
     */
    public function getKey()
    {
        if (is_array($this->getKeyName())) {
            $key = [];
            foreach ($this->getKeyName() as $keyName) {
                $key[$keyName] = $this->getAttribute($keyName);
            }
            return $key;
        }
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Override para o método getKeyForSaveQuery() para chave primária composta
     */
    protected function getKeyForSaveQuery()
    {
        if (is_array($this->getKeyName())) {
            $key = [];
            foreach ($this->getKeyName() as $keyName) {
                $key[$keyName] = $this->original[$keyName] ?? $this->getAttribute($keyName);
            }
            return $key;
        }
        return $this->original[$this->getKeyName()] ?? $this->getKey();
    }

    /**
     * Relacionamento com EtapaFluxo
     */
    public function etapaFluxo(): BelongsTo
    {
        return $this->belongsTo(EtapaFluxo::class);
    }

    /**
     * Relacionamento com Status
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Buscar opções de status disponíveis para uma etapa e usuário
     */
    public static function getOpcoesDisponiveis($etapaFluxoId, $usuarioOrganizacaoId)
    {
        return static::where('etapa_fluxo_id', $etapaFluxoId)
            ->where('mostra_para_responsavel', true)
            ->with(['status', 'etapaFluxo'])
            ->whereHas('etapaFluxo', function ($query) use ($usuarioOrganizacaoId) {
                $query->where(function($q) use ($usuarioOrganizacaoId) {
                    $q->where('organizacao_solicitante_id', $usuarioOrganizacaoId)
                      ->orWhere('organizacao_executora_id', $usuarioOrganizacaoId);
                });
            })
            ->orderBy('ordem')
            ->get();
    }

    /**
     * Criar ou atualizar uma opção de status
     * Para chave primária composta, usamos sempre updateOrInsert do DB
     */
    public static function createOrUpdate($etapaFluxoId, $statusId, $dados)
    {
        $conditions = [
            'etapa_fluxo_id' => $etapaFluxoId,
            'status_id' => $statusId
        ];

        $values = array_merge($dados, $conditions);

        // Usar updateOrInsert que funciona bem com chaves compostas
        \DB::table('etapa_status_opcoes')->updateOrInsert($conditions, $values);

        // Retornar o registro atualizado/criado
        return static::where('etapa_fluxo_id', $etapaFluxoId)
            ->where('status_id', $statusId)
            ->first();
    }

    /**
     * Verificar se um status é válido para uma etapa
     */
    public static function isStatusValido($etapaFluxoId, $statusId)
    {
        return static::where('etapa_fluxo_id', $etapaFluxoId)
            ->where('status_id', $statusId)
            ->where('mostra_para_responsavel', true)
            ->exists();
    }

    /**
     * Verificar se um status requer justificativa
     */
    public static function requerJustificativaStatus($etapaFluxoId, $statusId)
    {
        $opcao = static::where('etapa_fluxo_id', $etapaFluxoId)
            ->where('status_id', $statusId)
            ->first();
            
        return $opcao ? $opcao->requer_justificativa : false;
    }
} 