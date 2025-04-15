<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\CoordinacionController;

/*
|--------------------------------------------------------------------------
| Redirección raíz
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('calendario.index');
});

/*
|--------------------------------------------------------------------------
| Rutas públicas protegidas por auth
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('/dashboard', fn () => view('dashboard'))->middleware('verified')->name('dashboard');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // API eventos
    Route::get('/api/eventos', [CalendarioController::class, 'eventos']);

    // Acceso común a todos los roles excepto instructores
    Route::view('/programaciones', 'admin.programaciones.index')->name('programaciones.index');
    Route::view('/agenda', 'admin.agenda.index')->name('agenda.index');
    Route::view('/coordinaciones', 'admin.coordinaciones.index')->name('coordinaciones.index');
    Route::view('/cursos', 'admin.cursos.index')->name('cursos.index');
    Route::view('/aulas', 'admin.aulas.index')->name('aulas.index');
    Route::view('/instructores', 'admin.instructores.index')->name('instructores.index');
});

/*
|--------------------------------------------------------------------------
| Rutas exclusivas para administrador
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/admin/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');

    // Usuarios y feriados
    Route::view('/usuarios', 'admin.usuarios.index')->name('users.index');
    Route::view('/feriados', 'admin.feriados.index')->name('feriados.index');

    // Usuarios (controlador)
    Route::get('/admin/usuarios', [UserController::class, 'index'])->name('users.index');
    Route::put('/admin/usuarios/{user}', [UserController::class, 'updateRole'])->name('users.updateRole');

    // Coordinaciones con CRUD
    Route::resource('admin/coordinaciones', CoordinacionController::class)
        ->parameters(['coordinaciones' => 'coordinacion']) // 👈 necesario
        ->names('coordinaciones');

});

Route::resource('admin/cursos', CursoController::class)->names('cursos');

/*
|--------------------------------------------------------------------------
| Ruta de prueba
|--------------------------------------------------------------------------
*/
Route::get('/test-role', fn () => 'Middleware role registrado')->middleware('role:administrador');

/*
|--------------------------------------------------------------------------
| Rutas de autenticación
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
