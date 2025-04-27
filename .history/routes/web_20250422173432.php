<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProgramacionController;
use App\Http\Controllers\Admin\ProgramacionBloqueController;
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
    Route::get('/programaciones', [ProgramacionController::class, 'index'])->name('admin.programaciones.index');
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
    Route::put('instructores/documentos/{pivot}', [InstructorController::class, 'actualizarDocumento'])->name('admin.instructores.actualizarDocumento');

    // --- INICIO: NUEVAS RUTAS PARA PROGRAMACIONES ---

    Route::get('programaciones/bloque', [ProgramacionBloqueController::class, 'index'])
        ->name('programaciones.bloque.index');

    Route::get('programaciones/bloque/cursos', [ProgramacionBloqueController::class, 'getCursosPorGrupo'])
        ->name('programaciones.bloque.cursos');

    Route::get('programaciones/bloque/ordenar', [ProgramacionBloqueController::class, 'ordenar'])
        ->name('programaciones.bloque.ordenar');

    Route::post('programaciones/bloque', [ProgramacionBloqueController::class, 'store'])->name('programaciones.bloque.store');

    


        // --- RUTAS API ---
    Route::get('api/grupos/{grupo}/cursos', [ProgramacionController::class, 'getCursosPorGrupoApi'])
        ->name('api.programaciones.cursosPorGrupo');
    Route::get('api/cursos/{curso}/instructores', [ProgramacionController::class, 'getInstructoresPorCursoApi'])
        ->name('api.programaciones.instructoresPorCurso');
    Route::post('api/programaciones/calcular-fecha-fin', [ProgramacionController::class, 'calcularFechaFinApi'])
        ->name('api.programaciones.calcularFechaFin');
    Route::get('api/programaciones/verificar-disponibilidad', [ProgramacionController::class, 'verificarDisponibilidadApi'])
        ->name('api.programaciones.verificarDisponibilidad');
    Route::get('api/programaciones/detalle-disponibilidad', [ProgramacionController::class, 'getDetalleDisponibilidadApi'])
        ->name('api.programaciones.detalleDisponibilidad');
    Route::resource('programaciones', ProgramacionController::class)->parameters([
        'programaciones' => 'programacion']);
        // --- FIN: NUEVAS RUTAS PARA PROGRAMACIONES ---

    // Resource general para instructores (debe ir después de las rutas personalizadas)
    Route::resource('instructores', InstructorController::class)->except(['show'])->names('instructores');

    // AJAX: filtros de coordinación
    Route::get('/grupos-por-coordinacion/{coordinacion}', [GrupoController::class, 'getGruposByCoordinacionJson'])->name('grupos.por.coordinacion');
    Route::get('/grupos-visibles', [GrupoController::class, 'getGruposVisiblesPorUsuarioJson'])->name('grupos.visibles');

    // Edición modal JSON para cursos
    Route::get('cursos/{curso}/edit', [CursoController::class, 'edit'])->name('cursos.edit');
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
