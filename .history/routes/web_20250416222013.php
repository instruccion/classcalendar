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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/api/eventos', [CalendarioController::class, 'eventos']);

    // Vistas directas (a futuro puedes migrarlas a controladores)
    Route::view('/programaciones', 'admin.programaciones.index')->name('programaciones.index');
    Route::view('/agenda', 'admin.agenda.index')->name('agenda.index');
    Route::view('/cursos', 'admin.cursos.index')->name('cursos.index');
    Route::view('/aulas', 'admin.aulas.index')->name('aulas.index');
    Route::view('/instructores', 'admin.instructores.index')->name('instructores.index');

    // Coordinaciones
    Route::get('/coordinaciones', [CoordinacionController::class, 'index'])->name('coordinaciones.index');
    Route::get('coordinaciones/{coordinacion}/edit', [CoordinacionController::class, 'edit'])->name('coordinaciones.edit');
    Route::post('coordinaciones', [CoordinacionController::class, 'store'])->name('coordinaciones.store');
    Route::resource('coordinaciones', CoordinacionController::class)
        ->parameters(['coordinaciones' => 'coordinacion'])
        ->names('coordinaciones');

    // 🔥 Este delete estaba fuera de los resource, lo mantenemos por compatibilidad
    Route::delete('admin/cursos/{curso}', [CursoController::class, 'destroy'])->name('admin.cursos.destroy');
});

// Rutas accesibles por administrador, coordinador y analista
Route::middleware(['auth', 'role:administrador,coordinador,analista'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('cursos', CursoController::class)->names('cursos');
    Route::resource('grupos', GrupoController::class)->names('grupos'); // ✅ ¡Ruta corregida!

    // === RUTAS PARA AJAX DE FILTROS ===
    Route::get('/grupos-por-coordinacion/{coordinacion}', [GrupoController::class, 'getGruposByCoordinacionJson'])
        ->name('grupos.por.coordinacion');

    Route::get('/grupos-visibles', [GrupoController::class, 'getGruposVisiblesPorUsuarioJson'])
        ->name('grupos.visibles');

    Route::get('cursos/{curso}/edit', [CursoController::class, 'edit'])->name('cursos.edit');
    // ⚠️ Ya no uses 'admin.cursos.edit' porque la ruta está dentro de este grupo con alias 'admin.'
});

// Rutas exclusivas del administrador
Route::middleware(['auth', 'role:administrador'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
    Route::resource('usuarios', UserController::class)->names('users');
    Route::resource('coordinaciones', CoordinacionController::class)
        ->parameters(['coordinaciones' => 'coordinacion'])
        ->names('coordinaciones');
    Route::resource('aulas', AulaController::class)->names('aulas');
    Route::resource('feriados', FeriadoController::class)
        ->parameters(['feriados' => 'feriado'])
        ->names('feriados');
    Route::get('auditorias', [AuditoriaController::class, 'index'])->name('auditorias.index');
});

// Autenticación Breeze
require __DIR__ . '/auth.php';
