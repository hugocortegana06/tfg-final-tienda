<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;

// Rutas públicas (no requieren auth)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
// Ruta POST para procesar el login
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
// Rutas que requieren estar autenticado
Route::middleware('auth')->group(function() {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // CRUD de clientes
    Route::resource('clients', ClientController::class);
    // Otras rutas protegidas
    // Route::resource('clients', ClientController::class);
    // Route::resource('deposits', DepositController::class);

    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Rutas para depósitos finalizados y en curso
Route::get('/deposits/finalizados', [DepositController::class, 'finalizados'])->name('deposits.finalizados');
Route::get('/deposits/encurso', [DepositController::class, 'enCurso'])->name('deposits.encurso');
// Rutas para añadir usuarios y depósitos
Route::get('/deposits/create', [DepositController::class, 'create'])->name('deposits.create');

Route::middleware(['auth','admin'])->group(function() {
    Route::resource('users', UserController::class);    
    // ...

     // Rutas manuales para update y destroy:
     Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
     Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});



// Ruta de clientes
Route::resource('clients', ClientController::class); 

// Ruta raíz
Route::get('/', function() {
    return redirect('/login');
});

