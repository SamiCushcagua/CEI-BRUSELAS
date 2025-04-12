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
        return $this->belongsToMany(User::class, 'professor_student', 'professor_id', 'student_id')
            ->where('role', 'student');
    }

    // Relaciones para estudiantes
    public function subjectsAsStudent()
    {
        return $this->belongsToMany(Subject::class, 'subject_student', 'student_id', 'subject_id');
    }

    public function professors()
    {
        return $this->belongsToMany(User::class, 'professor_student', 'student_id', 'professor_id')
            ->where('role', 'professor');
    }
}
