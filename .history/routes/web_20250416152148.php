<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController,
    UserController,
    CalendarioController,
    CoordinacionController,
    CursoController,
    AulaController,
    AuditoriaController,
    GrupoController,
    FeriadoController
};

// Rutas para usuarios autenticados
Route::middleware(['auth'])->group(function () {
    Route::get('/', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('/dashboard', fn () => view('dashboard'))->middleware('verified')->name('dashboard');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Calendario API
    Route::get('/api/eventos', [CalendarioController::class, 'eventos']);

    // Vistas generales
    Route::view('/programaciones', 'admin.programaciones.index')->name('programaciones.index');
    Route::view('/agenda', 'admin.agenda.index')->name('agenda.index');
    Route::view('/aulas', 'admin.aulas.index')->name('aulas.index');
    Route::view('/instructores', 'admin.instructores.index')->name('instructores.index');

    // Accesibles por coordinadores, analistas y administradores
    Route::middleware('role:administrador,coordinador,analista')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('grupos', GrupoController::class)->names('grupos');
        Route::resource('cursos', CursoController::class)->names('cursos');
    });
});

// Rutas exclusivas de administrador
Route::middleware(['auth', 'role:administrador'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

    Route::resource('usuarios', UserController::class)->names('users');
    Route::resource('coordinaciones', CoordinacionController::class)->parameters(['coordinaciones' => 'coordinacion'])->names('coordinaciones');
    Route::resource('aulas', AulaController::class)->names('aulas');
    Route::resource('feriados', FeriadoController::class)->parameters(['feriados' => 'feriado'])->names('feriados');

    Route::get('auditorias', [AuditoriaController::class, 'index'])->name('auditorias.index');

    // Rutas AJAX
    Route::get('/grupos-por-coordinacion/{coordinacion}', [GrupoController::class, 'getGruposByCoordinacionJson'])->name('grupos.por.coordinacion');
    Route::get('/grupos-visibles', [GrupoController::class, 'getGruposVisiblesPorUsuarioJson'])->name('grupos.visibles');
});

// Autenticación Breeze
require __DIR__ . '/auth.php';
