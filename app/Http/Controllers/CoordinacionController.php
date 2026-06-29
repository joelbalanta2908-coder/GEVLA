<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActaCoordinacion;
use App\Models\LlamadoAtencion;
use App\Models\ProcesoDisciplinario;
use Illuminate\View\View;

class CoordinacionController extends Controller
{
    /**
     * Muestra el dashboard del coordinador con las estadísticas generales.
     */
    public function dashboard(): View
    {
        $totalLlamados = LlamadoAtencion::count();
        $llamadosPendientes = LlamadoAtencion::whereIn('estado_llamado', ['registrado', 'en_revision'])->count();

        $totalActas = ActaCoordinacion::count();
        $actasExpedidas = ActaCoordinacion::where('estado_acta', 'expedido')->count();

        $totalProcesos = ProcesoDisciplinario::count();
        $procesosActivos = ProcesoDisciplinario::where('estado_proceso', 'activo')->count();

        $llamadosRecientes = LlamadoAtencion::with(['aprendiz.usuario', 'instructor.usuario'])
            ->orderByDesc('fecha_llamado')
            ->limit(5)
            ->get();

        return view('dashboards.coordinador', compact(
            'totalLlamados',
            'llamadosPendientes',
            'totalActas',
            'actasExpedidas',
            'totalProcesos',
            'procesosActivos',
            'llamadosRecientes',
        ));
    }
}
