<?php

use App\Http\Controllers\ActaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CoordinacionController;
use App\Http\Controllers\LlamadoController;
use App\Http\Controllers\ProcesoController;
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
    Route::view('/aprendiz/dashboard', 'dashboards.aprendiz')->name('aprendiz.dashboard');
    Route::view('/instructor/dashboard', 'dashboards.instructor')->name('instructor.dashboard');

    // Rutas de Coordinación
    Route::prefix('coordinacion')->name('coordinacion.')->group(function () {
        Route::get('/dashboard', [CoordinacionController::class, 'dashboard'])->name('dashboard');

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

Route::get('/', fn () => redirect()->route('login'));