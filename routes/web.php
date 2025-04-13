<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\UserController;
use App\Models\FAQ;
use App\Models\ContactForum;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SubjectController;
use App\Models\Subject;
use App\Http\Controllers\SubjectRelationshipController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\StudentController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home page
Route::get('/', function () {
    $users = User::all();
    return view('welcome', [
        'users' => $users
    ]);
})->name('welcome');

Route::get('/contact-forum', function () {
    $contactForum = ContactForum::all();
    return view('contact-forum', [
        'contactForum' => $contactForum
    ]);
})->name('contact-forum');  

Route::post('/send-email', [ContactController::class, 'store'])->name('send-email');


// FAQ page
Route::get('/FAQ', function () {
    $faqs = FAQ::all(); 
    $users = User::all();
    $categories = FAQ::distinct()->pluck('category'); // neem geen dubbelen (distinct) en pluck zoekt uit een kolom hierbij category
    
    return view('FAQ', [
        'faqs' => $faqs,
        'users' => $users,
        'categories' => $categories
    ]);
})->name('FAQ');

Route::get('/FAQ/edit/{id}', function ($id) {
    $faq = FAQ::find($id);
    return view('edit_FAQ', ['faq' => $faq]);
})->name('FAQ.edit');

// About page
Route::get('/about', function () {
    if (Auth::check()) {
        return view('about', [
            'userInfo' => 'Welcome ' . Auth::user()->name . '! This is the full version of the page.'
        ]);
    }
    return view('about', [
        'userInfo' => 'This is the basic version. Log in to see more.'
    ]);
})->name('about');

// Contact page
Route::get('/Contact/{name?}', [ContactController::class, 'show'])->name('Contact');

// Public profile routes
Route::get('/profile/{user}', function (User $user) {
    return view('Profiel_page', ['user' => $user]);
})->name('profile.public');
 
    
    // Rutas de materias
    Route::get('/subjects', [SubjectController::class, 'index'])
    ->name('subjects.index');

    Route::get('/subjects/create', [SubjectController::class, 'create'])
    ->name('subjects.create');

    Route::post('/subjects', [SubjectController::class, 'store'])
    ->name('subjects.store');

    Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])
    ->name('subjects.edit');

    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])
    ->name('subjects.update');

    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])
    ->name('subjects.destroy');

    // Rutas para relaciones entre materias, profesores y estudiantes
    Route::post('/subjects/{subject}/assign-professor', [SubjectRelationshipController::class, 'assignProfessor']);
    Route::delete('/subjects/{subject}/remove-professor/{professor}', [SubjectRelationshipController::class, 'removeProfessor']);
    Route::get('/professors/{professor}/subjects', [SubjectRelationshipController::class, 'getProfessorSubjects']);
    Route::get('/professors/{professor}/students', [SubjectRelationshipController::class, 'getProfessorStudents']);
    Route::post('/professors/{professor}/assign-student', [SubjectRelationshipController::class, 'assignStudentToProfessor']);
    Route::delete('/professors/{professor}/remove-student/{student}', [SubjectRelationshipController::class, 'removeStudentFromProfessor']);

    // Rutas para estudiantes
    Route::post('/subjects/{subject}/enroll-student', [SubjectRelationshipController::class, 'enrollStudent']);
    Route::delete('/subjects/{subject}/remove-student/{student}', [SubjectRelationshipController::class, 'removeStudent']);
    Route::get('/students/{student}/subjects', [SubjectRelationshipController::class, 'getStudentSubjects'])->name('students.subjects');
    Route::get('/students/{student}/professors', [SubjectRelationshipController::class, 'getStudentProfessors'])->name('students.professors');

    // Professor routes
    Route::get('/professors/{professor}/subjects', [ProfessorController::class, 'subjects'])->name('professors.subjects');
    Route::get('/professors/{professor}/students', [ProfessorController::class, 'students'])->name('professors.students');


// Rutas de autenticación
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->name('login')
    ->middleware('guest');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

// Rutas de registro
Route::get('/register', [RegisteredUserController::class, 'create'])
    ->name('register')
    ->middleware('guest');

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest');

