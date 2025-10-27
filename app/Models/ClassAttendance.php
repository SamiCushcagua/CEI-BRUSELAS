<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassAttendance extends Model
{

    protected $table = 'class_attendance';
    protected $fillable = [
        'professor_id',
        'subject_id',
        'student_id',
        'class_date',
        'attendance_status',
        'bible_verse_delivered',
        'notes'
    ];

    protected $casts = [
        'class_date' => 'date',
        'bible_verse_delivered' => 'boolean',
    ];

    // Relación con el profesor
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    // Relación con la materia
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    // Relación con el estudiante
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Scope para filtrar por profesor
    public function scopeByProfessor($query, $professorId)
    {
        return $query->where('professor_id', $professorId);
    }

    // Scope para filtrar por materia
    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    // Scope para filtrar por fecha
    public function scopeByDate($query, $date)
    {
        return $query->where('class_date', $date);
    }
}