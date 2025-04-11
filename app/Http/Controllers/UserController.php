<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
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
} 