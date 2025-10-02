<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BibleVerse extends Model
{
    protected $fillable = [
        'chapter_id',
        'verse_number',
        'text'
    ];

    // Relación: Un versículo pertenece a un capítulo
    public function chapter()
    {
        return $this->belongsTo(BibleChapter::class);
    }

    // Relación: Un versículo puede ser favorito de muchos usuarios
    public function userFavorites()
    {
        return $this->belongsTo(BibleChapter::class, 'chapter_id');
    }

    // Método para obtener la referencia completa del versículo
    public function getFullReferenceAttribute()
    {
        return $this->chapter->book->name . ' ' . 
               $this->chapter->chapter_number . ':' . 
               $this->verse_number;
    }
}