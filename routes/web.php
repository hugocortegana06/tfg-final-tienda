<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Formularios
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register.form');

// Procesar formularios
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');

// Zona protegida (restringida a usuarios autenticados)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');
