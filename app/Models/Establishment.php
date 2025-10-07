<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Establishment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ruc', 'business_name', 'trade_name', 'address', 'phone', 'email',
        'establishment_code', 'emission_point', 'environment', 'is_active',
        'logo_path', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    /**
     * Get the users that belong to the establishment.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_establishment')
            ->withPivot('is_admin', 'is_active')
            ->withTimestamps();
    }

    /**
     * Get the admins of the establishment.
     */
    public function admins()
    {
        return $this->belongsToMany(User::class, 'user_establishment')
            ->wherePivot('is_admin', true)
            ->wherePivot('is_active', true)
            ->withPivot('is_admin', 'is_active')
            ->withTimestamps();
    }

    /**
     * Get the uploaded files for the establishment.
     */
    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(UploadedFile::class);
    }

    /**
     * Scope a query to only include active establishments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
