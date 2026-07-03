<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Aprendiz;
use App\Models\Ficha;
use App\Models\LlamadoAtencion;
use App\Models\HistorialProcesoDisciplinario;
use App\Models\NotificacionUsuario;
use App\Models\ProcesoDisciplinario;
use App\Support\Busqueda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProcesoController extends Controller
{
    /**
     * Lista los procesos disciplinarios con filtros opcionales.
     */
    public function index(Request $request): View
    {
        $query = ProcesoDisciplinario::with(['aprendiz.usuario']);

        // Búsqueda con inferencia: cada palabra debe coincidir parcialmente con
        // los datos del aprendiz, la etapa o las observaciones del proceso.
        if ($buscar = trim((string) $request->input('buscar', ''))) {
            foreach (Busqueda::tokens($buscar) as $token) {
                $query->where(function ($q) use ($token) {
                    $q->where('observaciones', 'like', "%{$token}%")
                        ->orWhere('etapa_actual', 'like', "%{$token}%")
                        ->orWhereHas('aprendiz.usuario', function ($sub) use ($token) {
                            $sub->where('nombres', 'like', "%{$token}%")
                                ->orWhere('apellidos', 'like', "%{$token}%")
                                ->orWhere('numero_documento', 'like', "%{$token}%");
                        });
                });
            }
        }

        if ($estadoProceso = $request->input('estado_proceso')) {
            $query->where('estado_proceso', $estadoProceso);
        }

        if ($etapaActual = $request->input('etapa_actual')) {
            $query->where('etapa_actual', $etapaActual);
        }

        $procesos = $query->orderByDesc('fecha_inicio')->paginate(15)->withQueryString();

        $months = collect(range(5, 0, -1))
            ->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo))
            ->map(fn (Carbon $date) => [
                'key' => $date->format('Y-m'),
                'label' => $date->locale('es')->translatedFormat('M Y'),
            ]);

        $monthKeys = $months->pluck('key')->toArray();
        $trendLabels = $months->pluck('label')->toArray();

        $procesosPorMes = ProcesoDisciplinario::selectRaw('DATE_FORMAT(fecha_inicio, "%Y-%m") as month')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('fecha_inicio', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $procesosTrend = array_map(fn ($key) => $procesosPorMes[$key] ?? 0, $monthKeys);

        $statusLabels = ['activo', 'suspendido', 'finalizado', 'apelacion'];
        $procesosPorEstado = ProcesoDisciplinario::selectRaw('estado_proceso as estado')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado_proceso')
            ->pluck('total', 'estado')
            ->toArray();
        $procesosEstadoData = array_map(fn ($key) => $procesosPorEstado[$key] ?? 0, $statusLabels);

        // Fichas para el filtro del reporte exportable.
        $fichasExport = Ficha::orderBy('numero_ficha')->get();

        return view('coordinacion.procesos.index', compact('procesos', 'trendLabels', 'procesosTrend', 'statusLabels', 'procesosEstadoData', 'fichasExport'));
    }

    /**
     * Muestra el formulario para crear un proceso disciplinario.
     */
    public function create(): View
    {
        $aprendices = Aprendiz::with('usuario')->get();
        // Mostrar llamados que no tienen proceso o para enlazarlos
        $llamados = LlamadoAtencion::with('aprendiz.usuario')->get();

        return view('coordinacion.procesos.create', compact('aprendices', 'llamados'));
    }

    /**
     * Almacena un nuevo proceso disciplinario en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_aprendiz'    => ['required', 'integer', 'exists:aprendiz,id_aprendiz'],
            'id_llamado'     => ['nullable', 'integer', 'exists:llamado_atencion,id_llamado'],
            'fecha_inicio'   => ['required', 'date'],
            'etapa_actual'   => ['required', Rule::in(['llamado_escrito', 'acondicionamiento', 'cancelacion_matricula'])],
            'estado_proceso' => ['required', Rule::in(['activo', 'suspendido', 'finalizado', 'apelacion'])],
            'observaciones'  => ['nullable', 'string'],
        ]);

        $procesoNuevo = ProcesoDisciplinario::create($validated);

        // Notificaciones: al aprendiz y al instructor del llamado de origen.
        $procesoNuevo->load(['aprendiz.usuario', 'llamadoAtencion.instructor.usuario']);
        NotificacionUsuario::emitir(
            $procesoNuevo->aprendiz?->usuario?->id_usuario,
            'Se inició un proceso disciplinario',
            'Se abrió un proceso disciplinario en tu contra. Consulta el detalle en tu portal.',
            route('aprendiz.procesos.show', $procesoNuevo->id_proceso, false)
        );
        NotificacionUsuario::emitir(
            $procesoNuevo->llamadoAtencion?->instructor?->usuario?->id_usuario,
            'Proceso disciplinario iniciado',
            "Tu llamado #{$procesoNuevo->id_llamado} derivó en un proceso disciplinario",
            route('instructor.procesos.index', [], false)
        );

        return redirect()
            ->route('coordinacion.procesos.index')
            ->with('success', 'Proceso disciplinario creado correctamente.');
    }

    /**
     * Muestra el detalle y el historial de un proceso disciplinario.
     */
    public function show(string $proceso): View
    {
        $proceso = ProcesoDisciplinario::with([
            'aprendiz.usuario',
            'llamadoAtencion',
            'historial.usuarioRegistra',
        ])->findOrFail($proceso);

        return view('coordinacion.procesos.show', compact('proceso'));
    }

    /**
     * Muestra el formulario para editar un proceso disciplinario.
     */
    public function edit(string $proceso): View
    {
        $proceso = ProcesoDisciplinario::findOrFail($proceso);
        $aprendices = Aprendiz::with('usuario')->get();
        $llamados = LlamadoAtencion::with('aprendiz.usuario')->get();

        return view('coordinacion.procesos.edit', compact('proceso', 'aprendices', 'llamados'));
    }

    /**
     * Actualiza un proceso disciplinario en la base de datos.
     */
    public function update(Request $request, string $proceso): RedirectResponse
    {
        $procesoModel = ProcesoDisciplinario::findOrFail($proceso);

        $validated = $request->validate([
            'id_aprendiz'    => ['required', 'integer', 'exists:aprendiz,id_aprendiz'],
            'id_llamado'     => ['nullable', 'integer', 'exists:llamado_atencion,id_llamado'],
            'fecha_inicio'   => ['required', 'date'],
            'etapa_actual'   => ['required', Rule::in(['llamado_escrito', 'acondicionamiento', 'cancelacion_matricula'])],
            'estado_proceso' => ['required', Rule::in(['activo', 'suspendido', 'finalizado', 'apelacion'])],
            'observaciones'  => ['nullable', 'string'],
        ]);

        $procesoModel->update($validated);

        return redirect()
            ->route('coordinacion.procesos.show', $procesoModel->id_proceso)
            ->with('success', 'Proceso disciplinario actualizado correctamente.');
    }

    /**
     * Almacena un nuevo registro en el historial del proceso disciplinario.
     */
    public function guardarHistorial(Request $request, string $proceso): RedirectResponse
    {
        $proceso = ProcesoDisciplinario::findOrFail($proceso);

        $validated = $request->validate([
            'etapa'       => ['required', Rule::in([
                'llamado_escrito', 'acondicionamiento', 'cancelacion_matricula',
            ])],
            'descripcion' => ['required', 'string'],
            'resultado'   => ['nullable', 'string', 'max:255'],
        ]);

        HistorialProcesoDisciplinario::create([
            'id_proceso'        => $proceso->id_proceso,
            'etapa'             => $validated['etapa'],
            'fecha_registro'    => now(),
            'id_usuario_registra' => Auth::id() ?? 1,
            'descripcion'       => $validated['descripcion'],
            'resultado'         => $validated['resultado'] ?? null,
        ]);

        // Actualizar la etapa actual del proceso si corresponde.
        $proceso->update(['etapa_actual' => $validated['etapa']]);

        return redirect()
            ->route('coordinacion.procesos.show', $proceso->id_proceso)
            ->with('success', 'Avance registrado correctamente.');
    }

    /**
     * Elimina un proceso disciplinario de la base de datos.
     */
    public function destroy(string $proceso): RedirectResponse
    {
        $procesoModel = ProcesoDisciplinario::findOrFail($proceso);
        
        // El historial del proceso se eliminará en cascada o dará error según FK. 
        // Asumiendo que es preferible no borrar si tiene historial:
        if ($procesoModel->historial()->exists() || $procesoModel->actas()->exists()) {
            return redirect()
                ->route('coordinacion.procesos.index')
                ->withErrors(['error' => 'No se puede eliminar el proceso disciplinario porque tiene un historial o actas asociadas.']);
        }

        $procesoModel->delete();

        return redirect()
            ->route('coordinacion.procesos.index')
            ->with('success', 'Proceso disciplinario eliminado correctamente.');
    }
}
