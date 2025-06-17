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
        'data_acao'
    ];

    protected $casts = [
        'dados_alterados' => 'array',
        'data_acao' => 'datetime'
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
        return $this->belongsTo(ExecucaoEtapa::class);
    }

    /**
     * Usuário que realizou a ação
     */
    public function usuario()
    {
        return $this->belongsTo(User::class);
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

    public function scopeRecentes($query, $dias = 30)
    {
        return $query->where('data_acao', '>=', now()->subDays($dias));
    }

    // Métodos estáticos para criação automática

    /**
     * Registra uma mudança de status
     */
    public static function registrarMudancaStatus($execucaoEtapaId, $statusAnteriorId, $statusNovoId, $observacao = null)
    {
        return self::create([
            'execucao_etapa_id' => $execucaoEtapaId,
            'usuario_id' => auth()->id(),
            'status_anterior_id' => $statusAnteriorId,
            'status_novo_id' => $statusNovoId,
            'acao' => 'STATUS_ALTERADO',
            'descricao_acao' => 'Status alterado',
            'observacao' => $observacao,
            'ip_usuario' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data_acao' => now()
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
    public function getIconeAcaoAttribute()
    {
        $icones = [
            'ETAPA_INICIADA' => 'fas fa-play text-primary',
            'ETAPA_CONCLUIDA' => 'fas fa-check-circle text-success',
            'DOCUMENTO_ENVIADO' => 'fas fa-upload text-info',
            'DOCUMENTO_APROVADO' => 'fas fa-check text-success',
            'DOCUMENTO_REPROVADO' => 'fas fa-times text-danger',
            'STATUS_ALTERADO' => 'fas fa-exchange-alt text-warning',
            'OBSERVACAO_ADICIONADA' => 'fas fa-comment text-info'
        ];

        return $icones[$this->acao] ?? 'fas fa-circle text-secondary';
    }

    /**
     * Retorna cor da ação
     */
    public function getCorAcaoAttribute()
    {
        $cores = [
            'ETAPA_INICIADA' => 'primary',
            'ETAPA_CONCLUIDA' => 'success',
            'DOCUMENTO_ENVIADO' => 'info',
            'DOCUMENTO_APROVADO' => 'success',
            'DOCUMENTO_REPROVADO' => 'danger',
            'STATUS_ALTERADO' => 'warning',
            'OBSERVACAO_ADICIONADA' => 'info'
        ];

        return $cores[$this->acao] ?? 'secondary';
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
