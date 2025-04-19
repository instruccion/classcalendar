<?php

use Illuminate\Support\Facades\Route;
// Asegúrate de que el namespace y el nombre del controlador son correctos
use App\Http\Controllers\Admin\ProgramacionController;
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
    // Comentado para evitar posible conflicto con Route::resource('programaciones',...)
    // Route::view('/programaciones', 'admin.programaciones.index')->name('programaciones.index');
    Route::view('/agenda', 'admin.agenda.index')->name('agenda.index');
    Route::view('/cursos', 'admin.cursos.index')->name('cursos.index');
    Route::view('/aulas', 'admin.aulas.index')->name('aulas.index');
});

// Rutas para administrador, coordinador y analista
Route::middleware(['auth', 'role:administrador,coordinador,analista'])->prefix('admin')->name('admin.')->group(function () {

    // Recursos existentes
    Route::resource('cursos', CursoController::class)->names('cursos');
    Route::resource('grupos', GrupoController::class)->names('grupos');
    Route::resource('aulas', AulaController::class)->names('aulas');

    // --- INICIO: NUEVAS RUTAS PARA PROGRAMACIONES ---
    // Esto crea las rutas: admin.programaciones.index, .create, .store, .show, .edit, .update, .destroy
    Route::resource('programaciones', ProgramacionController::class);

    // Ruta para la interfaz de programar por bloque
    Route::get('programar-bloque', [ProgramacionController::class, 'showProgramarBloque'])
         ->name('programaciones.bloque.show'); // Nombre: admin.programaciones.bloque.show

    // --- RUTAS API PARA LA INTERFAZ DE PROGRAMACIÓN ---
    // (Los nombres tendrán prefijo 'admin.')
    Route::get('api/grupos/{grupo}/cursos', [ProgramacionController::class, 'getCursosPorGrupoApi'])
         ->name('api.programaciones.cursosPorGrupo'); // Nombre: admin.api.programaciones.cursosPorGrupo
    Route::get('api/cursos/{curso}/instructores', [ProgramacionController::class, 'getInstructoresPorCursoApi'])
         ->name('api.programaciones.instructoresPorCurso'); // Nombre: admin.api.programaciones.instructoresPorCurso
    Route::post('api/programaciones/calcular-fecha-fin', [ProgramacionController::class, 'calcularFechaFinApi'])
          ->name('api.programaciones.calcularFechaFin'); // Nombre: admin.api.programaciones.calcularFechaFin
    Route::get('api/programaciones/verificar-disponibilidad', [ProgramacionController::class, 'verificarDisponibilidadApi'])
         ->name('api.programaciones.verificarDisponibilidad'); // Nombre: admin.api.programaciones.verificarDisponibilidad
    Route::get('api/programaciones/detalle-disponibilidad', [ProgramacionController::class, 'getDetalleDisponibilidadApi'])
         ->name('api.programaciones.detalleDisponibilidad'); // Nombre: admin.api.programaciones.detalleDisponibilidad
    // --- FIN: NUEVAS RUTAS PARA PROGRAMACIONES ---


    // Rutas para documentos de instructores
    Route::get('instructores/{instructor}/documentos', [InstructorController::class, 'documentos'])->name('instructores.documentos');
    Route::post('instructores/{instructor}/documentos', [InstructorController::class, 'asignarDocumento'])->name('instructores.asignarDocumento');
    Route::post('instructores/{instructor}/documentos/manual', [InstructorController::class, 'asignarDocumentoManual'])->name('instructores.asignarDocumentoManual');
    // Corregido el nombre duplicado que a veces aparecía en prompts anteriores
    Route::put('instructores/documentos/{pivot}', [InstructorController::class, 'actualizarDocumento'])->name('instructores.actualizarDocumento');

    // Resource general para instructores
    Route::resource('instructores', InstructorController::class)->except(['show'])->names('instructores');

    // AJAX: filtros de coordinación
    Route::get('/grupos-por-coordinacion/{coordinacion}', [GrupoController::class, 'getGruposByCoordinacionJson'])->name('grupos.por.coordinacion');
    Route::get('/grupos-visibles', [GrupoController::class, 'getGruposVisiblesPorUsuarioJson'])->name('grupos.visibles');

    // Edición modal JSON para cursos
    Route::get('cursos/{curso}/edit', [CursoController::class, 'edit'])->name('cursos.edit');

}); // <-- FIN DEL GRUPO admin/coordinador/analista

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

// Rutas de autenticación (Asegúrate que este archivo exista y contenga las rutas de Breeze/Fortify)
require __DIR__ . '/auth.php';
