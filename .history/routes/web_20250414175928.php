<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\CoordinacionController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\AuditoriaController;

// Rutas protegidas por autentificación
Route::middleware(['auth'])->group(function () {
    // Página principal
    Route::get('/', [CalendarioController::class, 'index'])->name('calendario.index');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Administración para el Administrador
    Route::middleware(['role:administrador'])->group(function () {
        // Usuarios
        Route::resource('admin/usuarios', UserController::class)->names('users');

        // Auditorías
        Route::get('/admin/auditorias', [AuditoriaController::class, 'index'])->name('auditorias.index');

        // Otros recursos como cursos, aulas, etc.
        Route::resource('admin/coordinaciones', CoordinacionController::class)->parameters(['coordinaciones' => 'coordinacion']);
        Route::resource('admin/cursos', CursoController::class)->parameters(['cursos' => 'curso']);
        Route::resource('admin/aulas', AulaController::class)->names('aulas');
    });
});

// Ruta de prueba para verificar middleware de roles
Route::get('/test-role', fn () => 'Middleware role registrado')->middleware('role:administrador');
