<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function create()
    {
        // Solo llega aquí si 'auth' y 'admin' se cumplen.
        return view('users.create');
    }

    public function store(Request $request)
    {
        // Validaciones del lado servidor
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'confirmed', // password_confirmation
                Password::min(6)
                    ->letters()
                    ->numbers()
            ],
            'role' => 'required|in:admin,user'
        ]);

        // Crear usuario
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role
        ]);

        // Redirige a la tabla de usuarios con el mensaje de éxito
        return redirect()
            ->route('users.index')
            ->with('success','Usuario creado correctamente');
    }

    // Lista todos los usuarios en orden descendente por fecha de creación
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('users.index', compact('users'));
    }
}
