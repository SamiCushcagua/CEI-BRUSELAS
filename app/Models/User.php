<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_profesor',
        'UsernameDummy',
        'verjaardag',
        'overMij',
        'image',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_profesor' => 'boolean'
        ];
    }

    /**
     * Check if the user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Check if the user is a professor
     */
    public function isProfessor(): bool
    {
        return $this->is_profesor === true;
    }

    /**
     * Check if the user is a student
     */
    public function isStudent(): bool
    {
        return $this->is_profesor === false && $this->is_admin === false;
    }

    /**
     * Get the cart items for the user.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // Relaciones para profesores
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_professor', 'professor_id', 'subject_id');
    }

    public function students()
    {
        // Obtener estudiantes que están inscritos en las materias que enseña este profesor
        $subjectIds = $this->subjects()->pluck('subjects.id');
        
        return User::where('is_profesor', false)
            ->where('is_admin', false)
            ->whereHas('subjectsAsStudent', function($query) use ($subjectIds) {
                $query->whereIn('subjects.id', $subjectIds);
            });
    }

    // Relaciones para estudiantes
    public function subjectsAsStudent()
    {
        return $this->belongsToMany(Subject::class, 'subject_student', 'student_id', 'subject_id');
    }

    public function professors()
    {
        // Obtener profesores que enseñan las materias que toma este estudiante
        $subjectIds = $this->subjectsAsStudent()->pluck('subjects.id');
        
        return User::where('is_profesor', true)
            ->whereHas('subjects', function($query) use ($subjectIds) {
                $query->whereIn('subjects.id', $subjectIds);
            });
    }


    // Relación: Un estudiante puede tener MUCHOS diplomas
public function diplomas()
{
    return $this->belongsToMany(Diploma::class, 'student_diplomas', 'student_id', 'diploma_id')
        ->withPivot('fecha_obtencion', 'calificacion', 'estado')
        ->withTimestamps();
}


// Relaciones para la Biblia
public function bibleReadings()
{
    return $this->hasMany(UserBibleReading::class, 'user_id');
}

public function favoriteVerses()
{
    return $this->hasMany(UserFavoriteVerse::class);
}

// Método para verificar si un usuario ha leído un capítulo
public function hasReadChapter($chapterId)
{
    return $this->bibleReadings()->where('chapter_id', $chapterId)->exists();
}

// Método para marcar un capítulo como leído
public function markChapterAsRead($chapterId)
{
    return $this->bibleReadings()->updateOrCreate(
        ['chapter_id' => $chapterId],
        ['read_at' => now()]
    );
}

// Relaciones para calificaciones
public function grades()
{
    return $this->hasMany(Grade::class, 'student_id');
}

public function gradesBySubject($subjectId)
{
    return $this->grades()->where('subject_id', $subjectId);
}

public function gradesByTrimester($trimester)
{
    return $this->grades()->where('trimester', $trimester);
}

public function gradesByYear($year)
{
    return $this->grades()->where('year', $year);
}

// Método para obtener calificación específica
public function getGrade($subjectId, $trimester, $year)
{
    return $this->grades()
        ->where('subject_id', $subjectId)
        ->where('trimester', $trimester)
        ->where('year', $year)
        ->first();
}

// Método para calcular promedio general
public function getOverallAverage($year = null)
{
    $query = $this->grades();
    
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


}
