<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoursePlanLesson extends Model
{
    protected $fillable = [
        'course_plan_id',
        'class_number',
        'class_date',
        'topic',
        'assignment',
        'has_homework',
    ];

    protected $casts = [
        'class_date' => 'date',
        'has_homework' => 'boolean',
    ];

    public function coursePlan(): BelongsTo
    {
        return $this->belongsTo(CoursePlan::class);
    }
}
