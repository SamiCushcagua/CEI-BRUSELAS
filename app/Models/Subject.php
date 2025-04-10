<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'products';
    
    protected $fillable = [
        'name',
        'description',
        'Archivo',
        'imagen',
        'Nivel',
        'profesor_asignado',
    ];

}
