<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class Notificacao extends Model
{
    use HasFactory;

    protected $table = 'notificacoes';

    protected $fillable = [
        'execucao_etapa_id',
        'usuario_destinatario_id',
        'tipo_notificacao_id',
        'canal',
        'assunto',
        'mensagem',
        'prioridade',
        'data_envio',
        'data_leitura',
        'data_expiracao',
        'status_envio',
        'tentativas',
        'erro_mensagem',
        'metadata',
    ];

    protected $casts = [
        'data_envio' => 'datetime',
        'data_leitura' => 'datetime',
        'data_expiracao' => 'datetime',
        'tentativas' => 'integer',
        'metadata' => 'array',
    ];

    protected $dates = [
        'data_envio',
        'data_leitura',
        'data_expiracao',
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
     * Usuário destinatário
     */
    public function usuarioDestinatario()
    {
        return $this->belongsTo(User::class, 'usuario_destinatario_id');
    }

    /**
     * Tipo de notificação
     */
    public function tipoNotificacao()
    {
        return $this->belongsTo(TipoNotificacao::class, 'tipo_notificacao_id');
    }

    // Scopes

    /**
     * Scope para notificações não lidas
     */
    public function scopeNaoLidas($query)
    {
        return $query->whereNull('data_leitura');
    }

    /**
     * Scope para notificações lidas
     */
    public function scopeLidas($query)
    {
        return $query->whereNotNull('data_leitura');
    }

    /**
     * Scope por status de envio
     */
    public function scopePorStatusEnvio($query, $status)
    {
        return $query->where('status_envio', $status);
    }

    /**
     * Scope por canal
     */
    public function scopePorCanal($query, $canal)
    {
        return $query->where('canal', $canal);
    }

    /**
     * Scope por prioridade
     */
    public function scopePorPrioridade($query, $prioridade)
    {
        return $query->where('prioridade', $prioridade);
    }

    /**
     * Scope por usuário
     */
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_destinatario_id', $usuarioId);
    }

    /**
     * Scope para notificações pendentes de envio
     */
    public function scopePendentesEnvio($query)
    {
        return $query->where('status_envio', 'PENDENTE');
    }

    /**
     * Scope para notificações expiradas
     */
    public function scopeExpiradas($query)
    {
        return $query->where('data_expiracao', '<', now());
    }

    /**
     * Scope ordenado por data (mais recente primeiro)
     */
    public function scopeRecente($query)
    {
        return $query->orderBy('data_envio', 'desc');
    }

    // Métodos de estado

    /**
     * Verifica se a notificação foi lida
     */
    public function isLida(): bool
    {
        return !is_null($this->data_leitura);
    }

    /**
     * Verifica se a notificação está expirada
     */
    public function isExpirada(): bool
    {
        return $this->data_expiracao && $this->data_expiracao->isPast();
    }

    /**
     * Verifica se a notificação está pendente
     */
    public function isPendente(): bool
    {
        return $this->status_envio === 'PENDENTE';
    }

    /**
     * Verifica se a notificação foi enviada com sucesso
     */
    public function isEnviada(): bool
    {
        return $this->status_envio === 'ENVIADO';
    }

    // Métodos de ação

    /**
     * Marca a notificação como lida
     */
    public function marcarComoLida(): bool
    {
        if (!$this->isLida()) {
            $this->data_leitura = now();
            $this->status_envio = 'LIDO';
            return $this->save();
        }
        return true;
    }

    /**
     * Marca como enviada
     */
    public function marcarComoEnviada(): bool
    {
        $this->status_envio = 'ENVIADO';
        return $this->save();
    }

    /**
     * Marca como erro e registra mensagem
     */
    public function marcarComoErro(string $mensagemErro): bool
    {
        $this->status_envio = 'ERRO';
        $this->erro_mensagem = $mensagemErro;
        $this->tentativas = $this->tentativas + 1;
        return $this->save();
    }

    /**
     * Marca como expirada
     */
    public function marcarComoExpirada(): bool
    {
        $this->status_envio = 'EXPIRADO';
        return $this->save();
    }

    // Métodos estáticos para criação

    /**
     * Cria notificação de sistema
     */
    public static function criarNotificacaoSistema(
        ExecucaoEtapa $execucaoEtapa,
        User $destinatario,
        TipoNotificacao $tipo,
        array $variaveis = [],
        string $prioridade = 'MEDIA'
    ): self {
        $mensagem = $tipo->gerarMensagemSistema($variaveis);
        
        return self::create([
            'execucao_etapa_id' => $execucaoEtapa->id,
            'usuario_destinatario_id' => $destinatario->id,
            'tipo_notificacao_id' => $tipo->id,
            'canal' => 'SISTEMA',
            'mensagem' => $mensagem,
            'prioridade' => $prioridade,
            'data_envio' => now(),
            'status_envio' => 'ENVIADO',
            'metadata' => [
                'variaveis' => $variaveis,
                'ip_origem' => Request::ip(),
            ],
        ]);
    }

    /**
     * Cria notificação de email
     */
    public static function criarNotificacaoEmail(
        ExecucaoEtapa $execucaoEtapa,
        User $destinatario,
        TipoNotificacao $tipo,
        string $assunto,
        array $variaveis = [],
        string $prioridade = 'MEDIA'
    ): self {
        $mensagem = $tipo->gerarEmail($variaveis);
        
        return self::create([
            'execucao_etapa_id' => $execucaoEtapa->id,
            'usuario_destinatario_id' => $destinatario->id,
            'tipo_notificacao_id' => $tipo->id,
            'canal' => 'EMAIL',
            'assunto' => $assunto,
            'mensagem' => $mensagem,
            'prioridade' => $prioridade,
            'data_envio' => now(),
            'status_envio' => 'PENDENTE',
            'metadata' => [
                'variaveis' => $variaveis,
                'ip_origem' => Request::ip(),
            ],
        ]);
    }

    // Métodos auxiliares

    /**
     * Retorna cor da prioridade
     */
    public function getCorPrioridade(): string
    {
        return match($this->prioridade) {
            'BAIXA' => 'secondary',
            'MEDIA' => 'primary',
            'ALTA' => 'warning',
            'URGENTE' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Retorna ícone da prioridade
     */
    public function getIconePrioridade(): string
    {
        return match($this->prioridade) {
            'BAIXA' => 'fa-arrow-down',
            'MEDIA' => 'fa-minus',
            'ALTA' => 'fa-arrow-up',
            'URGENTE' => 'fa-exclamation-triangle',
            default => 'fa-bell',
        };
    }

    /**
     * Retorna ícone do canal
     */
    public function getIconeCanal(): string
    {
        return match($this->canal) {
            'EMAIL' => 'fa-envelope',
            'SISTEMA' => 'fa-bell',
            'SMS' => 'fa-sms',
            'WHATSAPP' => 'fa-whatsapp',
            default => 'fa-bell',
        };
    }

    /**
     * Retorna cor do status
     */
    public function getCorStatus(): string
    {
        return match($this->status_envio) {
            'PENDENTE' => 'warning',
            'ENVIADO' => 'success',
            'ERRO' => 'danger',
            'LIDO' => 'info',
            'EXPIRADO' => 'secondary',
            default => 'secondary',
        };
    }
}
