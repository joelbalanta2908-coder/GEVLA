<?php

use App\Http\Controllers\Auth\LoginController;
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
    Route::view('/coordinacion/dashboard', 'dashboards.coordinador')->name('coordinacion.dashboard');
});

Route::get('/', fn () => redirect()->route('login'));
