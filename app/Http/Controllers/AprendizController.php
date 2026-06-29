<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LlamadoAtencion;
use App\Models\ActaCoordinacion;
use App\Models\ProcesoDisciplinario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AprendizController extends Controller
{
    /**
     * Muestra el panel de control del Aprendiz.
     */
    public function dashboard(): View
    {
        $user = Auth::user();
        
        // Asumiendo que el usuario tiene relación con aprendiz
        $aprendiz = $user->aprendiz;
        
        if (!$aprendiz) {
            abort(403, 'Acceso denegado: El usuario no es un aprendiz.');
        }

        // Obtener resúmenes
        $llamados = LlamadoAtencion::where('id_aprendiz', $aprendiz->id_aprendiz)
                        ->orderByDesc('fecha_llamado')
                        ->get();
                        
        $actas = ActaCoordinacion::where('id_aprendiz', $aprendiz->id_aprendiz)
                        ->orderByDesc('fecha_expedicion')
                        ->get();
                        
        $procesos = ProcesoDisciplinario::where('id_aprendiz', $aprendiz->id_aprendiz)
                        ->orderByDesc('fecha_inicio')
                        ->get();

        return view('dashboards.aprendiz', compact('aprendiz', 'llamados', 'actas', 'procesos'));
    }

    /**
     * Helper para obtener el aprendiz actual o abortar si no lo es.
     */
    private function getAprendiz()
    {
        $aprendiz = Auth::user()->aprendiz;
        if (!$aprendiz) abort(403, 'Acceso denegado: El usuario no es un aprendiz.');
        return $aprendiz;
    }

    // --- LLAMADOS DE ATENCIÓN ---
    public function llamados(): View
    {
        $aprendiz = $this->getAprendiz();
        $llamados = LlamadoAtencion::with('instructor.usuario')
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->orderByDesc('fecha_llamado')
            ->paginate(15);
        return view('aprendiz.llamados.index', compact('llamados'));
    }

    public function showLlamado(string $id): View
    {
        $aprendiz = $this->getAprendiz();
        $llamado = LlamadoAtencion::with(['instructor.usuario', 'faltas', 'coordinacion'])
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->findOrFail($id);
        return view('aprendiz.llamados.show', compact('llamado'));
    }

    // --- ACTAS DE COORDINACIÓN ---
    public function actas(): View
    {
        $aprendiz = $this->getAprendiz();
        $actas = ActaCoordinacion::with('falta')
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->orderByDesc('fecha_expedicion')
            ->paginate(15);
        return view('aprendiz.actas.index', compact('actas'));
    }

    public function showActa(string $id): View
    {
        $aprendiz = $this->getAprendiz();
        $acta = ActaCoordinacion::with(['falta', 'procesoDisciplinario'])
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->findOrFail($id);
        return view('aprendiz.actas.show', compact('acta'));
    }

    // --- PROCESOS DISCIPLINARIOS ---
    public function procesos(): View
    {
        $aprendiz = $this->getAprendiz();
        $procesos = ProcesoDisciplinario::where('id_aprendiz', $aprendiz->id_aprendiz)
            ->orderByDesc('fecha_inicio')
            ->paginate(15);
        return view('aprendiz.procesos.index', compact('procesos'));
    }

    public function showProceso(string $id): View
    {
        $aprendiz = $this->getAprendiz();
        $proceso = ProcesoDisciplinario::with(['historial', 'llamadoAtencion'])
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->findOrFail($id);
        return view('aprendiz.procesos.show', compact('proceso'));
    }
}
