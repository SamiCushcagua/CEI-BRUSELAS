<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BibleChapter extends Model
{
    protected $fillable = [
        'book_id',
        'chapter_number',
        'title',
        'verses_count'
    ];

    // Relación: Un capítulo pertenece a un libro
    public function book()
    {
        return $this->belongsTo(BibleBook::class, 'book_id');
    }

    // Relación: Un capítulo tiene muchos versículos
    public function verses()
    {
        return $this->hasMany(BibleVerse::class, 'chapter_id');
    }

    // Relación: Un capítulo puede ser leído por muchos usuarios
    public function userReadings()
    {
        return $this->hasMany(UserBibleReading::class);
    }

    // Método para obtener el nombre completo del capítulo
    public function getFullNameAttribute()
    {
        return $this->book->name . ' ' . $this->chapter_number;
    }
}