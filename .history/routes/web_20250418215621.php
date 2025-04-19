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
    Admin\ProgramacionController // <-- Controlador importado
};

// Rutas generales autenticadas (no restringidas por rol)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('/dashboard', fn () => view('dashboard'))->middleware('verified')->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/api/eventos', [CalendarioController::class, 'eventos']); // Asumo que esta API es pública o tiene su propio auth

    // Vistas directas (Manteniendo tu estructura original)
    // ¡OJO! Si 'admin.programaciones.index' ahora es manejado por el controlador,
    // esta línea podría necesitar removerse o ajustarse si quieres una vista estática separada.
    // Por ahora la dejamos, pero Route::resource creará admin.programaciones.index también.
    Route::view('/programaciones', 'admin.programaciones.index')->name('programaciones.index');

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

    // Define las rutas estándar (index, create, store, show, edit, update, destroy)
    // para el recurso 'programaciones' usando ProgramacionController.
    // Nombres: admin.programaciones.index, admin.programaciones.create, etc.
    Route::resource('programaciones', ProgramacionController::class);

    // Ruta específica para mostrar la interfaz de programación por bloques
    Route::get('programar-bloque', [ProgramacionController::class, 'showProgramarBloque'])
         ->name('programaciones.bloque.show'); // Nombre de ruta: admin.programaciones.bloque.show

    // --- RUTAS PARA LAS LLAMADAS API INTERNAS (Fetch/Alpine) ---
    // Las ponemos también dentro del prefijo 'admin' para mantener la consistencia
    // y heredar el middleware de autenticación/roles.

    // Obtener cursos filtrados por grupo (para el select dinámico)
    // Nota: Asumimos que los métodos API estarán en ProgramacionController por ahora.
    //       Les añadimos 'Api' al nombre del método para diferenciarlos si es necesario.
    Route::get('api/grupos/{grupo}/cursos', [ProgramacionController::class, 'getCursosPorGrupoApi'])
         ->name('api.programaciones.cursosPorGrupo'); // Nombre: admin.api.programaciones.cursosPorGrupo

    // Obtener instructores filtrados por curso (para el select dinámico)
    Route::get('api/cursos/{curso}/instructores', [ProgramacionController::class, 'getInstructoresPorCursoApi'])
         ->name('api.programaciones.instructoresPorCurso'); // Nombre: admin.api.programaciones.instructoresPorCurso

    // Calcular la fecha y hora de fin basadas en inicio y duración
    Route::post('api/programaciones/calcular-fecha-fin', [ProgramacionController::class, 'calcularFechaFinApi'])
          ->name('api.programaciones.calcularFechaFin'); // Nombre: admin.api.programaciones.calcularFechaFin

    // Verificar si un instructor o aula están ocupados en un rango
    Route::get('api/programaciones/verificar-disponibilidad', [ProgramacionController::class, 'verificarDisponibilidadApi'])
         ->name('api.programaciones.verificarDisponibilidad'); // Nombre: admin.api.programaciones.verificarDisponibilidad

    // Obtener detalles de ocupación para el modal/calendario de disponibilidad
    Route::get('api/programaciones/detalle-disponibilidad', [ProgramacionController::class, 'getDetalleDisponibilidadApi'])
         ->name('api.programaciones.detalleDisponibilidad'); // Nombre: admin.api.programaciones.detalleDisponibilidad

    // --- FIN: NUEVAS RUTAS PARA PROGRAMACIONES ---


    // Rutas para documentos de instructores (deben ir antes del resource general)
    Route::get('instructores/{instructor}/documentos', [InstructorController::class, 'documentos'])->name('instructores.documentos');
    Route::post('instructores/{instructor}/documentos', [InstructorController::class, 'asignarDocumento'])->name('instructores.asignarDocumento');
    Route::post('instructores/{instructor}/documentos/manual', [InstructorController::class, 'asignarDocumentoManual'])->name('instructores.asignarDocumentoManual');
    // ¡OJO! El nombre de esta ruta tenía 'admin.' duplicado. Corregido a:
    Route::put('instructores/documentos/{pivot}', [InstructorController::class, 'actualizarDocumento'])->name('instructores.actualizarDocumento');


    // Resource general para instructores (debe ir después de las rutas personalizadas)
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

require __DIR__ . '/auth.php';
