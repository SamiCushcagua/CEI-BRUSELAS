<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'trimester',
        'year',
        'task_score',
        'exam_score1',  
        'exam_score2',  
        'participation_score',
        'bible_score',
        'text_score',
        'other_score',
        'notes'
    ];

    protected $casts = [
        'task_score' => 'decimal:2',
        'exam_score1' => 'decimal:2',  
        'exam_score2' => 'decimal:2',  
        'participation_score' => 'decimal:2',
        'bible_score' => 'decimal:2',
        'text_score' => 'decimal:2',
        'other_score' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    // Accessor para calcular el total de calificaciones
    public function getTotalScoreAttribute()
    {
        $total = 0;
        $total += $this->task_score ?? 0;
        $total += $this->exam_score1 ?? 0;
        $total += $this->exam_score2 ?? 0;
        $total += $this->participation_score ?? 0;
        $total += $this->bible_score ?? 0;
        $total += $this->text_score ?? 0;
        $total += $this->other_score ?? 0;

        return round($total, 2);
    }

    // Accessor para calcular el promedio
    public function getAverageScoreAttribute()
    {
        $scores = array_filter([
            $this->task_score,
            $this->exam_score1,
            $this->exam_score2,
            $this->participation_score,
            $this->bible_score,
            $this->text_score,
            $this->other_score
        ]);

        if (empty($scores)) {
            return 0;
        }

        return round(array_sum($scores) / count($scores), 2);
    }

    // Scope para filtrar por trimestre
    public function scopeByTrimester($query, $trimester)
    {
        return $query->where('trimester', $trimester);
    }

    // Scope para filtrar por año
    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    // Scope para filtrar por estudiante
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    // Scope para filtrar por materia
    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }
}
