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

// Relaciones para calificaciones
public function grades()
{
    return $this->hasMany(Grade::class);
}

public function gradeSettings()
{
    return $this->hasMany(GradeSetting::class);
}

// Método para obtener calificaciones por trimestre
public function getGradesByTrimester($trimester, $year = null)
{
    $query = $this->grades()->where('trimester', $trimester);
    
    if ($year) {
        $query->where('year', $year);
    }
    
    return $query->get();
}

// Método para obtener promedio de la materia
public function getAverageGrade($trimester = null, $year = null)
{
    $query = $this->grades();
    
    if ($trimester) {
        $query->where('trimester', $trimester);
    }
    
    if ($year) {
        $query->where('year', $year);
    }
    
    $grades = $query->get();
    
    if ($grades->isEmpty()) {
        return 0;
    }
    
    $total = $grades->sum('total_score');
    $count = $grades->count();
    
    return round($total / $count, 2);
}

// Método para obtener estudiantes con calificaciones
public function getStudentsWithGrades($trimester = null, $year = null)
{
    $query = $this->students();
    
    if ($trimester || $year) {
        $query->whereHas('grades', function($q) use ($trimester, $year) {
            if ($trimester) {
                $q->where('trimester', $trimester);
            }
            if ($year) {
                $q->where('year', $year);
            }
        });
    }
    
    return $query->get();
}


} 