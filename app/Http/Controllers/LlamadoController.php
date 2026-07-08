<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Aprendiz;
use App\Models\Ficha;
use App\Models\Instructor;
use App\Models\LlamadoAtencion;
use App\Models\NotificacionUsuario;
use App\Support\Busqueda;
use App\Support\CorreoLlamado;
use App\Support\PruebasLlamado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LlamadoController extends Controller
{
    /**
     * Lista los llamados de atención con filtros opcionales.
     */
    public function index(Request $request): View
    {
        $query = LlamadoAtencion::with(['aprendiz.usuario', 'instructor.usuario']);

        // Búsqueda con inferencia: cada palabra debe coincidir parcialmente con
        // el asunto o los datos del aprendiz, sin exigir el texto exacto.
        if ($buscar = $request->input('buscar')) {
            foreach (Busqueda::tokens($buscar) as $token) {
                $query->where(function ($q) use ($token) {
                    $q->where('asunto', 'like', "%{$token}%")
                      ->orWhereHas('aprendiz.usuario', function ($sub) use ($token) {
                          $sub->where('nombres', 'like', "%{$token}%")
                              ->orWhere('apellidos', 'like', "%{$token}%")
                              ->orWhere('numero_documento', 'like', "%{$token}%");
                      });
                });
            }
        }

        if ($categoria = $request->input('categoria')) {
            $query->where('categoria', $categoria);
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado_llamado', $estado);
        }

        $llamados = $query->orderByDesc('fecha_llamado')->paginate(15)->withQueryString();

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

        $llamadosTrend = array_map(fn ($key) => $llamadosPorMes[$key] ?? 0, $monthKeys);

        $statusLabels = ['registrado', 'en_revision', 'notificado', 'cerrado', 'cancelado'];
        $llamadosPorEstado = LlamadoAtencion::selectRaw('estado_llamado as estado')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado_llamado')
            ->pluck('total', 'estado')
            ->toArray();
        $llamadosEstadoData = array_map(fn ($key) => $llamadosPorEstado[$key] ?? 0, $statusLabels);

        // Fichas para el filtro del reporte exportable.
        $fichasExport = Ficha::orderBy('numero_ficha')->get();

        return view('coordinacion.llamados.index', compact('llamados', 'trendLabels', 'llamadosTrend', 'statusLabels', 'llamadosEstadoData', 'fichasExport'));
    }

    /**
     * Muestra el formulario para crear un llamado de atención.
     */
    public function create(): View
    {
        $aprendices = Aprendiz::with('usuario')->get();
        $instructores = Instructor::with('usuario')->get();

        return view('coordinacion.llamados.create', compact('aprendices', 'instructores'));
    }

    /**
     * Guarda un llamado de atención en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_aprendiz'        => ['required', 'integer', 'exists:aprendiz,id_aprendiz'],
            'id_instructor'      => ['required', 'integer', 'exists:instructor,id_instructor'],
            'fecha_llamado'      => ['required', 'date'],
            'tipo_llamado'       => ['required', Rule::in(['llamado_escrito', 'acondicionamiento', 'cancelacion_matricula'])],
            'categoria'          => ['required', Rule::in(['academico', 'disciplinario'])],
            'asunto'             => ['required', 'string', 'max:200'],
            'descripcion_hechos' => ['required', 'string'],
            'pruebas_aportadas'  => ['nullable', 'string'],
            'pruebas_fotos'      => ['nullable', 'array', 'max:8'],
            'pruebas_fotos.*'    => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'estado_llamado'     => ['required', Rule::in(['registrado', 'en_revision', 'notificado', 'cerrado', 'cancelado'])],
            'observaciones'      => ['nullable', 'string'],
        ], [
            'pruebas_fotos.*.image' => 'Cada prueba debe ser una imagen (JPG, PNG o WEBP).',
            'pruebas_fotos.*.max'   => 'Cada foto de prueba no puede superar los 4 MB.',
            'pruebas_fotos.max'     => 'Puedes adjuntar como máximo 8 fotos de prueba.',
        ]);

        // Texto + fotos de evidencia se combinan en el mismo campo (JSON).
        $validated['pruebas_aportadas'] = PruebasLlamado::desdeRequest($request);
        unset($validated['pruebas_fotos']);

        // No se permite un llamado por exactamente la misma razón al mismo
        // aprendiz si aún no han pasado al menos 14 días desde el último con
        // esa razón (ignora mayúsculas y espacios dobles al comparar).
        if (LlamadoAtencion::existeLlamadoRecienteMismaRazon((int) $validated['id_aprendiz'], $validated['asunto'])) {
            return back()->withInput()->withErrors([
                'asunto' => 'Este aprendiz ya tiene un llamado de atención por esta misma razón dentro de los últimos '
                    . LlamadoAtencion::DIAS_MINIMOS_MISMA_RAZON . ' días. Deben transcurrir al menos dos semanas para registrar otro llamado con el mismo motivo.',
            ]);
        }

        $validated['id_usuario_reporta'] = Auth::id() ?? 1; // Fallback por si acaso en entorno dev

        $llamado = LlamadoAtencion::create($validated);

        // Correo personalizado al aprendiz con el detalle del llamado. Solo se
        // envía para llamados nuevos (aquí, en la creación); nunca de forma
        // retroactiva. Si el envío falla, no rompe la creación del llamado.
        CorreoLlamado::enviar($llamado);

        // Notificaciones: al aprendiz y al instructor asignado.
        $llamado->load(['aprendiz.usuario', 'instructor.usuario']);
        NotificacionUsuario::emitir(
            $llamado->aprendiz?->usuario?->id_usuario,
            'Nuevo llamado de atención',
            "Se te registró un llamado de atención: {$llamado->asunto}",
            route('aprendiz.llamados.show', $llamado->id_llamado, false)
        );
        NotificacionUsuario::emitir(
            $llamado->instructor?->usuario?->id_usuario,
            'Llamado registrado a tu nombre',
            "Coordinación registró el llamado #{$llamado->id_llamado} ({$llamado->asunto}) con tu firma como instructor",
            route('instructor.llamados.show', $llamado->id_llamado, false)
        );

        return redirect()
            ->route('coordinacion.llamados.index')
            ->with('success', 'Llamado de atención creado correctamente.');
    }

    /**
     * Muestra el detalle de un llamado de atención.
     */
    public function show(string $llamado): View
    {
        $llamado = LlamadoAtencion::with([
            'aprendiz.usuario',
            'instructor.usuario',
            'coordinacion',
            'faltas',
        ])->findOrFail($llamado);

        return view('coordinacion.llamados.show', compact('llamado'));
    }

    /**
     * Muestra el formulario de edición de un llamado.
     */
    public function edit(string $llamado): View
    {
        $llamado = LlamadoAtencion::findOrFail($llamado);
        $aprendices = Aprendiz::with('usuario')->get();
        $instructores = Instructor::with('usuario')->get();

        return view('coordinacion.llamados.edit', compact('llamado', 'aprendices', 'instructores'));
    }

    /**
     * Actualiza un llamado de atención completo.
     */
    public function update(Request $request, string $llamado): RedirectResponse
    {
        $llamadoModel = LlamadoAtencion::findOrFail($llamado);

        $validated = $request->validate([
            'id_aprendiz'        => ['required', 'integer', 'exists:aprendiz,id_aprendiz'],
            'id_instructor'      => ['required', 'integer', 'exists:instructor,id_instructor'],
            'fecha_llamado'      => ['required', 'date'],
            'tipo_llamado'       => ['required', Rule::in(['llamado_escrito', 'acondicionamiento', 'cancelacion_matricula'])],
            'categoria'          => ['required', Rule::in(['academico', 'disciplinario'])],
            'asunto'             => ['required', 'string', 'max:200'],
            'descripcion_hechos' => ['required', 'string'],
            'pruebas_aportadas'  => ['nullable', 'string'],
            'pruebas_fotos'      => ['nullable', 'array', 'max:8'],
            'pruebas_fotos.*'    => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'estado_llamado'     => ['required', Rule::in(['registrado', 'en_revision', 'notificado', 'cerrado', 'cancelado'])],
            'observaciones'      => ['nullable', 'string'],
        ], [
            'pruebas_fotos.*.image' => 'Cada prueba debe ser una imagen (JPG, PNG o WEBP).',
            'pruebas_fotos.*.max'   => 'Cada foto de prueba no puede superar los 4 MB.',
            'pruebas_fotos.max'     => 'Puedes adjuntar como máximo 8 fotos de prueba.',
        ]);

        // Conserva las fotos previas (menos las que se quiten), agrega nuevas.
        $validated['pruebas_aportadas'] = PruebasLlamado::desdeRequest($request, $llamadoModel->pruebas_aportadas);
        unset($validated['pruebas_fotos']);

        $llamadoModel->update($validated);

        return redirect()
            ->route('coordinacion.llamados.show', $llamadoModel->id_llamado)
            ->with('success', 'Llamado de atención actualizado correctamente.');
    }

    /**
     * Actualiza solo el estado de un llamado de atención (desde la vista show).
     */
    public function actualizarEstado(Request $request, string $llamado): RedirectResponse
    {
        $request->validate([
            'estado_llamado' => ['required', Rule::in([
                'registrado', 'en_revision', 'notificado', 'cerrado', 'cancelado',
            ])],
        ]);

        $llamado = LlamadoAtencion::with(['aprendiz.usuario', 'instructor.usuario'])->findOrFail($llamado);
        $estadoAnterior = $llamado->estado_llamado;
        $llamado->update(['estado_llamado' => $request->input('estado_llamado')]);

        // Notificaciones del cambio de estado: al instructor que lo reportó y
        // al aprendiz implicado (solo si realmente cambió).
        if ($estadoAnterior !== $llamado->estado_llamado) {
            $etiquetaEstado = str_replace('_', ' ', ucfirst((string) $llamado->estado_llamado));

            NotificacionUsuario::emitir(
                $llamado->instructor?->usuario?->id_usuario,
                'Tu llamado cambió de estado',
                "El llamado #{$llamado->id_llamado} ({$llamado->asunto}) pasó a estado {$etiquetaEstado}",
                route('instructor.llamados.show', $llamado->id_llamado, false)
            );
            NotificacionUsuario::emitir(
                $llamado->aprendiz?->usuario?->id_usuario,
                'Tu llamado de atención fue actualizado',
                "El llamado ({$llamado->asunto}) pasó a estado {$etiquetaEstado}",
                route('aprendiz.llamados.show', $llamado->id_llamado, false)
            );
        }

        return redirect()
            ->route('coordinacion.llamados.show', $llamado->id_llamado)
            ->with('success', 'Estado del llamado actualizado correctamente.');
    }

    /**
     * Elimina un llamado de atención.
     */
    public function destroy(string $llamado): RedirectResponse
    {
        $llamadoModel = LlamadoAtencion::findOrFail($llamado);

        // Un llamado que ya derivó en un proceso disciplinario no se elimina:
        // es la raíz de ese proceso y borrarlo dejaría el expediente incompleto.
        if ($llamadoModel->procesosDisciplinarios()->exists()) {
            return redirect()
                ->route('coordinacion.llamados.index')
                ->withErrors(['login' => 'No se puede eliminar el llamado porque ya tiene un proceso disciplinario asociado.']);
        }

        // Se eliminan primero los registros dependientes (faltas y las
        // notificaciones oficiales generadas al crear el llamado) para no
        // violar las claves foráneas, y se limpian las fotos del storage.
        $fotos = $llamadoModel->pruebas_fotos;

        DB::transaction(function () use ($llamadoModel) {
            $llamadoModel->faltas()->delete();
            $llamadoModel->notificaciones()->delete();
            $llamadoModel->delete();
        });

        PruebasLlamado::eliminarArchivos($fotos);

        return redirect()
            ->route('coordinacion.llamados.index')
            ->with('success', 'Llamado de atención eliminado correctamente.');
    }
}
