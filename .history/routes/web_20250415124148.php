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
    GrupoController, // Controlador principal de grupos
    FeriadoController
};

// Rutas para usuarios autenticados
Route::middleware(['auth'])->group(function () {
    Route::get('/', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('/dashboard', fn () => view('dashboard'))->middleware('verified')->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/api/eventos', [CalendarioController::class, 'eventos']);
    Route::view('/programaciones', 'admin.programaciones.index')->name('programaciones.index');
    Route::view('/agenda', 'admin.agenda.index')->name('agenda.index');
    Route::view('/coordinaciones', 'admin.coordinaciones.index')->name('coordinaciones.index');
    Route::view('/cursos', 'admin.cursos.index')->name('cursos.index');
    Route::view('/aulas', 'admin.aulas.index')->name('aulas.index');
    Route::view('/instructores', 'admin.instructores.index')->name('instructores.index');
});

// Rutas Administrativas
Route::middleware(['auth', 'role:administrador'])->prefix('admin')->name('admin.')->group(function () {

    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

    // Recursos RESTful
    Route::resource('usuarios', UserController::class)->names('users');
    Route::resource('coordinaciones', CoordinacionController::class)->parameters(['coordinaciones' => 'coordinacion'])->names('coordinaciones');
    Route::resource('cursos', CursoController::class)->names('cursos');
    Route::resource('aulas', AulaController::class)->names('aulas');
    Route::resource('grupos', GrupoController::class)->names('grupos'); // Define admin.grupos.index, .store, etc.
    Route::resource('feriados', FeriadoController::class)->parameters(['feriados' => 'feriado'])->names('feriados');

    // Auditorías
    Route::get('auditorias', [AuditoriaController::class, 'index'])->name('auditorias.index');

    // === RUTAS PARA AJAX DE FILTROS ===
    // Método AJAX para obtener grupos filtrados por coordinación
    Route::get('/grupos-por-coordinacion/{coordinacion}', [GrupoController::class, 'getGruposByCoordinacionJson'])
        ->name('grupos.por.coordinacion'); // admin.grupos.por.coordinacion

    // Método AJAX para obtener grupos visibles para el usuario
    Route::get('/grupos-visibles', [GrupoController::class, 'getGruposVisiblesPorUsuarioJson'])
        ->name('grupos.visibles'); // admin.grupos.visibles
    // === FIN RUTAS AJAX ===

    // Logout (si es específico de admin) - parece raro aquí, usualmente es global
    // Route::post('logout', [UserController::class, 'logout'])->name('logout');
});

// Autenticación Breeze
require __DIR__ . '/auth.php';
