<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $role = (string) $request->query('role', 'all');

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($role === 'admin', fn ($query) => $query->where('is_admin', true))
            ->when($role === 'profesor', fn ($query) => $query->where('is_profesor', true)->where('is_admin', false))
            ->when($role === 'user', fn ($query) => $query->where('is_admin', false)->where('is_profesor', false))
            ->orderBy('name')
            ->orderBy('email')
            ->get();

        return view('usersAllShow', [
            'allUser' => $users,
            'search' => $search,
            'role' => $role,
        ]);
    }

    public function create(Request $request)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('usersAllShow')->with('error', 'No tienes permiso para realizar esta acción.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $request->has('is_admin'),
            'is_profesor' => $request->has('is_profesor')
        ]);

        return redirect()->route('usersAllShow')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Validar que el usuario actual sea administrador
        if (!Auth::user()->is_admin) {
            return redirect()->route('usersAllShow')->with('error', 'No tienes permiso para realizar esta acción.');
        }

        // Obtener el rol seleccionado
        $role = $request->input('role');

        // Actualizar los roles según la selección
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
                return redirect()->route('usersAllShow')->with('error', 'Rol no válido.');
        }

        $user->save();

        return redirect()->route('usersAllShow')->with('success', 'Rol de usuario actualizado correctamente.');
    }

    public function destroy($id)
    {
        // Validar que el usuario actual sea administrador
        if (!Auth::user()->is_admin) {
            return redirect()->route('usersAllShow')->with('error', 'No tienes permiso para realizar esta acción.');
        }

        $user = User::findOrFail($id);
        
        // Prevenir que el usuario se elimine a sí mismo
        if ($user->id === Auth::id()) {
            return redirect()->route('usersAllShow')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();

        return redirect()->route('usersAllShow')->with('success', 'Usuario eliminado correctamente.');
    }
} 