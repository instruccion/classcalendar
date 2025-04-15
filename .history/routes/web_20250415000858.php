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

/*
|--------------------------------------------------------------------------|
| Rutas protegidas (acceso general para usuarios autenticados) |
|--------------------------------------------------------------------------|
*/
Route::middleware(['auth'])->group(function () {
    // Página principal redirige al calendario
    Route::get('/', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('/dashboard', fn () => view('dashboard'))->middleware('verified')->name('dashboard');

    // Perfil del usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Eventos del calendario (API)
    Route::get('/api/eventos', [CalendarioController::class, 'eventos']);

    // Vistas compartidas entre roles (excepto instructores)
    Route::view('/programaciones', 'admin.programaciones.index')->name('programaciones.index');
    Route::view('/agenda', 'admin.agenda.index')->name('agenda.index');
    Route::view('/coordinaciones', 'admin.coordinaciones.index')->name('coordinaciones.index');
    Route::view('/cursos', 'admin.cursos.index')->name('cursos.index');
    Route::view('/aulas', 'admin.aulas.index')->name('aulas.index');
    Route::view('/instructores', 'admin.instructores.index')->name('instructores.index');
});

/*
|--------------------------------------------------------------------------|
| Rutas exclusivas para administrador |
|--------------------------------------------------------------------------|
*/
Route::middleware(['auth', 'role:administrador'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard de administración
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

    // Usuarios
    Route::resource('usuarios', UserController::class)->names('users');
    Route::put('usuarios/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
    Route::put('usuarios/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');

    // Coordinaciones, cursos, aulas, grupos
    Route::resource('coordinaciones', CoordinacionController::class)->parameters(['coordinaciones' => 'coordinacion']);
    Route::resource('cursos', CursoController::class)->names('cursos');
    Route::resource('aulas', AulaController::class)->names('aulas');
    Route::resource('grupos', GrupoController::class)->names('grupos');
    Route::delete('/admin/grupos/{grupo}', [GrupoController::class, 'destroy'])->name('grupos.destroy');
    Route::post('/admin/grupos', [GrupoController::class, 'store'])->name('grupos.store');
    

    // Auditorías
    Route::get('auditorias', [AuditoriaController::class, 'index'])->name('auditorias.index');

    // Feriados
    Route::resource('feriados', FeriadoController::class)  // Si necesitas este controlador
        ->parameters(['feriados' => 'feriado'])
        ->names('feriados');

    // Cerrar sesión (si aplica lógica personalizada)
    Route::post('logout', [UserController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------|
| Autenticación (Laravel Breeze) |
|--------------------------------------------------------------------------|
*/
require __DIR__ . '/auth.php';
