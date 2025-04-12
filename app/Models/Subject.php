<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'Nivel',
        'profesor_asignado',
        'Archivo',
        'imagen'
    ];

    // Relación con profesores (many-to-many)
    public function professors()
    {
        return $this->belongsToMany(User::class, 'subject_professor', 'subject_id', 'professor_id')
            ->where('role', 'professor');
    }

    // Relación con estudiantes (many-to-many)
    public function students()
    {
        return $this->belongsToMany(User::class, 'subject_student', 'subject_id', 'student_id')
            ->where('role', 'student');
    }
} 