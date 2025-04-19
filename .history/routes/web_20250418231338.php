<?php

use Illuminate\Support\Facades\Route;
// QUITARÉ la importación de ProgramacionController por ahora
// use App\Http\Controllers\Admin\ProgramacionController;
use App\Http\Controllers\{
    ProfileController,
    UserController,
    CalendarioController,
    CoordinacionController,
    CursoController,
    AulaController,
    AuditoriaController,
    GrupoController,
    FeriadoController,
    InstructorController,
    // Quitado ProgramacionController de aquí también
};

// Rutas generales autenticadas (no restringidas por rol)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('/dashboard', fn () => view('dashboard'))->middleware('verified')->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/api/eventos', [CalendarioController::class, 'eventos']);

    // Vistas directas
    Route::view('/programaciones', 'admin.programaciones.index')->name('programaciones.index');
    Route::view('/agenda', 'admin.agenda.index')->name('agenda.index');
    Route::view('/cursos', 'admin.cursos.index')->name('cursos.index');
    Route::view('/aulas', 'admin.aulas.index')->name('aulas.index');
});

// Rutas para administrador, coordinador y analista
Route::middleware(['auth', 'role:administrador,coordinador,analista'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('cursos', CursoController::class)->names('cursos');
    Route::resource('grupos', GrupoController::class)->names('grupos');
    Route::resource('aulas', AulaController::class)->names('aulas');

    // Rutas para documentos de instructores (deben ir antes del resource general)
    Route::get('instructores/{instructor}/documentos', [InstructorController::class, 'documentos'])->name('instructores.documentos');
    Route::post('instructores/{instructor}/documentos', [InstructorController::class, 'asignarDocumento'])->name('instructores.asignarDocumento');
    Route::post('instructores/{instructor}/documentos/manual', [InstructorController::class, 'asignarDocumentoManual'])->name('instructores.asignarDocumentoManual');
    // Asegúrate de que el nombre aquí sea el correcto según tu controlador
    Route::put('instructores/documentos/{pivot}', [InstructorController::class, 'actualizarDocumento'])->name('instructores.actualizarDocumento'); // Quitado admin. extra


    // Resource general para instructores (debe ir después de las rutas personalizadas)
    Route::resource('instructores', InstructorController::class)->except(['show'])->names('instructores');

    // AJAX: filtros de coordinación
    Route::get('/grupos-por-coordinacion/{coordinacion}', [GrupoController::class, 'getGruposByCoordinacionJson'])->name('grupos.por.coordinacion');
    Route::get('/grupos-visibles', [GrupoController::class, 'getGruposVisiblesPorUsuarioJson'])->name('grupos.visibles');

    // Edición modal JSON para cursos
    Route::get('cursos/{curso}/edit', [CursoController::class, 'edit'])->name('cursos.edit');

    // --- AQUÍ NO AÑADIREMOS NADA TODAVÍA ---

});

// Rutas exclusivas para administrador
Route::middleware(['auth', 'role:administrador'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
    Route::resource('usuarios', UserController::class)->names('users');
    Route::resource('coordinaciones', CoordinacionController::class)
        ->parameters(['coordinaciones' => 'coordinacion'])
        ->names('coordinaciones');
    Route::resource('feriados', FeriadoController::class)
        ->parameters(['feriados' => 'feriado'])
        ->names('feriados');
    Route::get('auditorias', [AuditoriaController::class, 'index'])->name('auditorias.index');
});

require __DIR__ . '/auth.php';
