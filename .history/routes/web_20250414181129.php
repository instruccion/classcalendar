<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\CoordinacionController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\GrupoController; // Asegúrate de agregar GrupoController

use Illuminate\Support\Facades\Route;

/*
|----------------------------------------------------------------------
| Rutas públicas protegidas por auth
|----------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('/dashboard', fn () => view('dashboard'))->middleware('verified')->name('dashboard');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // API eventos
    Route::get('/api/eventos', [CalendarioController::class, 'eventos']);

    // Acceso común a todos los roles excepto instructores
    Route::view('/programaciones', 'admin.programaciones.index')->name('programaciones.index');
    Route::view('/agenda', 'admin.agenda.index')->name('agenda.index');
    Route::view('/coordinaciones', 'admin.coordinaciones.index')->name('coordinaciones.index');
    Route::view('/cursos', 'admin.cursos.index')->name('cursos.index');
    Route::view('/aulas', 'admin.aulas.index')->name('aulas.index');
    Route::view('/instructores', 'admin.instructores.index')->name('instructores.index');
});

/*
|----------------------------------------------------------------------
| Rutas exclusivas para el administrador
|----------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/admin/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');

    // Usuarios
    Route::get('/admin/usuarios', [UserController::class, 'index'])->name('users.index');
    Route::get('/admin/usuarios/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/admin/usuarios', [UserController::class, 'store'])->name('users.store');
    Route::get('/admin/usuarios/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/admin/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/admin/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::put('/admin/usuarios/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
    Route::put('/admin/usuarios/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');

    // Coordinaciones con CRUD
    Route::resource('admin/coordinaciones', CoordinacionController::class)->parameters(['coordinaciones' => 'coordinacion']);
    Route::resource('admin/cursos', CursoController::class)->parameters(['cursos' => 'curso']);
    Route::resource('admin/aulas', AulaController::class)->names('aulas');
    Route::resource('admin/grupos', GrupoController::class)->names('grupos');
    Route::get('/admin/auditorias', [AuditoriaController::class, 'index'])->name('auditorias.index');
});
