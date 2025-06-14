<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

class User extends Authenticatable implements LdapAuthenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;
    use AuthenticatesWithLdap;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'manager',
        'department',
        'employeeNumber',
        'dataAssinaturaTermo',
        'nomeSetor',
        'isExterno',
        'orgao',
        'cpf',
        'telefone',
        'empresa',
        'cargo',
        'data_nascimento',
        'status',
        'active',
        'guid',
        'domain',
        'organizacao_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'active'            => 'boolean',
            'isExterno'         => 'boolean',
        ];
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getLdapAuthIdentifierName()
    {
        return 'username';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getLdapAuthIdentifier()
    {
        return $this->{$this->getLdapAuthIdentifierName()};
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getLdapAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the LDAP domain for the user.
     *
     * @return string|null
     */
    public function getLdapDomain(): ?string
    {
        return $this->domain ?? null;
    }

    /**
     * Get the LDAP GUID for the user.
     *
     * @return string|null
     */
    public function getLdapGuid(): ?string
    {
        return $this->guid ?? null;
    }

    /**
     * Get the organizacao that owns the user.
     */
    public function organizacao()
    {
        return $this->belongsTo(Organizacao::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        
        // Se for array, converter para Collection e verificar interseção
        if (is_array($role)) {
            return collect($role)->intersect($this->roles->pluck('name'))->count() > 0;
        }
        
        // Se for Collection, verificar interseção
        return ! ! $role->intersect($this->roles->pluck('name'))->count();
    }

    public function hasAnyRole($roles): bool
    {
        if (is_string($roles)) {
            return $this->hasRole($roles);
        }
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPassword($token));
    }
}
