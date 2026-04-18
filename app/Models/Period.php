<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    protected $fillable = [
        'name',
        'year',
        'trimester',
        'start_date',
        'end_date',
        'is_active',
        'is_locked',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Periodo marcado como activo (si hay varios, el primero por año/trimestre).
     */
    public static function currentAcademic(): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('year')
            ->orderBy('trimester')
            ->first();
    }

    /**
     * Periodo académico inmediatamente anterior al dado (mismo orden año + trimestre).
     */
    public static function previousChronologicalTo(?self $period): ?self
    {
        if (! $period) {
            return null;
        }

        $periods = static::query()
            ->orderBy('year')
            ->orderBy('trimester')
            ->get();

        $idx = $periods->search(fn (self $p) => (int) $p->id === (int) $period->id);
        if ($idx === false || $idx < 1) {
            return null;
        }

        return $periods[$idx - 1];
    }
}