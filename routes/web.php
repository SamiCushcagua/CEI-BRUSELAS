<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\UserController;
use App\Models\FAQ;
use App\Models\ContactForum;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SubjectController;
use App\Models\Subject;
use App\Http\Controllers\SubjectRelationshipController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FAQController;

// Public Routes
Route::get('/', function () {
    return view('welcome', ['users' => User::all()]);
})->name('welcome');

Route::get('/about', function () {
    return view('about');
})->name('about');

// FAQ Routes
Route::get('/FAQ', [FAQController::class, 'index'])->name('FAQ');
Route::post('/FAQ', [FAQController::class, 'store'])->name('FAQ.store');
Route::get('/FAQ/{id}/edit', [FAQController::class, 'edit'])->name('FAQ.edit');
Route::put('/FAQ/{id}', [FAQController::class, 'update'])->name('FAQ.update');
Route::delete('/FAQ/{id}', [FAQController::class, 'destroy'])->name('FAQ.delete');

// Contact Routes
Route::get('/Contact/{name?}', [ContactController::class, 'show'])->name('Contact');
Route::get('/contact-forum', function () {
    $contactForum = App\Models\ContactForum::all();
    return view('contact-forum', ['contactForum' => $contactForum]);
})->name('contact-forum');
Route::post('/send-email', [ContactController::class, 'store'])->name('send-email');
Route::post('/contact-forum', [ContactController::class, 'store'])->name('contact-forum');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Profile Routes
Route::get('/profile', function () {
        return view('Profiel_page', ['user' => Auth::user()]);
})->name('profile');

Route::get('/profile/{user}/edit', function (User $user) {
    return view('edit_profiel', ['user' => $user]);
})->name('profile.edit');

    Route::get('/profile/{user}', function (User $user) {
        return view('Profiel_page', ['user' => $user]);
    })->name('profile.public');

    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('usersAllShow');

    Route::post('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::put('/users/{id}', [UserController::class, 'edit'])->name('users.edit');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    // Subject Routes
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::get('/subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
    Route::get('/dashboard-cursos', function () {
        $subjects = Subject::all();
        return view('subjects.dashboard_cursos', ['subjects' => $subjects]);
    })->name('dashboard_cursos');

    // Subject Relationships
    Route::post('/subjects/{subject}/assign-professor', [SubjectRelationshipController::class, 'assignProfessor']);
    Route::delete('/subjects/{subject}/remove-professor/{professor}', [SubjectRelationshipController::class, 'removeProfessor']);
    Route::post('/subjects/{subject}/enroll-student', [SubjectRelationshipController::class, 'enrollStudent']);
    Route::delete('/subjects/{subject}/remove-student/{student}', [SubjectRelationshipController::class, 'removeStudent']);

    // Professor Routes
    Route::get('/professors', [ProfessorController::class, 'index'])->name('professors.index');
    Route::get('/professors/{professor}/subjects', [ProfessorController::class, 'subjects'])->name('professors.subjects');
    Route::get('/professors/{professor}/students', [ProfessorController::class, 'students'])->name('professors.students');
    Route::post('/professors/{professor}/assign-student', [SubjectRelationshipController::class, 'assignStudentToProfessor']);
    Route::delete('/professors/{professor}/remove-student/{student}', [SubjectRelationshipController::class, 'removeStudentFromProfessor']);

    // Student Routes
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/{student}/subjects', [SubjectRelationshipController::class, 'getStudentSubjects'])->name('students.subjects');
    Route::get('/students/{student}/professors', [SubjectRelationshipController::class, 'getStudentProfessors'])->name('students.professors');
});

Route::get('/create-test-product', function () {
    return view('create-test-product');
})->name('create-test-product');

require __DIR__.'/auth.php';