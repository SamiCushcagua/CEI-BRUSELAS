<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BibleBook extends Model
{
    protected $fillable = [
        'name',
        'testament',
        'order',
        'chapters_count'
    ];

    // Relación: Un libro tiene muchos capítulos
    public function chapters()
    {
        return $this->hasMany(BibleChapter::class, 'book_id');
    }

    // Scope para obtener libros del Antiguo Testamento
    public function scopeOldTestament($query)
    {
        return $query->where('testament', 'old');
    }

    // Scope para obtener libros del Nuevo Testamento
    public function scopeNewTestament($query)
    {
        return $query->where('testament', 'new');
    }

    // Accessor para obtener el número real de capítulos
    public function getChaptersCountAttribute()
    {
        return $this->chapters()->count();
    }
}