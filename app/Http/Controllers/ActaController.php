<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActaCoordinacion;
use App\Models\Aprendiz;
use App\Models\Falta;
use App\Models\Ficha;
use App\Models\LlamadoAtencion;
use App\Models\Matricula;
use App\Models\NotificacionUsuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActaController extends Controller
{
    /**
     * Lista las actas de coordinación con filtros opcionales.
     */
    public function index(Request $request): View
    {
        $query = ActaCoordinacion::with(['aprendiz.usuario']);

        if ($tipoActa = $request->input('tipo_acta')) {
            $query->where('tipo_acta', $tipoActa);
        }

        if ($estadoActa = $request->input('estado_acta')) {
            $query->where('estado_acta', $estadoActa);
        }

        $actas = $query->orderByDesc('fecha_expedicion')->paginate(10)->withQueryString();

        $months = collect(range(5, 0, -1))
            ->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo))
            ->map(fn (Carbon $date) => [
                'key' => $date->format('Y-m'),
                'label' => $date->locale('es')->translatedFormat('M Y'),
            ]);

        $monthKeys = $months->pluck('key')->toArray();
        $trendLabels = $months->pluck('label')->toArray();

        $actasPorMes = ActaCoordinacion::selectRaw('DATE_FORMAT(fecha_expedicion, "%Y-%m") as month')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('fecha_expedicion', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $actasTrend = array_map(fn ($key) => $actasPorMes[$key] ?? 0, $monthKeys);

        $statusLabels = ['expedido', 'notificado', 'firme'];
        $actasPorEstado = ActaCoordinacion::selectRaw('estado_acta as estado')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado_acta')
            ->pluck('total', 'estado')
            ->toArray();
        $actasEstadoData = array_map(fn ($key) => $actasPorEstado[$key] ?? 0, $statusLabels);

        // Fichas para el filtro del reporte exportable.
        $fichasExport = Ficha::orderBy('numero_ficha')->get();

        return view('coordinacion.actas.index', compact('actas', 'trendLabels', 'actasTrend', 'statusLabels', 'actasEstadoData', 'fichasExport'));
    }

    /**
     * Muestra el formulario de creación de un acta.
     */
    public function create(Request $request): View
    {
        $faltas = Falta::all();
        $fichas = Ficha::with('programa')->orderBy('numero_ficha')->get();

        // Si viene desde el detalle de un llamado, precargamos el llamado seleccionado.
        $llamadoSeleccionado = null;
        if ($idLlamado = $request->input('llamado')) {
            $llamadoSeleccionado = LlamadoAtencion::find($idLlamado);
        }

        // Ficha preseleccionada: la enviada en el intento anterior (old) o la de
        // la matrícula más reciente del aprendiz del llamado de origen. Con ella
        // se precarga la lista de aprendices para que el formulario no arranque
        // vacío al volver de un error de validación o al venir desde un llamado.
        $fichaSeleccionada = (int) old('id_ficha', 0);
        if (! $fichaSeleccionada && $llamadoSeleccionado) {
            $matricula = Matricula::where('id_aprendiz', $llamadoSeleccionado->id_aprendiz)
                ->orderByRaw("estado_matricula = 'activa' DESC")
                ->orderByDesc('fecha_matricula')
                ->first();
            $fichaSeleccionada = (int) ($matricula->id_ficha ?? 0);
        }

        $aprendicesFicha = $fichaSeleccionada
            ? $this->aprendicesDeFicha($fichaSeleccionada)
            : collect();

        return view('coordinacion.actas.create', compact(
            'faltas',
            'fichas',
            'llamadoSeleccionado',
            'fichaSeleccionada',
            'aprendicesFicha',
        ));
    }

    /**
     * Devuelve en JSON los aprendices matriculados en una ficha (para el
     * selector dependiente del formulario de actas).
     */
    public function aprendicesPorFicha(Ficha $ficha): JsonResponse
    {
        return response()->json($this->aprendicesDeFicha((int) $ficha->id_ficha));
    }

    /**
     * Aprendices matriculados en la ficha, con los datos mínimos del selector.
     */
    private function aprendicesDeFicha(int $idFicha): Collection
    {
        return Aprendiz::with('usuario')
            ->whereHas('matriculas', fn ($q) => $q->where('id_ficha', $idFicha))
            ->get()
            ->map(fn (Aprendiz $aprendiz) => [
                'id'        => $aprendiz->id_aprendiz,
                'nombre'    => trim(($aprendiz->usuario->nombres ?? '') . ' ' . ($aprendiz->usuario->apellidos ?? '')),
                'documento' => (string) ($aprendiz->usuario->numero_documento ?? ''),
            ])
            ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * Almacena una nueva acta de coordinación.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_aprendiz'          => ['required', 'integer', 'exists:aprendiz,id_aprendiz'],
            'id_falta'             => ['required', 'integer', 'exists:falta,id_falta'],
            'tipo_acta'            => ['required', Rule::in([
                'acondicionamiento_academico',
                'cancelacion_academica',
                'acondicionamiento_disciplinario',
                'cancelacion_disciplinaria',
            ])],
            'fecha_expedicion'     => ['required', 'date'],
            'sancion_descripcion'  => ['required', 'string'],
            'meses_inhabilitacion' => ['nullable', 'integer', 'min:0'],
        ]);

        // El número de acta se genera automáticamente: consecutivo por año
        // (AC-2026-001, AC-2026-002, ...), sin depender de digitación manual.
        $validated['numero_acta'] = $this->generarNumeroActa();

        $acta = ActaCoordinacion::create($validated);

        // Notificación al aprendiz sobre el acta expedida.
        $aprendizActa = Aprendiz::with('usuario')->find($acta->id_aprendiz);
        NotificacionUsuario::emitir(
            $aprendizActa?->usuario?->id_usuario,
            'Se expidió un acta de coordinación',
            "Acta {$acta->numero_acta}: " . str_replace('_', ' ', ucfirst((string) $acta->tipo_acta)),
            route('aprendiz.actas.show', $acta->id_acta, false)
        );

        return redirect()
            ->route('coordinacion.actas.index')
            ->with('success', 'Acta de coordinación expedida correctamente con el número ' . $acta->numero_acta . '.');
    }

    /**
     * Genera el siguiente número de acta del año en curso: AC-AAAA-NNN.
     * Busca el mayor consecutivo existente para el año y le suma 1.
     */
    private function generarNumeroActa(): string
    {
        $anio = now()->format('Y');
        $prefijo = "AC-{$anio}-";

        $maximo = ActaCoordinacion::where('numero_acta', 'like', $prefijo . '%')
            ->get()
            ->map(fn (ActaCoordinacion $a) => (int) substr((string) $a->numero_acta, strlen($prefijo)))
            ->max();

        return $prefijo . str_pad((string) (((int) $maximo) + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Muestra el detalle de un acta de coordinación.
     */
    public function show(string $acta): View
    {
        $acta = ActaCoordinacion::with([
            'aprendiz.usuario',
            'falta',
            'procesoDisciplinario',
        ])->findOrFail($acta);

        return view('coordinacion.actas.show', compact('acta'));
    }

    /**
     * Muestra el formulario de edición de un acta de coordinación.
     */
    public function edit(string $acta): View
    {
        $acta = ActaCoordinacion::findOrFail($acta);
        $aprendices = Aprendiz::with('usuario')->get();
        $faltas = Falta::all();

        return view('coordinacion.actas.edit', compact('acta', 'aprendices', 'faltas'));
    }

    /**
     * Actualiza un acta de coordinación existente.
     */
    public function update(Request $request, string $acta): RedirectResponse
    {
        $actaModel = ActaCoordinacion::findOrFail($acta);

        $validated = $request->validate([
            'id_aprendiz'                 => ['required', 'integer', 'exists:aprendiz,id_aprendiz'],
            'id_falta'                    => ['required', 'integer', 'exists:falta,id_falta'],
            'tipo_acta'                   => ['required', Rule::in([
                'acondicionamiento_academico',
                'cancelacion_academica',
                'acondicionamiento_disciplinario',
                'cancelacion_disciplinaria',
            ])],
            'numero_acta'                 => ['required', 'string', 'max:30', Rule::unique('acta_coordinacion', 'numero_acta')->ignore($actaModel->id_acta, 'id_acta')],
            'fecha_expedicion'            => ['required', 'date'],
            'fecha_notificacion_personal' => ['nullable', 'date'],
            'fecha_firmeza'               => ['nullable', 'date'],
            'estado_acta'                 => ['required', Rule::in(['expedido', 'notificado', 'firme'])],
            'sancion_descripcion'         => ['required', 'string'],
            'meses_inhabilitacion'        => ['nullable', 'integer', 'min:0'],
        ]);

        $actaModel->update($validated);

        return redirect()
            ->route('coordinacion.actas.show', $actaModel->id_acta)
            ->with('success', 'Acta de coordinación actualizada correctamente.');
    }

    /**
     * Elimina un acta de coordinación.
     */
    public function destroy(string $acta): RedirectResponse
    {
        $actaModel = ActaCoordinacion::findOrFail($acta);
        
        if ($actaModel->procesoDisciplinario()->exists()) {
            return redirect()
                ->route('coordinacion.actas.index')
                ->withErrors(['error' => 'No se puede eliminar el acta porque pertenece a un proceso disciplinario en curso.']);
        }
        
        $actaModel->delete();

        return redirect()
            ->route('coordinacion.actas.index')
            ->with('success', 'Acta de coordinación eliminada correctamente.');
    }
}
