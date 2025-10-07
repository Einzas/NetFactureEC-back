<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Employee extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'company_id',
        'email',
        'password',
        'name',
        'identification',
        'phone',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * JWT Identifier
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWT Custom Claims
     */
    public function getJWTCustomClaims()
    {
        return [
            'company_id' => $this->company_id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

    /**
     * Empresa del empleado
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Roles del empleado
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'employee_role');
    }

    /**
     * Permisos directos del empleado
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'employee_permission')
            ->withPivot('granted')
            ->withTimestamps();
    }

    /**
     * Archivos subidos por el empleado
     */
    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(UploadedFile::class);
    }

    /**
     * Verificar si tiene un permiso
     */
    public function can($permission, $arguments = []): bool
    {
        // Verificar permisos directos revocados
        $directPermission = $this->permissions()
            ->where('name', $permission)
            ->first();
        
        if ($directPermission && !$directPermission->pivot->granted) {
            return false; // Permiso explícitamente revocado
        }

        // Verificar permisos directos otorgados
        if ($directPermission && $directPermission->pivot->granted) {
            return true;
        }

        // Verificar permisos a través de roles
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();
    }

    /**
     * Verificar si tiene alguno de los permisos
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar si tiene todos los permisos
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->can($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Verificar si tiene un rol
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Asignar rol
     */
    public function assignRole(...$roles): self
    {
        $roleModels = collect($roles)
            ->flatten()
            ->map(function ($role) {
                return $role instanceof Role 
                    ? $role 
                    : Role::where('name', $role)->first();
            })
            ->filter()
            ->pluck('id')
            ->all();

        $this->roles()->syncWithoutDetaching($roleModels);
        
        return $this;
    }

    /**
     * Remover rol
     */
    public function removeRole(...$roles): self
    {
        $roleModels = collect($roles)
            ->flatten()
            ->map(function ($role) {
                return $role instanceof Role 
                    ? $role 
                    : Role::where('name', $role)->first();
            })
            ->filter()
            ->pluck('id');

        $this->roles()->detach($roleModels);
        
        return $this;
    }

    /**
     * Otorgar permiso directo
     */
    public function givePermissionTo(string $permission): self
    {
        $permissionModel = Permission::findByName($permission);
        
        if ($permissionModel) {
            $this->permissions()->syncWithoutDetaching([
                $permissionModel->id => ['granted' => true]
            ]);
        }
        
        return $this;
    }

    /**
     * Revocar permiso directo
     */
    public function revokePermissionTo(string $permission): self
    {
        $permissionModel = Permission::findByName($permission);
        
        if ($permissionModel) {
            $this->permissions()->syncWithoutDetaching([
                $permissionModel->id => ['granted' => false]
            ]);
        }
        
        return $this;
    }

    /**
     * Obtener todos los permisos (roles + directos)
     */
    public function getAllPermissions()
    {
        // Permisos de roles
        $rolePermissions = Permission::whereHas('roles', function ($query) {
            $query->whereIn('roles.id', $this->roles->pluck('id'));
        })->get();

        // Permisos directos otorgados
        $directPermissions = $this->permissions()
            ->wherePivot('granted', true)
            ->get();

        // Permisos revocados
        $revokedPermissions = $this->permissions()
            ->wherePivot('granted', false)
            ->pluck('permissions.id');

        // Combinar y filtrar revocados
        return $rolePermissions
            ->merge($directPermissions)
            ->unique('id')
            ->reject(function ($permission) use ($revokedPermissions) {
                return $revokedPermissions->contains($permission->id);
            });
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
     * Scope: Por empresa
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope: Con autenticación SSO
     */
    public function scopeWithSSO($query)
    {
        return $query->whereNotNull('sso_provider');
    }
}
