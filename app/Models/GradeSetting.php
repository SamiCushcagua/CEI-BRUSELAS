<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeSetting extends Model
{
    protected $fillable = [
        'subject_id',
        'evaluation_type_id',
        'weight',
        'max_score',
        'passing_score'
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'max_score' => 'decimal:2',
        'passing_score' => 'decimal:2'
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function evaluationType(): BelongsTo
    {
        return $this->belongsTo(EvaluationType::class);
    }

    // Scope para filtrar por materia
    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    // Scope para filtrar por tipo de evaluación
    public function scopeByEvaluationType($query, $evaluationTypeId)
    {
        return $query->where('evaluation_type_id', $evaluationTypeId);
    }
}