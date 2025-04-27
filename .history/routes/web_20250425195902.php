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
    FeriadoController,
    InstructorController,
    ConfirmacionInstructorController,
    MiAgendaController
};
use App\Http\Controllers\Admin\{
    ProgramacionController,
    ProgramacionBloqueController
};

// =====================
// Rutas Generales (todos los usuarios autenticados)
// =====================
Route::middleware(['auth'])->group(function () {
    Route::get('/', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('/dashboard', fn () => view('dashboard'))->middleware('verified')->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/api/eventos', [CalendarioController::class, 'eventos']);

    // API para eventos de Mi Agenda (solo instructores)
    Route::get('/api/mi-agenda', [MiAgendaController::class, 'index'])
        ->middleware('role:instructor')
        ->name('api.mi-agenda');

    // Vista Mi Agenda (solo instructores)
    Route::middleware('role:instructor')->group(function () {
        Route::view('/mi-agenda', 'instructores.agenda')->name('instructores.agenda');
    });

    // Vista Agenda para Administradores
    Route::middleware('role:administrador')->group(function () {
        Route::get('instructores/agenda', [MiAgendaController::class, 'agendaAdministrador'])->name('agenda.instructor');
    });

    // Vistas directas
    Route::view('/agenda', 'admin.agenda.index')->name('agenda.index');
    Route::view('/cursos', 'admin.cursos.index')->name('cursos.index');
    Route::view('/aulas', 'admin.aulas.index')->name('aulas.index');
    Route::get('/programaciones', [ProgramacionController::class, 'index'])->name('admin.programaciones.index');
});

// =====================
// Rutas para Administrador, Coordinador y Analista
// =====================
Route::middleware(['auth', 'role:administrador,coordinador,analista'])->prefix('admin')->name('admin.')->group(function () {
    // Gestión de cursos, grupos y aulas
    Route::resource('cursos', CursoController::class)->names('cursos');
    Route::resource('grupos', GrupoController::class)->names('grupos');
    Route::resource('aulas', AulaController::class)->names('aulas');

    // Gestión de documentos de instructores
    Route::get('instructores/{instructor}/documentos', [InstructorController::class, 'documentos'])->name('instructores.documentos');
    Route::post('instructores/{instructor}/documentos', [InstructorController::class, 'asignarDocumento'])->name('instructores.asignarDocumento');
    Route::post('instructores/{instructor}/documentos/manual', [InstructorController::class, 'asignarDocumentoManual'])->name('instructores.asignarDocumentoManual');
    Route::put('instructores/documentos/{pivot}', [InstructorController::class, 'actualizarDocumento'])->name('instructores.actualizarDocumento');

    // Programaciones y bloques
    Route::resource('programaciones', ProgramacionController::class)->parameters(['programaciones' => 'programacion']);

    Route::prefix('programaciones/bloque')->name('programaciones.bloque.')->group(function () {
        Route::get('/', [ProgramacionBloqueController::class, 'index'])->name('index');
        Route::get('/cursos', [ProgramacionBloqueController::class, 'getCursosPorGrupo'])->name('cursos');
        Route::get('/ordenar', [ProgramacionBloqueController::class, 'ordenar'])->name('ordenar');
        Route::post('/', [ProgramacionBloqueController::class, 'store'])->name('store');
        Route::get('/grupo/{grupo}/codigo/{bloque_codigo?}/edit', [ProgramacionBloqueController::class, 'editBloque'])->name('edit');
        Route::put('/grupo/{grupo}/codigo/{bloque_codigo?}', [ProgramacionBloqueController::class, 'updateBloque'])->name('update');
    });

    // API relacionadas a programación
    Route::prefix('api')->group(function () {
        Route::get('grupos/{grupo}/cursos', [ProgramacionController::class, 'getCursosPorGrupoApi'])->name('programaciones.cursosPorGrupo');
        Route::get('cursos/{curso}/instructores', [ProgramacionController::class, 'getInstructoresPorCursoApi'])->name('programaciones.instructoresPorCurso');
        Route::post('programaciones/calcular-fecha-fin', [ProgramacionController::class, 'calcularFechaFinApi'])->name('programaciones.calcularFechaFin');
        Route::get('programaciones/verificar-disponibilidad', [ProgramacionController::class, 'verificarDisponibilidadApi'])->name('programaciones.verificarDisponibilidad');
        Route::get('programaciones/detalle-disponibilidad', [ProgramacionController::class, 'getDetalleDisponibilidadApi'])->name('programaciones.detalleDisponibilidad');
    });

    // Confirmación de instructores
    Route::get('instructor/confirmar/{token}', [ConfirmacionInstructorController::class, 'mostrar'])->name('instructor.confirmar');
    Route::post('instructor/confirmar/{token}', [ConfirmacionInstructorController::class, 'procesar'])->name('instructor.confirmar.enviar');

    // Gestión de instructores
    Route::resource('instructores', InstructorController::class)->except(['show'])->names('instructores');

    // AJAX para filtros
    Route::get('/grupos-por-coordinacion/{coordinacion}', [GrupoController::class, 'getGruposByCoordinacionJson'])->name('grupos.por.coordinacion');
    Route::get('/grupos-visibles', [GrupoController::class, 'getGruposVisiblesPorUsuarioJson'])->name('grupos.visibles');

    // Edición modal JSON de cursos
    Route::get('cursos/{curso}/edit', [CursoController::class, 'edit'])->name('cursos.edit');
});

// =====================
// Rutas exclusivas para Administradores
// =====================
Route::middleware(['auth', 'role:administrador'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');
    Route::resource('usuarios', UserController::class)->names('users');
    Route::resource('coordinaciones', CoordinacionController::class)->parameters(['coordinaciones' => 'coordinacion'])->names('coordinaciones');
    Route::resource('feriados', FeriadoController::class)->parameters(['feriados' => 'feriado'])->names('feriados');
    Route::get('auditorias', [AuditoriaController::class, 'index'])->name('auditorias.index');

    // Agenda de instructores (selector por instructor)
    Route::get('/agenda-instructor', [ConfirmacionInstructorController::class, 'selectorAgenda'])->name('agenda.selector');
    Route::get('/agenda-instructor/{instructor}', [MiAgendaController::class, 'agendaInstructor'])->name('agenda.instructor');
});

// =====================
// Rutas de Autenticación
// =====================
require __DIR__.'/auth.php';
