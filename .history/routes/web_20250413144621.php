<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

// Ruta principal después del login
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas de perfil para usuarios autenticados
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas protegidas para administradores
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/admin/usuarios', [UserController::class, 'index'])->name('users.index');
    Route::put('/admin/usuarios/{user}', [UserController::class, 'updateRole'])->name('users.updateRole');
});

// Ruta de prueba para middleware de roles
Route::get('/test-role', function () {
    return 'Middleware role registrado';
})->middleware('role:administrador');

// Rutas de autenticación
require __DIR__.'/auth.php';
