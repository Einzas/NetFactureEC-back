<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'module',
    ];

    /**
     * Roles que tienen este permiso
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }

    /**
     * Employees que tienen este permiso directamente
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_permission')
            ->withPivot('granted')
            ->withTimestamps();
    }

    /**
     * Obtener permisos por módulo
     */
    public static function byModule(string $module)
    {
        return static::where('module', $module)->get();
    }

    /**
     * Buscar permiso por nombre
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }
}
