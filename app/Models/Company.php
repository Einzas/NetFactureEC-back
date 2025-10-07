<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'ruc',
        'business_name',
        'trade_name',
        'address',
        'city',
        'province',
        'phone',
        'email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Dueño de la empresa
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Empleados de la empresa
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Roles personalizados de la empresa
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Archivos de la empresa
     */
    public function files(): HasMany
    {
        return $this->hasMany(UploadedFile::class);
    }

    /**
     * Verificar si la empresa está activa
     */
    public function isActive(): bool
    {
        return $this->is_active && 
               ($this->subscription_expires_at === null || 
                $this->subscription_expires_at->isFuture());
    }

    /**
     * Verificar si la suscripción está expirada
     */
    public function isSubscriptionExpired(): bool
    {
        return $this->subscription_expires_at && 
               $this->subscription_expires_at->isPast();
    }

    /**
     * Verificar si puede agregar más empleados
     */
    public function canAddEmployee(): bool
    {
        return $this->employees()->count() < $this->max_employees;
    }

    /**
     * Obtener uso de almacenamiento en MB
     */
    public function getStorageUsageMb(): float
    {
        $bytes = $this->files()->sum('size');
        return round($bytes / 1024 / 1024, 2);
    }

    /**
     * Verificar si puede subir archivo
     */
    public function canUploadFile(int $fileSizeBytes): bool
    {
        $currentUsageMb = $this->getStorageUsageMb();
        $newFileMb = $fileSizeBytes / 1024 / 1024;
        
        return ($currentUsageMb + $newFileMb) <= $this->max_storage_mb;
    }

    /**
     * Scope: Solo activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Por owner
     */
    public function scopeOwnedBy($query, int $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    /**
     * Scope: Ambiente de producción
     */
    public function scopeProduction($query)
    {
        return $query->where('environment', 'produccion');
    }

    /**
     * Get the uploaded files for the company.
     */
    public function uploadedFiles(): HasMany
    {
        return $this->files();
    }
}
