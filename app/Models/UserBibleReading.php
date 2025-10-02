<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBibleReading extends Model
{
    protected $fillable = [
        'user_id',
        'chapter_id',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime'
    ];

    // Relación: Una lectura pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Una lectura pertenece a un capítulo
    public function chapter()
    {
        return $this->belongsTo(BibleChapter::class);
    }
}