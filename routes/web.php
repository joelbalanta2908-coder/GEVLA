<?php

use App\Http\Controllers\ActaController;
use App\Http\Controllers\AprendizController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CoordinacionController;
use App\Http\Controllers\FichaController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\LlamadoController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\ReglamentoController;
use App\Http\Controllers\RolController;
use App\Support\Roles;
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

// Recuperación de contraseña en 3 pasos: correo → código → nueva contraseña.
// Los POST llevan throttle para frenar abusos (envíos y adivinanza de códigos).
Route::middleware('guest')->group(function () {
    Route::get('/recuperar', [\App\Http\Controllers\Auth\RecuperacionController::class, 'mostrarSolicitud'])->name('recuperar.solicitud');
    Route::post('/recuperar', [\App\Http\Controllers\Auth\RecuperacionController::class, 'enviarCodigo'])->middleware('throttle:6,1')->name('recuperar.enviar');
    Route::get('/recuperar/codigo', [\App\Http\Controllers\Auth\RecuperacionController::class, 'mostrarCodigo'])->name('recuperar.codigo');
    Route::post('/recuperar/codigo', [\App\Http\Controllers\Auth\RecuperacionController::class, 'verificarCodigo'])->middleware('throttle:10,1')->name('recuperar.verificar');
    Route::post('/recuperar/reenviar', [\App\Http\Controllers\Auth\RecuperacionController::class, 'reenviarCodigo'])->middleware('throttle:3,1')->name('recuperar.reenviar');
    Route::get('/recuperar/nueva', [\App\Http\Controllers\Auth\RecuperacionController::class, 'mostrarNueva'])->name('recuperar.nueva');
    Route::post('/recuperar/nueva', [\App\Http\Controllers\Auth\RecuperacionController::class, 'guardarNueva'])->name('recuperar.guardar');
});

