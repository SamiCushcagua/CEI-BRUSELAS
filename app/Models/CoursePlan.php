<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoursePlan extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'subject_id',
        'period_id',
        'textbook',
        'cognitive_objectives',
        'affective_objectives',
        'psychomotor_objectives',
        'requirements',
        'task_descriptions',
        'general_notes',
        'complementary_data',
        'bibliography',
        'status',
        'last_edited_by',
    ];

    protected $casts = [
        'cognitive_objectives' => 'array',
        'affective_objectives' => 'array',
        'psychomotor_objectives' => 'array',
        'requirements' => 'array',
        'task_descriptions' => 'array',
        'general_notes' => 'array',
        'bibliography' => 'array',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CoursePlanLesson::class)->orderBy('class_number');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function hasTeacherContent(): bool
    {
        foreach (['textbook', 'complementary_data'] as $field) {
            if (filled(trim((string) ($this->{$field} ?? '')))) {
                return true;
            }
        }

        foreach ([
            'cognitive_objectives',
            'affective_objectives',
            'psychomotor_objectives',
            'requirements',
            'task_descriptions',
            'general_notes',
            'bibliography',
        ] as $field) {
            $items = $this->{$field} ?? [];
            if (! is_array($items)) {
                continue;
            }
            foreach ($items as $item) {
                if (filled(trim((string) $item))) {
                    return true;
                }
            }
        }

        foreach ($this->lessons as $lesson) {
            if (filled(trim((string) ($lesson->topic ?? '')))
                || filled(trim((string) ($lesson->assignment ?? '')))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return 'empty'|'draft'|'published'
     */
    public function adminDisplayStatus(): string
    {
        if (! $this->hasTeacherContent()) {
            return 'empty';
        }

        return $this->isPublished() ? 'published' : 'draft';
    }
}
