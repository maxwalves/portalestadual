<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organizacao extends Model
{
    protected $fillable = [
        'nome',
        'tipo',
        'cnpj',
        'email',
        'telefone',
        'endereco',
        'responsavel_nome',
        'responsavel_cargo',
        'is_ativo',
        'created_by',
        'updated_by'
    ];
    protected $table = 'organizacao';
    protected $primaryKey = 'id';

    /**
     * Get the termos de adesão for the organization.
     */
    public function termosAdesao()
    {
        return $this->hasMany(TermoAdesao::class, 'organizacao_id');
    }

    /**
     * Get the users for the organization.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'organizacao_id');
    }
}
