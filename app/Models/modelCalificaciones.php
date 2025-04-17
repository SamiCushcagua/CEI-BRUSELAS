<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class modelCalificaciones extends Model
{
    //
protected $table = 'calificaciones';

protected $fillable = [
   
    'Examen1',
    'Examen2',
    'participacion',
    'puntualidad',
    'Material',
    'VersiculoBiblico',
    'tarea',
    'total'
    
];

}
