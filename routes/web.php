<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DepositController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí definimos todas las rutas de la aplicación, agrupándolas y
| protegiéndolas con los middlewares adecuados.
|
*/

// Rutas públicas (no requieren estar autenticado)
Route::get('/', fn() => redirect()->route('login.form'));
Route::get('/login', [AuthController::class, 'showLoginForm'])
     ->name('login.form');
Route::post('/login', [AuthController::class, 'login'])
     ->name('login.process');

// Rutas que requieren estar autenticado
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', fn() => view('dashboard'))
         ->name('dashboard');

    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout'])
         ->name('logout');

    // Búsqueda AJAX de clientes para autocomplete
    Route::get('/clients/search', [ClientController::class, 'search'])
         ->name('clients.search');

    // CRUD de clientes
    Route::resource('clients', ClientController::class)
         ->except(['show']);

    // CRUD de depósitos
    Route::resource('deposits', DepositController::class)
         ->except(['show']);

    // Filtrado de depósitos
    Route::get('/deposits/encurso', [DepositController::class, 'enCurso'])
         ->name('deposits.encurso');
    Route::get('/deposits/finalizados', [DepositController::class, 'finalizados'])
         ->name('deposits.finalizados');

     
     // partial AJAX
     Route::get('/deposits/finalizados/partial', [DepositController::class, 'finalizadosPartial'])
     ->name('deposits.finalizados.partial');

     // Ya dentro del middleware 'auth'...
     Route::get('/deposits/entregados', [
          DepositController::class, 'entregados'
     ])->name('deposits.entregados');
     
     // Para la recarga AJAX de la tabla
     Route::get('/deposits/entregados/partial', [
          DepositController::class, 'entregadosPartial'
     ])->name('deposits.entregados.partial');
     

    // Rutas reservadas a administradores
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class);
    });
});
