<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActaCoordinacion;
use App\Models\LlamadoAtencion;
use App\Models\ProcesoDisciplinario;
use Illuminate\Support\Carbon;
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

        $months = collect(range(5, 0, -1))
            ->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo))
            ->map(fn (Carbon $date) => [
                'key' => $date->format('Y-m'),
                'label' => $date->locale('es')->translatedFormat('M Y'),
            ]);

        $monthKeys = $months->pluck('key')->toArray();
        $trendLabels = $months->pluck('label')->toArray();

        $llamadosPorMes = LlamadoAtencion::selectRaw('DATE_FORMAT(fecha_llamado, "%Y-%m") as month')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('fecha_llamado', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $actasPorMes = ActaCoordinacion::selectRaw('DATE_FORMAT(fecha_expedicion, "%Y-%m") as month')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('fecha_expedicion', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $procesosPorMes = ProcesoDisciplinario::selectRaw('DATE_FORMAT(fecha_inicio, "%Y-%m") as month')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('fecha_inicio', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $llamadosTrend = array_map(fn ($key) => $llamadosPorMes[$key] ?? 0, $monthKeys);
        $actasTrend = array_map(fn ($key) => $actasPorMes[$key] ?? 0, $monthKeys);
        $llamadosPorEstado = LlamadoAtencion::selectRaw('estado_llamado as estado')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado_llamado')
            ->pluck('total', 'estado')
            ->toArray();

        $actasPorEstado = ActaCoordinacion::selectRaw('estado_acta as estado')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado_acta')
            ->pluck('total', 'estado')
            ->toArray();

        $procesosPorEstado = ProcesoDisciplinario::selectRaw('estado_proceso as estado')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado_proceso')
            ->pluck('total', 'estado')
            ->toArray();

        $llamadosTrend = array_map(fn ($key) => $llamadosPorMes[$key] ?? 0, $monthKeys);
        $actasTrend = array_map(fn ($key) => $actasPorMes[$key] ?? 0, $monthKeys);
        $procesosTrend = array_map(fn ($key) => $procesosPorMes[$key] ?? 0, $monthKeys);

        return view('dashboards.coordinador', compact(
            'totalLlamados',
            'llamadosPendientes',
            'totalActas',
            'actasExpedidas',
            'totalProcesos',
            'procesosActivos',
            'llamadosRecientes',
            'trendLabels',
            'llamadosTrend',
            'actasTrend',
            'procesosTrend',
            'llamadosPorEstado',
            'actasPorEstado',
            'procesosPorEstado',
        ));
    }
}
