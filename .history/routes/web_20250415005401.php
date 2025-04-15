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

// 🌐 Rutas para usuarios autenticados
Route::middleware(['auth'])->group(function () {
    // Dashboard base
    Route::get('/', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('/dashboard', fn () => view('dashboard'))->middleware('verified')->name('dashboard');

    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // API de eventos
    Route::get('/api/eventos', [CalendarioController::class, 'eventos']);

    // Vistas comunes
    Route::view('/programaciones', 'admin.programaciones.index')->name('programaciones.index');
    Route::view('/agenda', 'admin.agenda.index')->name('agenda.index');
    Route::view('/coordinaciones', 'admin.coordinaciones.index')->name('coordinaciones.index');
    Route::view('/cursos', 'admin.cursos.index')->name('cursos.index');
    Route::view('/aulas', 'admin.aulas.index')->name('aulas.index');
    Route::view('/instructores', 'admin.instructores.index')->name('instructores.index');
});

// 🔒 Rutas exclusivas del administrador
Route::middleware(['auth', 'role:administrador'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

    // Usuarios
    Route::resource('usuarios', UserController::class)->names('users');
    Route::put('usuarios/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
    Route::put('usuarios/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');

    // Coordinaciones
    Route::resource('coordinaciones', CoordinacionController::class)
        ->parameters(['coordinaciones' => 'coordinacion'])
        ->names('coordinaciones');

    // Cursos
    Route::resource('cursos', CursoController::class)->names('cursos');

    // Aulas
    Route::resource('aulas', AulaController::class)->names('aulas');

    // Grupos
    Route::resource('grupos', GrupoController::class)->names('grupos');

    // 🔁 Ruta adicional para AJAX de filtro dinámico de grupos por coordinación
    Route::get('/grupos', [GrupoController::class, 'getGruposByCoordinacion']);

    // Auditorías
    Route::get('auditorias', [AuditoriaController::class, 'index'])->name('auditorias.index');

    // Feriados
    Route::resource('feriados', FeriadoController::class)
        ->parameters(['feriados' => 'feriado'])
        ->names('feriados');

    // Logout personalizado
    Route::post('logout', [UserController::class, 'logout'])->name('logout');
});

// Autenticación Breeze
require __DIR__ . '/auth.php';
