<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteVerse extends Model
{
    protected $fillable = [
        'user_id',
        'verse_id',
        'note'
    ];

    // Relación: Un favorito pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Un favorito pertenece a un versículo
    public function verse()
    {
        return $this->belongsTo(BibleVerse::class);
    }
}