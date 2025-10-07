<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

    /**
     * Empresas del owner
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'owner_id');
    }

    /**
     * Archivos subidos por el owner
     */
    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(UploadedFile::class);
    }

    /**
     * Verificar si es superadministrador
     */
    public function isSuperAdmin(): bool
    {
        return $this->type === 'superadmin';
    }

    /**
     * Verificar si es owner
     */
    public function isOwner(): bool
    {
        return $this->type === 'owner';
    }

    /**
     * Actualizar último login
     */
    public function updateLastLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? request()->ip(),
        ]);
    }

    /**
     * Scope: Solo activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get active companies.
     */
    public function activeCompanies()
    {
        return $this->companies()->where('is_active', true);
    }

    /**
     * Scope: Solo superadmins
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('type', 'superadmin');
    }

    /**
     * Scope: Solo owners
     */
    public function scopeOwners($query)
    {
        return $query->where('type', 'owner');
    }
}
