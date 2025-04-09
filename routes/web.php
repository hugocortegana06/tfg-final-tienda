<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Rutas públicas (no requieren auth)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
// Ruta POST para procesar el login
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
// Rutas que requieren estar autenticado
Route::middleware('auth')->group(function() {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Otras rutas protegidas
    // Route::resource('clients', ClientController::class);
    // Route::resource('deposits', DepositController::class);

    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Ruta raíz
Route::get('/', function() {
    return redirect('/login');
});

