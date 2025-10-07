<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UploadedFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'employee_id',
        'user_id',
        'original_name',
        'stored_name',
        'path',
        'mime_type',
        'size',
        'extension',
        'type',
        'module',
        'related_id',
        'description',
        'metadata',
        'hash',
        'is_public',
        'is_processed',
        'processed_at',
        'download_count',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
        'is_processed' => 'boolean',
        'processed_at' => 'datetime',
    ];

    /**
     * Empresa propietaria del archivo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Empleado que subió el archivo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Owner que subió el archivo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener URL del archivo
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    /**
     * Obtener tamaño formateado
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get human readable file size.
     */
    public function getHumanReadableSize(): string
    {
        return $this->getFormattedSizeAttribute();
    }

    /**
     * Incrementar contador de descargas
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    /**
     * Marcar como procesado
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'is_processed' => true,
            'processed_at' => now(),
        ]);
    }

    /**
     * Verificar si es imagen
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Verificar si es PDF
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Scope: Por tipo
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Por módulo
     */
    public function scopeOfModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope: Solo públicos
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope: Por empresa
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Check if the file exists in storage.
     */
    public function exists(): bool
    {
        return Storage::exists($this->path);
    }

    /**
     * Eliminar archivo físico
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($file) {
            if ($file->isForceDeleting()) {
                Storage::delete($file->path);
            }
        });
    }
}
