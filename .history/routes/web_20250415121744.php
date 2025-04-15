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
// ¡Importa el controlador que manejará la lógica AJAX!
// Si decides mantenerlo en GrupoController, ya está importado.
// Si creas Api/GrupoApiController, impórtalo aquí:
// use App\Http\Controllers\Api\GrupoApiController;

// 🌐 Rutas para usuarios autenticados (sin prefijo admin)
Route::middleware(['auth'])->group(function () {
    // ... (tus rutas generales aquí - calendario, profile, etc.) ...
    Route::get('/', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('/dashboard', fn () => view('dashboard'))->middleware('verified')->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/api/eventos', [CalendarioController::class, 'eventos']);
    Route::view('/programaciones', 'admin.programaciones.index')->name('programaciones.index');
    Route::view('/agenda', 'admin.agenda.index')->name('agenda.index');
    // ¡Estas rutas View::view() NO funcionarán si las vistas dependen de datos del controlador!
    // Deberías usar controladores para pasar datos a estas vistas también si es necesario.
    // Route::get('/coordinaciones', [CoordinacionController::class, 'index'])->name('coordinaciones.index'); // Ejemplo
    // Route::get('/cursos', [CursoController::class, 'index'])->name('cursos.index'); // Ejemplo
    Route::view('/coordinaciones', 'admin.coordinaciones.index')->name('coordinaciones.index'); // Manteniendo tu estructura
    Route::view('/cursos', 'admin.cursos.index')->name('cursos.index'); // Manteniendo tu estructura
    Route::view('/aulas', 'admin.aulas.index')->name('aulas.index');
    Route::view('/instructores', 'admin.instructores.index')->name('instructores.index');
});

// 🔒 Rutas Administrativas (Prefijo /admin, Nombre admin.*)
// Aplicar middleware de rol aquí es más limpio si TODO es para admin
Route::middleware(['auth', 'role:administrador'])->prefix('admin')->name('admin.')->group(function () {

    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

    // Recursos RESTful
    Route::resource('usuarios', UserController::class)->names('users');
    Route::resource('coordinaciones', CoordinacionController::class)->parameters(['coordinaciones' => 'coordinacion'])->names('coordinaciones');
    Route::resource('cursos', CursoController::class)->names('cursos');
    Route::resource('aulas', AulaController::class)->names('aulas');
    Route::resource('grupos', GrupoController::class)->names('grupos'); // Define admin.grupos.index, .store, etc.
    Route::resource('feriados', FeriadoController::class)->parameters(['feriados' => 'feriado'])->names('feriados');

    // Rutas específicas de Usuarios
    Route::put('usuarios/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
    Route::put('usuarios/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');

    // Auditorías
    Route::get('auditorias', [AuditoriaController::class, 'index'])->name('auditorias.index');

    // === RUTAS PARA AJAX DE FILTROS ===
    // Elige UN controlador para manejar esto (GrupoController o Api/GrupoApiController)
    // Usando GrupoController por ahora:
    Route::get('/grupos-por-coordinacion/{coordinacion}', [GrupoController::class, 'getGruposByCoordinacionJson']) // Cambié nombre de método para claridad
         ->name('grupos.por.coordinacion'); // Nombre final: admin.grupos.por.coordinacion

    Route::get('/grupos-visibles', [GrupoController::class, 'getGruposVisiblesPorUsuarioJson']) // Nuevo método
         ->name('grupos.visibles'); // Nombre final: admin.grupos.visibles
    // === FIN RUTAS AJAX ===

    // Logout (si es específico de admin) - parece raro aquí, usualmente es global
    // Route::post('logout', [UserController::class, 'logout'])->name('logout'); // ¿Quizás debería estar fuera?
});



// Autenticación Breeze
require __DIR__ . '/auth.php';
