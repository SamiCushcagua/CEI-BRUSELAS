<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function gradeSettings(): HasMany
    {
        return $this->hasMany(GradeSetting::class);
    }

    // Scope para tipos activos
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope para buscar por nombre
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }
}