// Rutas de perfil
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas de usuarios
Route::middleware(['auth'])->group(function () {
    Route::get('/usersAllShow', function () {
        $allUser = User::all();
        return view('usersAllShow', ['allUser' => $allUser]);
    })->name('usersAllShow');
    
    Route::post('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::put('/users/{id}', [UserController::class, 'edit'])->name('users.edit');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});

// Ruta de perfil dummy
Route::middleware(['auth'])->group(function () {
    Route::get('/users/{user}/edit/dummy', function (User $user) {
        return view('Profiel_page', ['user' => $user]);
    })->name('users.edit.dummy');

    Route::put('/users/{user}/edit/dummy', function (User $user, Request $request) {
        try {
            $request->validate([
                'UsernameDummy' => 'required|string|max:255',
                'verjaardag' => 'nullable|date',
                'overMij' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $data = $request->only(['UsernameDummy', 'verjaardag', 'overMij']);

            if ($request->hasFile('image')) {
                if ($user->image) {
                    Storage::disk('public')->delete($user->image);
                }
                $filename = time() . '_' . $request->file('image')->getClientOriginalName();
                $data['image'] = $request->file('image')->storeAs('profile-photos', $filename, 'public');
            }
            
            $user->update($data);
            return redirect()->route('profile')->with('success', 'User info updated successfully');
        } catch (\Exception $e) {
            return redirect()->route('profile')->with('error', 'Error updating user info: ' . $e->getMessage());
        }
    })->name('users.edit.dummy');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Rutas de materias
    Route::get('/subjects', [SubjectController::class, 'index'])
    ->name('subjects.index');

    Route::get('/subjects/create', [SubjectController::class, 'create'])
    ->name('subjects.create');

    Route::post('/subjects', [SubjectController::class, 'store'])
    ->name('subjects.store');

    Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])
    ->name('subjects.edit');

    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])
    ->name('subjects.update');

    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])
    ->name('subjects.destroy');

    // Rutas para relaciones entre materias, profesores y estudiantes
    Route::post('/subjects/{subject}/assign-professor', [SubjectRelationshipController::class, 'assignProfessor']);
    Route::delete('/subjects/{subject}/remove-professor/{professor}', [SubjectRelationshipController::class, 'removeProfessor']);
    Route::get('/professors/{professor}/subjects', [SubjectRelationshipController::class, 'getProfessorSubjects']);
    Route::get('/professors/{professor}/students', [SubjectRelationshipController::class, 'getProfessorStudents']);
    Route::post('/professors/{professor}/assign-student', [SubjectRelationshipController::class, 'assignStudentToProfessor']);
    Route::delete('/professors/{professor}/remove-student/{student}', [SubjectRelationshipController::class, 'removeStudentFromProfessor']);

    // Rutas para estudiantes
    Route::post('/subjects/{subject}/enroll-student', [SubjectRelationshipController::class, 'enrollStudent']);
    Route::delete('/subjects/{subject}/remove-student/{student}', [SubjectRelationshipController::class, 'removeStudent']);
    Route::get('/students/{student}/subjects', [SubjectRelationshipController::class, 'getStudentSubjects'])->name('students.subjects');
    Route::get('/students/{student}/professors', [SubjectRelationshipController::class, 'getStudentProfessors'])->name('students.professors');

    // Professor routes
    Route::get('/professors/{professor}/subjects', [ProfessorController::class, 'subjects'])->name('professors.subjects');
    Route::get('/professors/{professor}/students', [ProfessorController::class, 'students'])->name('professors.students');

    // Rutas de autenticación
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login')
        ->middleware('guest');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('guest');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout')
        ->middleware('auth');

    // Rutas de registro
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register')
        ->middleware('guest');

    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('guest');

    // Rutas de perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de usuarios
    Route::get('/usersAllShow', function () {
        $allUser = User::all();
        return view('usersAllShow', ['allUser' => $allUser]);
    })->name('usersAllShow');
    
    Route::post('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::put('/users/{id}', [UserController::class, 'edit'])->name('users.edit');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    // User Management Routes
    Route::post('/users/create', function (Request $request) {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'is_admin' => 'boolean'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => $request->has('is_admin')
            ]);

            return redirect()->back()->with('success', 'User created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error creating user: ' . $e->getMessage());
        }
    })->name('users.create');

    Route::delete('/users/{user}', function (User $user) {
        try {
            $user->delete();
            return redirect()->back()->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    })->name('users.destroy');

    Route::put('/users/{user}/edit', function (User $user, Request $request) {
        try {
            $role = $request->input('role');
            
            switch ($role) {
                case 'admin':
                    $user->is_admin = true;
                    $user->is_profesor = false;
                    break;
                case 'profesor':
                    $user->is_admin = false;
                    $user->is_profesor = true;
                    break;
                case 'user':
                    $user->is_admin = false;
                    $user->is_profesor = false;
                    break;
                default:
                    return redirect()->back()->with('error', 'Rol no válido');
            }

            $user->save();
            
            $newRole = $user->is_admin ? 'Administrador' : ($user->is_profesor ? 'Profesor' : 'Usuario');
            return redirect()->back()->with('success', "Rol de usuario actualizado a: {$newRole}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error actualizando usuario: ' . $e->getMessage());
        }
    })->name('users.edit');
});

Route::post('/FAQ', function (Request $request) {
    $request->validate([    
        'question' => 'required|string|max:255',
        'answer' => 'required|string',
        'category' => 'required|string|max:255'
    ]);

    $faq = FAQ::create($request->all());
    return redirect()->route('FAQ')->with('success', 'FAQ created successfully');
})->name('FAQ');



/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::get('/profile', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return view('Profiel_page', ['user' => $user]);
    }
    return view('Profiel_page');
})->name('profile');

Route::get('/profile/{user}/edit', function (User $user) {
    return view('edit_profiel', ['user' => $user]);
})->name('profile.edit');

Route::delete('/FAQ/delete/{id}', function ($id) {
    $faq = FAQ::findOrFail($id);
    $faq->delete();
    return redirect()->route('FAQ')->with('success', 'FAQ deleted successfully');
})->name('FAQ.delete');

Route::put('/FAQ/edit/{id}', function (Request $request, $id) {
    $faq = FAQ::findOrFail($id);
    $faq->update($request->all());
    return redirect()->route('FAQ')->with('success', 'FAQ updated successfully');
})->name('FAQ.update');


Route::post('/contact-forum', [ContactController::class, 'store'])->name('contact-forum');






Route::get('/subjects/dashboard_cursos', function () {
    $subjects = Subject::all();
    return view('subjects.dashboard_cursos', ['subjects' => $subjects]);
})->name('dashboard_cursos');   

    




/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

// Rutas para profesores
Route::resource('professors', ProfessorController::class);

// Rutas para estudiantes
Route::resource('students', StudentController::class);










