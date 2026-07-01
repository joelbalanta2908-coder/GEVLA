<?php

use App\Http\Controllers\ActaController;
use App\Http\Controllers\AprendizController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CoordinacionController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\LlamadoController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\ReglamentoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de Autenticacion
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login')
    ->middleware('guest');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware('guest');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

Route::middleware('auth')->group(function () {
    
    // Consulta del Reglamento del Aprendiz (compartido por los tres roles)
    Route::get('/reglamento', [ReglamentoController::class, 'index'])->name('reglamento.index');

    // Perfil de Usuario
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/ver', [\App\Http\Controllers\PerfilController::class, 'show'])->name('show');
        Route::get('/editar', [\App\Http\Controllers\PerfilController::class, 'edit'])->name('edit');
        Route::put('/actualizar', [\App\Http\Controllers\PerfilController::class, 'update'])->name('update');
        Route::get('/ayuda', [\App\Http\Controllers\PerfilController::class, 'help'])->name('help');
    });
    // Rutas de Aprendiz
    Route::prefix('aprendiz')->name('aprendiz.')->group(function () {
        Route::get('/dashboard', [AprendizController::class, 'dashboard'])->name('dashboard');
        
        // Historial (Solo lectura)
        Route::get('/llamados', [AprendizController::class, 'llamados'])->name('llamados.index');
        Route::get('/llamados/{id}', [AprendizController::class, 'showLlamado'])->name('llamados.show');
        
        Route::get('/actas', [AprendizController::class, 'actas'])->name('actas.index');
        Route::get('/actas/{id}', [AprendizController::class, 'showActa'])->name('actas.show');
        
        Route::get('/procesos', [AprendizController::class, 'procesos'])->name('procesos.index');
        Route::get('/procesos/{id}', [AprendizController::class, 'showProceso'])->name('procesos.show');

        Route::get('/notificaciones', [AprendizController::class, 'notificaciones'])->name('notificaciones.index');
    });

    // Rutas de Instructor
    Route::prefix('instructor')->name('instructor.')->group(function () {
        Route::get('/dashboard', [InstructorController::class, 'dashboard'])->name('dashboard');

        // Fichas a cargo y hoja de vida del aprendiz
        Route::get('/fichas', [InstructorController::class, 'fichas'])->name('fichas.index');
        Route::get('/aprendices/{id}', [InstructorController::class, 'aprendizShow'])->name('aprendices.show');

        // Seguimiento de procesos y notificaciones
        Route::get('/procesos', [InstructorController::class, 'procesos'])->name('procesos.index');
        Route::get('/notificaciones', [InstructorController::class, 'notificaciones'])->name('notificaciones.index');

        // Exportación de reportes (PDF imprimible / Excel / Word).
        // Debe registrarse ANTES del resource para no chocar con /llamados/{llamado}.
        Route::get('llamados/export/{formato}', [\App\Http\Controllers\InstructorLlamadoController::class, 'export'])
            ->where('formato', 'pdf|excel|word')
            ->name('llamados.export');

        // Gestión de Llamados (CRUD)
        Route::resource('llamados', \App\Http\Controllers\InstructorLlamadoController::class)->parameters(['llamados' => 'llamado']);
    });

    // Rutas de Coordinación
    Route::prefix('coordinacion')->name('coordinacion.')->group(function () {
        Route::get('/dashboard', [CoordinacionController::class, 'dashboard'])->name('dashboard');

        // Aprendices (listado y hoja de vida)
        Route::get('/aprendices', [CoordinacionController::class, 'aprendices'])->name('aprendices.index');
        Route::get('/aprendices/{id}', [CoordinacionController::class, 'aprendizShow'])->name('aprendices.show');

        // Fichas (con instructor líder e instructores asignados)
        Route::get('/fichas', [CoordinacionController::class, 'fichas'])->name('fichas.index');

        // Exportación de reportes (PDF / Excel / Word). Deben ir ANTES de los
        // resource para no chocar con /{llamado}, /{acta}, /{proceso}.
        Route::get('llamados/export/{formato}', [\App\Http\Controllers\CoordinacionReporteController::class, 'llamados'])->where('formato', 'pdf|excel|word')->name('llamados.export');
        Route::get('actas/export/{formato}', [\App\Http\Controllers\CoordinacionReporteController::class, 'actas'])->where('formato', 'pdf|excel|word')->name('actas.export');
        Route::get('procesos/export/{formato}', [\App\Http\Controllers\CoordinacionReporteController::class, 'procesos'])->where('formato', 'pdf|excel|word')->name('procesos.export');

        // Llamados de atención
        Route::resource('llamados', LlamadoController::class)->parameters(['llamados' => 'llamado']);
        Route::patch('llamados/{llamado}/estado', [LlamadoController::class, 'actualizarEstado'])->name('llamados.actualizarEstado');

        // Actas de coordinación
        Route::resource('actas', ActaController::class)->parameters(['actas' => 'acta']);

        // Procesos disciplinarios
        Route::resource('procesos', ProcesoController::class)->parameters(['procesos' => 'proceso']);
        Route::post('procesos/{proceso}/historial', [ProcesoController::class, 'guardarHistorial'])->name('procesos.historial.store');
    });
});

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->tieneRol('Coordinador')) return redirect()->route('coordinacion.dashboard');
        if ($user->tieneRol('Instructor')) return redirect()->route('instructor.dashboard');
        if ($user->tieneRol('Aprendiz')) return redirect()->route('aprendiz.dashboard');
    }
    return redirect()->route('login');
});