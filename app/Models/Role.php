<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'display_name',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Empresa a la que pertenece (null = rol global)
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Permisos del rol
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    /**
     * Employees que tienen este rol
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_role');
    }

    /**
     * Verificar si es rol del sistema (no editable)
     */
    public function isSystem(): bool
    {
        return $this->is_system;
    }

    /**
     * Verificar si es rol global
     */
    public function isGlobal(): bool
    {
        return is_null($this->company_id);
    }

    /**
     * Asignar permisos al rol
     */
    public function givePermissionTo(...$permissions): self
    {
        $permissionModels = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                return $permission instanceof Permission 
                    ? $permission 
                    : Permission::findByName($permission);
            })
            ->filter()
            ->pluck('id');

        $this->permissions()->syncWithoutDetaching($permissionModels);
        
        return $this;
    }

    /**
     * Sincronizar permisos del rol (reemplaza los existentes)
     */
    public function syncPermissions($permissions): self
    {
        if ($permissions instanceof \Illuminate\Support\Collection) {
            $permissionIds = $permissions->pluck('id')->all();
        } elseif (is_array($permissions)) {
            if (empty($permissions)) {
                $permissionIds = [];
            } elseif (is_numeric($permissions[0])) {
                // Si es un array de IDs
                $permissionIds = $permissions;
            } else {
                // Si es un array de modelos
                $permissionIds = collect($permissions)->pluck('id')->all();
            }
        } else {
            $permissionIds = [$permissions];
        }

        $this->permissions()->sync($permissionIds);
        
        return $this;
    }

    /**
     * Revocar permisos del rol
     */
    public function revokePermissionTo(...$permissions): self
    {
        $permissionModels = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                return $permission instanceof Permission 
                    ? $permission 
                    : Permission::findByName($permission);
            })
            ->filter()
            ->pluck('id');

        $this->permissions()->detach($permissionModels);
        
        return $this;
    }

    /**
     * Verificar si tiene un permiso
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Roles del sistema
     */
    public static function systemRoles()
    {
        return static::where('is_system', true)->get();
    }

    /**
     * Roles de una empresa
     */
    public static function forCompany(int $companyId)
    {
        return static::where('company_id', $companyId)->get();
    }
}
