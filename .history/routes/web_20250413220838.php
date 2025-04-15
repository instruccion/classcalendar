<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CalendarioController;

Route::get('/', function () {
    return redirect()->route('calendario.index');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/', [CalendarioController::class, 'index'])->name('calendario.index');
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

Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

// Ruta de prueba para middleware de roles
Route::get('/test-role', function () {
    return 'Middleware role registrado';
})->middleware('role:administrador');

Route::middleware('auth')->get('/api/eventos', [CalendarioController::class, 'eventos']);

Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::resource('admin/coordinaciones', CoordinacionController::class)->names('coordinaciones');
});



// Controladores genéricos para vista de pruebas (puedes luego reemplazar por controladores reales)
Route::view('/programaciones', 'admin.programaciones.index')->name('programaciones.index');
Route::view('/agenda', 'admin.agenda.index')->name('agenda.index');
Route::view('/coordinaciones', 'admin.coordinaciones.index')->name('coordinaciones.index');
Route::view('/cursos', 'admin.cursos.index')->name('cursos.index');
Route::view('/aulas', 'admin.aulas.index')->name('aulas.index');
Route::view('/instructores', 'admin.instructores.index')->name('instructores.index');

// Solo para administradores
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::view('/usuarios', 'admin.usuarios.index')->name('users.index');
    Route::view('/feriados', 'admin.feriados.index')->name('feriados.index');
});

// Rutas de autenticación
require __DIR__.'/auth.php';
