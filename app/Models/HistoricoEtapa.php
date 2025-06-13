<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class HistoricoEtapa extends Model
{
    use HasFactory;

    protected $table = 'historico_etapas';

    // Não tem updated_at, apenas created_at customizado como data_acao
    public $timestamps = false;

    protected $fillable = [
        'execucao_etapa_id',
        'usuario_id',
        'status_anterior_id',
        'status_novo_id',
        'acao',
        'descricao_acao',
        'observacao',
        'dados_alterados',
        'ip_usuario',
        'user_agent',
        'data_acao',
    ];

    protected $casts = [
        'dados_alterados' => 'array',
        'data_acao' => 'datetime',
    ];

    protected $dates = [
        'data_acao',
    ];

    // Relacionamentos

    /**
     * Execução de etapa relacionada
     */
    public function execucaoEtapa()
    {
        return $this->belongsTo(ExecucaoEtapa::class, 'execucao_etapa_id');
    }

    /**
     * Usuário que realizou a ação
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Status anterior
     */
    public function statusAnterior()
    {
        return $this->belongsTo(Status::class, 'status_anterior_id');
    }

    /**
     * Status novo
     */
    public function statusNovo()
    {
        return $this->belongsTo(Status::class, 'status_novo_id');
    }

    // Scopes

    /**
     * Scope por execução de etapa
     */
    public function scopePorExecucaoEtapa($query, $execucaoEtapaId)
    {
        return $query->where('execucao_etapa_id', $execucaoEtapaId);
    }

    /**
     * Scope por usuário
     */
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    /**
     * Scope por ação
     */
    public function scopePorAcao($query, $acao)
    {
        return $query->where('acao', $acao);
    }

    /**
     * Scope por período
     */
    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_acao', [$dataInicio, $dataFim]);
    }

    /**
     * Scope ordenado por data (mais recente primeiro)
     */
    public function scopeRecente($query)
    {
        return $query->orderBy('data_acao', 'desc');
    }

    // Métodos estáticos para criação automática

    /**
     * Registra uma mudança de status
     */
    public static function registrarMudancaStatus(
        ExecucaoEtapa $execucaoEtapa,
        ?Status $statusAnterior,
        Status $statusNovo,
        User $usuario,
        ?string $observacao = null
    ): self {
        return self::create([
            'execucao_etapa_id' => $execucaoEtapa->id,
            'usuario_id' => $usuario->id,
            'status_anterior_id' => $statusAnterior?->id,
            'status_novo_id' => $statusNovo->id,
            'acao' => 'MUDANCA_STATUS',
            'descricao_acao' => "Status alterado de '{$statusAnterior?->nome}' para '{$statusNovo->nome}'",
            'observacao' => $observacao,
            'dados_alterados' => [
                'status_anterior' => $statusAnterior?->toArray(),
                'status_novo' => $statusNovo->toArray(),
            ],
            'ip_usuario' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'data_acao' => now(),
        ]);
    }

    /**
     * Registra envio de documento
     */
    public static function registrarEnvioDocumento(
        ExecucaoEtapa $execucaoEtapa,
        User $usuario,
        array $documentoInfo,
        ?string $observacao = null
    ): self {
        return self::create([
            'execucao_etapa_id' => $execucaoEtapa->id,
            'usuario_id' => $usuario->id,
            'acao' => 'ENVIO_DOCUMENTO',
            'descricao_acao' => "Documento enviado: {$documentoInfo['nome']}",
            'observacao' => $observacao,
            'dados_alterados' => $documentoInfo,
            'ip_usuario' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'data_acao' => now(),
        ]);
    }

    /**
     * Registra início de etapa
     */
    public static function registrarInicioEtapa(
        ExecucaoEtapa $execucaoEtapa,
        User $usuario,
        ?string $observacao = null
    ): self {
        return self::create([
            'execucao_etapa_id' => $execucaoEtapa->id,
            'usuario_id' => $usuario->id,
            'acao' => 'INICIO_ETAPA',
            'descricao_acao' => 'Etapa iniciada',
            'observacao' => $observacao,
            'ip_usuario' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'data_acao' => now(),
        ]);
    }

    // Métodos auxiliares

    /**
     * Retorna ícone da ação
     */
    public function getIconeAcao(): string
    {
        return match($this->acao) {
            'MUDANCA_STATUS' => 'fa-exchange-alt',
            'ENVIO_DOCUMENTO' => 'fa-file-upload',
            'INICIO_ETAPA' => 'fa-play',
            'CONCLUSAO_ETAPA' => 'fa-check',
            'APROVACAO' => 'fa-thumbs-up',
            'REPROVACAO' => 'fa-thumbs-down',
            default => 'fa-history',
        };
    }

    /**
     * Retorna cor da ação
     */
    public function getCorAcao(): string
    {
        return match($this->acao) {
            'MUDANCA_STATUS' => 'primary',
            'ENVIO_DOCUMENTO' => 'info',
            'INICIO_ETAPA' => 'success',
            'CONCLUSAO_ETAPA' => 'success',
            'APROVACAO' => 'success',
            'REPROVACAO' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Retorna resumo da ação para exibição
     */
    public function getResumoAcao(): string
    {
        $resumo = $this->descricao_acao ?: $this->acao;
        
        if ($this->observacao) {
            $resumo .= " - {$this->observacao}";
        }
        
        return $resumo;
    }
}