Route::middleware('auth')->group(function () {

    // Cambio dinámico de rol activo (para usuarios con varios roles asignados).
    Route::post('/rol/cambiar', [RolController::class, 'cambiar'])->name('rol.cambiar');

    // Consulta del Reglamento del Aprendiz (compartido por los tres roles)
    Route::get('/reglamento', [ReglamentoController::class, 'index'])->name('reglamento.index');

    // Perfil de Usuario
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/ver', [\App\Http\Controllers\PerfilController::class, 'show'])->name('show');
        Route::get('/editar', [\App\Http\Controllers\PerfilController::class, 'edit'])->name('edit');
        Route::put('/actualizar', [\App\Http\Controllers\PerfilController::class, 'update'])->name('update');
        Route::get('/ayuda', [\App\Http\Controllers\PerfilController::class, 'help'])->name('help');
    });

    // Endpoint general para marcar notificaciones como recibidas (usable por diferentes roles)
    // Notificaciones de la campanita (estado de lectura persistente por usuario)
    Route::post('/notificaciones/marcar-todas', [\App\Http\Controllers\NotificacionController::class, 'marcarTodas'])->name('notificaciones.todas');
    Route::post('/notificaciones/{notificacion}/leida', [\App\Http\Controllers\NotificacionController::class, 'marcarLeida'])->name('notificaciones.leida');
    // Rutas de Aprendiz
    Route::prefix('aprendiz')->name('aprendiz.')->middleware('rol:Aprendiz')->group(function () {
        Route::get('/dashboard', [AprendizController::class, 'dashboard'])->name('dashboard');

        // Exportación de reportes propios (PDF / Excel / Word). Deben ir ANTES de
        // las rutas /{id} para no colisionar.
        Route::get('llamados/export/{formato}', [\App\Http\Controllers\AprendizReporteController::class, 'llamados'])->where('formato', 'pdf|excel|word')->name('llamados.export');
        Route::get('actas/export/{formato}', [\App\Http\Controllers\AprendizReporteController::class, 'actas'])->where('formato', 'pdf|excel|word')->name('actas.export');
        Route::get('procesos/export/{formato}', [\App\Http\Controllers\AprendizReporteController::class, 'procesos'])->where('formato', 'pdf|excel|word')->name('procesos.export');

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
    Route::prefix('instructor')->name('instructor.')->middleware('rol:Instructor')->group(function () {
        Route::get('/dashboard', [InstructorController::class, 'dashboard'])->name('dashboard');

        // Fichas a cargo e información del aprendiz
        Route::get('/fichas', [InstructorController::class, 'fichas'])->name('fichas.index');
        // Vista adicional: aprendices asociados a la ficha seleccionada.
        Route::get('/fichas/{ficha}/aprendices', [InstructorController::class, 'fichaAprendices'])->name('fichas.aprendices');
        // Consulta del historial disciplinario de los aprendices de una ficha
        // (disponible para todos los instructores asociados a la ficha).
        Route::get('/fichas/{ficha}', [InstructorController::class, 'fichaShow'])->name('fichas.show');
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
    Route::prefix('coordinacion')->name('coordinacion.')->middleware('rol:Coordinador')->group(function () {
        Route::get('/dashboard', [CoordinacionController::class, 'dashboard'])->name('dashboard');

        // Centro de reportes (generación y exportación con filtro por ficha)
        Route::get('/reportes', [CoordinacionController::class, 'reportes'])->name('reportes.index');

        // Aprendices (listado, alta, hoja de vida y activación/inactivación)
        Route::get('/aprendices', [CoordinacionController::class, 'aprendices'])->name('aprendices.index');
        Route::get('/aprendices/crear', [CoordinacionController::class, 'crearAprendizForm'])->name('aprendices.crear');
        Route::post('/aprendices', [CoordinacionController::class, 'crearAprendiz'])->name('aprendices.store');
        Route::patch('/aprendices/{aprendiz}/estado', [CoordinacionController::class, 'actualizarEstadoAprendiz'])->name('aprendices.estado');
        Route::get('/aprendices/{aprendiz}/editar', [CoordinacionController::class, 'editarAprendizForm'])->name('aprendices.editar');
        Route::put('/aprendices/{aprendiz}', [CoordinacionController::class, 'actualizarAprendiz'])->name('aprendices.update');
        Route::get('/aprendices/{id}', [CoordinacionController::class, 'aprendizShow'])->name('aprendices.show');

        // Coordinadores: CRUD completo (crear, editar, activar/inactivar y
        // eliminar con validaciones). La ruta /crear va ANTES de /{coordinador}.
        Route::get('/coordinadores', [CoordinacionController::class, 'coordinadores'])->name('coordinadores.index');
        Route::get('/coordinadores/crear', [CoordinacionController::class, 'crearCoordinadorForm'])->name('coordinadores.crear');
        Route::post('/coordinadores', [CoordinacionController::class, 'crearCoordinador'])->name('coordinadores.store');
        Route::get('/coordinadores/{coordinador}/editar', [CoordinacionController::class, 'editarCoordinadorForm'])->name('coordinadores.editar');
        Route::put('/coordinadores/{coordinador}', [CoordinacionController::class, 'actualizarCoordinador'])->name('coordinadores.update');
        Route::patch('/coordinadores/{coordinador}/estado', [CoordinacionController::class, 'actualizarEstadoCoordinador'])->name('coordinadores.estado');
        Route::delete('/coordinadores/{coordinador}', [CoordinacionController::class, 'eliminarCoordinador'])->name('coordinadores.destroy');

        // Docentes (instructores): alta, fichas asignadas, liderazgo, tipo
        // (materia/transversal) y activación/inactivación. La ruta /crear debe
        // declararse ANTES de /{instructor} para no colisionar.
        Route::get('/docentes', [CoordinacionController::class, 'docentes'])->name('docentes.index');
        Route::get('/docentes/crear', [CoordinacionController::class, 'crearDocenteForm'])->name('docentes.crear');
        Route::post('/docentes', [CoordinacionController::class, 'crearDocente'])->name('docentes.store');
        Route::patch('/docentes/{instructor}/tipo', [CoordinacionController::class, 'actualizarTipoDocente'])->name('docentes.tipo');
        Route::patch('/docentes/{instructor}/estado', [CoordinacionController::class, 'actualizarEstadoDocente'])->name('docentes.estado');
        Route::get('/docentes/{instructor}/editar', [CoordinacionController::class, 'editarDocenteForm'])->name('docentes.editar');
        Route::put('/docentes/{instructor}', [CoordinacionController::class, 'actualizarDocente'])->name('docentes.update');
        Route::delete('/docentes/{instructor}', [CoordinacionController::class, 'eliminarDocente'])->name('docentes.destroy');
        Route::get('/docentes/{instructor}', [CoordinacionController::class, 'docenteShow'])->name('docentes.show');

        // Gestión de Fichas (CRUD + asociaciones + instructor líder).
        // Las acciones específicas van declaradas junto al resource; usan verbos
        // distintos a GET show, por lo que no colisionan con /fichas/{ficha}.
        Route::patch('fichas/{ficha}/estado', [FichaController::class, 'actualizarEstado'])->name('fichas.actualizarEstado');
        Route::post('fichas/{ficha}/instructores', [FichaController::class, 'asociarInstructores'])->name('fichas.instructores.store');
        Route::post('fichas/{ficha}/instructores/nuevo', [FichaController::class, 'crearInstructor'])->name('fichas.instructores.crear');
        Route::delete('fichas/{ficha}/instructores/{instructor}', [FichaController::class, 'eliminarInstructor'])->name('fichas.instructores.destroy');
        Route::put('fichas/{ficha}/lider', [FichaController::class, 'asignarLider'])->name('fichas.lider');
        Route::post('fichas/{ficha}/aprendices', [FichaController::class, 'asociarAprendices'])->name('fichas.aprendices.store');
        Route::delete('fichas/{ficha}/aprendices/{matricula}', [FichaController::class, 'retirarAprendiz'])->name('fichas.aprendices.destroy');
        Route::resource('fichas', FichaController::class)->parameters(['fichas' => 'ficha']);

        // Programas de formación (catálogo base de las fichas).
        Route::resource('programas', ProgramaController::class)
            ->parameters(['programas' => 'programa'])
            ->except(['show']);

        // Exportación de reportes (PDF / Excel / Word). Deben ir ANTES de los
        // resource para no chocar con /{llamado}, /{acta}, /{proceso}.
        Route::get('llamados/export/{formato}', [\App\Http\Controllers\CoordinacionReporteController::class, 'llamados'])->where('formato', 'pdf|excel|word')->name('llamados.export');
        Route::get('actas/export/{formato}', [\App\Http\Controllers\CoordinacionReporteController::class, 'actas'])->where('formato', 'pdf|excel|word')->name('actas.export');
        Route::get('procesos/export/{formato}', [\App\Http\Controllers\CoordinacionReporteController::class, 'procesos'])->where('formato', 'pdf|excel|word')->name('procesos.export');

        // Llamados de atención
        Route::resource('llamados', LlamadoController::class)->parameters(['llamados' => 'llamado']);
        Route::patch('llamados/{llamado}/estado', [LlamadoController::class, 'actualizarEstado'])->name('llamados.actualizarEstado');

        // Actas de coordinación. La ruta AJAX de aprendices por ficha se declara
        // ANTES del resource para no colisionar con /actas/{acta}.
        Route::get('actas/aprendices-por-ficha/{ficha}', [ActaController::class, 'aprendicesPorFicha'])->name('actas.aprendicesPorFicha');
        Route::resource('actas', ActaController::class)->parameters(['actas' => 'acta']);

        // Procesos disciplinarios
        Route::resource('procesos', ProcesoController::class)->parameters(['procesos' => 'proceso']);
        Route::post('procesos/{proceso}/historial', [ProcesoController::class, 'guardarHistorial'])->name('procesos.historial.store');
    });
});

Route::get('/', function () {
    if (auth()->check()) {
        // Redirige al dashboard del rol activo (o al rol por defecto del backend).
        $rol = session('rol_activo') ?? Roles::porDefecto(auth()->user());
        if ($rol) {
            return redirect()->route(Roles::dashboardRoute($rol));
        }
    }
    return redirect()->route('login');
});