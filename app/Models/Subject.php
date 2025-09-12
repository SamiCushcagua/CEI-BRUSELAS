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
        'Archivo',
        'imagen',
       
    ];

    // Relación con profesores (many-to-many)
    public function professors()
    {
        return $this->belongsToMany(User::class, 'subject_professor', 'subject_id', 'professor_id')
            ->where('is_profesor', true);
    }

    // Relación con estudiantes (many-to-many)
    public function students()
    {
        return $this->belongsToMany(User::class, 'subject_student', 'subject_id', 'student_id')
            ->where('is_profesor', false)
            ->where('is_admin', false);
    }

    /**
     * Inscribir estudiante en una materia
     */
    public function enrollStudent(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:users,id',
        ]);

        // Verificar que el usuario sea estudiante
        $student = User::find($validated['student_id']);
        if (!$student || $student->role !== 'student') {
            return redirect()->route('dashboard_cursos')
                ->with('error', 'El usuario seleccionado no es un estudiante.');
        }

        // Inscribir al estudiante (attach no duplica si ya existe)
        $subject->students()->syncWithoutDetaching([$validated['student_id']]);

        return redirect()->route('dashboard_cursos')
            ->with('success', 'Estudiante inscrito exitosamente.');
    }

    /**
     * Desinscribir estudiante de una materia
     */
    public function removeStudent(Subject $subject, User $student)
    {
        $subject->students()->detach($student->id);

        return redirect()->route('dashboard_cursos')
            ->with('success', 'Estudiante desinscrito exitosamente.');
    }
} 