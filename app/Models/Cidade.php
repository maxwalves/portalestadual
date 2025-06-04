<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    use HasFactory;

    protected $fillable = [
        'ibge_id',
        'nome',
        'estado',
        'nome_normalizado'
    ];

    /**
     * Normalizar texto removendo acentos e caracteres especiais
     */
    public static function normalizeText($text)
    {
        $text = strtolower($text);
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        return trim($text);
    }

    /**
     * Buscar cidades por nome com autocomplete
     */
    public static function searchForAutocomplete($term, $limit = 10)
    {
        $normalizedTerm = self::normalizeText($term);
        
        return self::where('estado', 'PR')
            ->where(function ($query) use ($term, $normalizedTerm) {
                $query->where('nome', 'like', "%{$term}%")
                      ->orWhere('nome_normalizado', 'like', "%{$normalizedTerm}%");
            })
            ->orderBy('nome')
            ->limit($limit)
            ->get()
            ->map(function ($cidade) {
                return [
                    'id' => $cidade->id,
                    'nome' => $cidade->nome,
                    'label' => $cidade->nome . ' - PR',
                    'value' => $cidade->nome
                ];
            });
    }

    /**
     * Mutator para normalizar o nome ao salvar
     */
    public function setNomeAttribute($value)
    {
        $this->attributes['nome'] = $value;
        $this->attributes['nome_normalizado'] = self::normalizeText($value);
    }
}
