<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoNotificacao extends Model
{
    use HasFactory;

    protected $table = 'tipo_notificacoes';

    protected $fillable = [
        'codigo',
        'nome',
        'descricao',
        'template_email',
        'template_sms',
        'template_sistema',
        'variaveis_disponiveis',
        'is_ativo',
    ];

    protected $casts = [
        'variaveis_disponiveis' => 'array',
        'is_ativo' => 'boolean',
    ];

    // Relacionamentos

    /**
     * Notificações deste tipo
     */
    public function notificacoes()
    {
        return $this->hasMany(Notificacao::class, 'tipo_notificacao_id');
    }

    // Scopes

    /**
     * Scope para tipos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('is_ativo', true);
    }

    /**
     * Scope por código
     */
    public function scopePorCodigo($query, $codigo)
    {
        return $query->where('codigo', $codigo);
    }

    // Métodos auxiliares

    /**
     * Verifica se o tipo está ativo
     */
    public function isAtivo(): bool
    {
        return $this->is_ativo;
    }

    /**
     * Processa template substituindo variáveis
     */
    public function processarTemplate(string $template, array $variaveis = []): string
    {
        $texto = $template;
        
        foreach ($variaveis as $chave => $valor) {
            $placeholder = "{{$chave}}";
            $texto = str_replace($placeholder, $valor, $texto);
        }
        
        return $texto;
    }

    /**
     * Gera mensagem do sistema processada
     */
    public function gerarMensagemSistema(array $variaveis = []): string
    {
        return $this->processarTemplate($this->template_sistema ?: 'Notificação: {etapa_nome}', $variaveis);
    }

    /**
     * Gera email processado
     */
    public function gerarEmail(array $variaveis = []): string
    {
        return $this->processarTemplate($this->template_email ?: $this->template_sistema ?: 'Notificação', $variaveis);
    }

    /**
     * Gera SMS processado
     */
    public function gerarSms(array $variaveis = []): string
    {
        return $this->processarTemplate($this->template_sms ?: $this->template_sistema ?: 'Notificação', $variaveis);
    }

    /**
     * Valida se todas as variáveis necessárias foram fornecidas
     */
    public function validarVariaveis(array $variaveis): array
    {
        $variaveisNecessarias = $this->variaveis_disponiveis ?: [];
        $faltando = [];
        
        foreach ($variaveisNecessarias as $variavel) {
            if (!array_key_exists($variavel, $variaveis)) {
                $faltando[] = $variavel;
            }
        }
        
        return $faltando;
    }

    /**
     * Retorna variáveis disponíveis formatadas
     */
    public function getVariaveisFormatadas(): string
    {
        if (!$this->variaveis_disponiveis) {
            return 'Nenhuma variável definida';
        }
        
        return implode(', ', array_map(fn($var) => "{{$var}}", $this->variaveis_disponiveis));
    }

    // Métodos estáticos para busca rápida

    /**
     * Busca tipo por código
     */
    public static function buscarPorCodigo(string $codigo): ?self
    {
        return self::where('codigo', $codigo)->first();
    }

    /**
     * Retorna tipos ativos ordenados
     */
    public static function ativosOrdenados()
    {
        return self::where('is_ativo', true)->orderBy('nome')->get();
    }
}
