<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;  // tu modelo de usuario
use Illuminate\Support\Facades\Hash;



class AuthController extends Controller
{
    // Mostrar formulario login
    public function showLoginForm()
    {
        // Si ya está logueado, redirige
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    // Procesar login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Se construyen las credenciales
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials)) {
            $nombre = Auth::user()->name;
            $rol = Auth::user()->role;
            return redirect()
                ->route('dashboard')
                ->with('success', "¡Bienvenido, $nombre! Eres $rol.");
        }
        

        // Credenciales invalidas
        return back()->withErrors(['email' => 'Credenciales incorrectas.'])->withInput();
    }

    // Cerrar sesión
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login.form')->with('info', 'Sesión cerrada.');
    }
}
