<?php
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
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
use App\Http\Controllers\CartController;
use App\Http\Controllers\SubjectController;
use App\Models\Subject;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home page
Route::get('/', function () {
    $products = Product::all();
    $users = User::all();
    return view('welcome', [
        'products' => $products,
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

// Users list public route
Route::get('/usersAllShow', function () {
    $allUser = User::all();
    return view('usersAllShow', ['allUser' => $allUser]);
})->name('usersAllShow');


/*
|--------------------------------------------------------------------------
| Product Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas de productos
Route::get('/create-test-product', function () {
    return view('create-test-product');
})->name('create-test-product');

// Rutas protegidas de productos
Route::middleware(['auth'])->group(function () {
    // Rutas de productos usando el controlador
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('store-product');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit.form');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Ruta para crear producto de prueba
    Route::post('/store-test-product', function (Request $request) {
        try {
            $request->validate([
                'name' => 'required|max:255',
                'prijs' => 'required|numeric|min:0',
                'description' => 'required',
                'title' => 'required|max:255',
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'created_date' => 'required|date'
            ]);

            $data = $request->only(['name', 'prijs', 'description', 'title', 'created_date']);
            
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($data);
            return redirect()->route('welcome')->with('success', 'Product created successfully: ' . $product->name);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error creating product: ' . $e->getMessage());
        }
    })->name('store-test-product');

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
});

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
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
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

Route::middleware('auth')->group(function () {
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
            $user->is_admin = !$user->is_admin;
            $user->save();
            
            $newRole = $user->is_admin ? 'Administrator' : 'User';
            return redirect()->back()->with('success', "User role updated to: {$newRole}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating user: ' . $e->getMessage());
        }
    })->name('users.edit');

    // Product Management Routes
    Route::put('/products/{product}/update', function (Product $product, Request $request) {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'prijs' => 'required|numeric|min:0',
                'description' => 'required',
                'title' => 'required|max:255',
                'created_date' => 'required|date',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
            
            $data = $request->only(['name', 'prijs', 'description', 'title', 'created_date']);

            if ($request->hasFile('image')) {
                // Eliminar imagen anterior si existe
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                // Guardar nueva imagen
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);
            return redirect()->route('welcome')->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating product: ' . $e->getMessage());
        }


        return view('welcome');
    })->name('products.update');
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

// Cart routes
Route::middleware(['auth'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/remove/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
});










