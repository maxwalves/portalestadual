<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = [
        'execucao_etapa_id',
        'template_documento_id',
        'tipo_documento_id',
        'usuario_upload_id',
        'nome_arquivo',
        'nome_arquivo_sistema',
        'tamanho_bytes',
        'mime_type',
        'hash_arquivo',
        'caminho_storage',
        'versao',
        'documento_pai_id',
        'status_documento',
        'is_assinado',
        'data_upload',
        'data_validade',
        'observacoes',
        'motivo_reprovacao',
        'data_aprovacao',
        'usuario_aprovacao_id',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_assinado' => 'boolean',
        'data_upload' => 'datetime',
        'data_validade' => 'date',
        'data_aprovacao' => 'datetime',
        'metadata' => 'array',
        'tamanho_bytes' => 'integer',
        'versao' => 'integer',
    ];

    /**
     * Status possíveis para documentos
     */
    const STATUS_PENDENTE = 'PENDENTE';
    const STATUS_EM_ANALISE = 'EM_ANALISE';
    const STATUS_APROVADO = 'APROVADO';
    const STATUS_REPROVADO = 'REPROVADO';
    const STATUS_EXPIRADO = 'EXPIRADO';

    /**
     * Relacionamento com execução de etapa
     */
    public function execucaoEtapa(): BelongsTo
    {
        return $this->belongsTo(ExecucaoEtapa::class);
    }

    /**
     * Relacionamento com template de documento
     */
    public function templateDocumento(): BelongsTo
    {
        return $this->belongsTo(TemplateDocumento::class);
    }

    /**
     * Relacionamento com tipo de documento
     */
    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class);
    }

    /**
     * Relacionamento com usuário que fez upload
     */
    public function usuarioUpload(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_upload_id');
    }

    /**
     * Relacionamento com usuário que aprovou/reprovou
     */
    public function usuarioAprovacao(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_aprovacao_id');
    }

    /**
     * Relacionamento com documento pai (para versionamento)
     */
    public function documentoPai(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'documento_pai_id');
    }

    /**
     * Relacionamento com versões filhas
     */
    public function versoes(): HasMany
    {
        return $this->hasMany(Documento::class, 'documento_pai_id')->orderBy('versao');
    }

    /**
     * Scope para documentos aprovados
     */
    public function scopeAprovados($query)
    {
        return $query->where('status_documento', self::STATUS_APROVADO);
    }

    /**
     * Scope para documentos pendentes
     */
    public function scopePendentes($query)
    {
        return $query->where('status_documento', self::STATUS_PENDENTE);
    }

    /**
     * Scope para documentos em análise
     */
    public function scopeEmAnalise($query)
    {
        return $query->where('status_documento', self::STATUS_EM_ANALISE);
    }

    /**
     * Scope para documentos reprovados
     */
    public function scopeReprovados($query)
    {
        return $query->where('status_documento', self::STATUS_REPROVADO);
    }

    /**
     * Scope para filtrar por tipo de documento
     */
    public function scopeTipoDocumento($query, $tipoDocumentoId)
    {
        return $query->where('tipo_documento_id', $tipoDocumentoId);
    }

    /**
     * Scope para documentos assinados
     */
    public function scopeAssinados($query)
    {
        return $query->where('is_assinado', true);
    }

    /**
     * Scope para documentos não assinados
     */
    public function scopeNaoAssinados($query)
    {
        return $query->where('is_assinado', false);
    }

    /**
     * Scope para documentos válidos (não expirados)
     */
    public function scopeValidos($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('data_validade')
              ->orWhere('data_validade', '>=', now()->format('Y-m-d'));
        });
    }

    /**
     * Scope para documentos expirados
     */
    public function scopeExpirados($query)
    {
        return $query->where('data_validade', '<', now()->format('Y-m-d'));
    }

    /**
     * Verifica se o documento está aprovado
     */
    public function isAprovado(): bool
    {
        return $this->status_documento === self::STATUS_APROVADO;
    }

    /**
     * Verifica se o documento está pendente
     */
    public function isPendente(): bool
    {
        return $this->status_documento === self::STATUS_PENDENTE;
    }

    /**
     * Verifica se o documento está em análise
     */
    public function isEmAnalise(): bool
    {
        return $this->status_documento === self::STATUS_EM_ANALISE;
    }

    /**
     * Verifica se o documento está reprovado
     */
    public function isReprovado(): bool
    {
        return $this->status_documento === self::STATUS_REPROVADO;
    }

    /**
     * Verifica se o documento está expirado
     */
    public function isExpirado(): bool
    {
        if (is_null($this->data_validade)) {
            return false;
        }

        return $this->data_validade < now()->format('Y-m-d');
    }

    /**
     * Obtém a cor do badge do status
     */
    public function getCorStatus(): string
    {
        $cores = [
            self::STATUS_PENDENTE => 'warning',
            self::STATUS_EM_ANALISE => 'info',
            self::STATUS_APROVADO => 'success',
            self::STATUS_REPROVADO => 'danger',
            self::STATUS_EXPIRADO => 'dark',
        ];

        return $cores[$this->status_documento] ?? 'secondary';
    }

    /**
     * Obtém o ícone do status
     */
    public function getIconeStatus(): string
    {
        $icones = [
            self::STATUS_PENDENTE => 'fas fa-clock',
            self::STATUS_EM_ANALISE => 'fas fa-search',
            self::STATUS_APROVADO => 'fas fa-check-circle',
            self::STATUS_REPROVADO => 'fas fa-times-circle',
            self::STATUS_EXPIRADO => 'fas fa-calendar-times',
        ];

        return $icones[$this->status_documento] ?? 'fas fa-file';
    }

    /**
     * Formata o tamanho do arquivo
     */
    public function getTamanhoFormatado(): string
    {
        $bytes = $this->tamanho_bytes;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }

    /**
     * Obtém a extensão do arquivo
     */
    public function getExtensao(): string
    {
        return strtolower(pathinfo($this->nome_arquivo, PATHINFO_EXTENSION));
    }

    /**
     * Verifica se o arquivo existe no storage
     */
    public function arquivoExiste(): bool
    {
        return Storage::exists($this->caminho_storage);
    }

    /**
     * Obtém o URL para download do arquivo
     */
    public function getUrlDownload(): string
    {
        return route('documentos.download', $this->id);
    }

    /**
     * Verifica se é uma nova versão de documento
     */
    public function isNovaVersao(): bool
    {
        return !is_null($this->documento_pai_id);
    }

    /**
     * Obtém a versão mais recente deste documento
     */
    public function getVersaoMaisRecente(): ?Documento
    {
        if ($this->isNovaVersao()) {
            return $this->documentoPai->versoes()->orderBy('versao', 'desc')->first();
        }

        return $this->versoes()->orderBy('versao', 'desc')->first() ?? $this;
    }

    /**
     * Verifica se precisa de assinatura
     */
    public function precisaAssinatura(): bool
    {
        return $this->tipoDocumento && $this->tipoDocumento->requer_assinatura && !$this->is_assinado;
    }

    /**
     * Obtém informações de metadados específicos
     */
    public function getMetadata(string $chave, $default = null)
    {
        return $this->metadata[$chave] ?? $default;
    }

    /**
     * Define metadados específicos
     */
    public function setMetadata(string $chave, $valor): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$chave] = $valor;
        $this->metadata = $metadata;
    }

    /**
     * Verifica integridade do arquivo através do hash
     */
    public function verificarIntegridade(): bool
    {
        if (!$this->arquivoExiste()) {
            return false;
        }

        $hashAtual = hash_file('sha256', Storage::path($this->caminho_storage));
        return $hashAtual === $this->hash_arquivo;
    }
} 