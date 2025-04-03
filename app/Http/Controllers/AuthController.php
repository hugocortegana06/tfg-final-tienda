<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;  // tu modelo de usuario
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Mostrar formulario de login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Procesar login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Intento de autenticación
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // Login correcto
            return redirect('/dashboard');
        }

        // Credenciales inválidas
        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    // Mostrar formulario de registro
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Procesar registro
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed'
        ]);

        // Crear usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),  // se encripta
            'role' => 'client', // o 'admin' si corresponde
            'dni' => $request->dni, // si quieres guardar DNI
            'phone' => $request->phone,
            'address' => $request->address
        ]);

        // Autenticar al usuario recién creado
        Auth::login($user);

        return redirect('/dashboard');
    }

    // Cerrar sesión
    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
