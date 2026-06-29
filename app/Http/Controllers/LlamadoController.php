<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Aprendiz;
use App\Models\Instructor;
use App\Models\LlamadoAtencion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if ($buscar = $request->input('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('asunto', 'like', "%{$buscar}%")
                  ->orWhereHas('aprendiz.usuario', function ($sub) use ($buscar) {
                      $sub->where('nombres', 'like', "%{$buscar}%")
                          ->orWhere('apellidos', 'like', "%{$buscar}%");
                  });
            });
        }

        if ($categoria = $request->input('categoria')) {
            $query->where('categoria', $categoria);
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado_llamado', $estado);
        }

        $llamados = $query->orderByDesc('fecha_llamado')->paginate(15)->withQueryString();

        return view('coordinacion.llamados.index', compact('llamados'));
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
            'estado_llamado'     => ['required', Rule::in(['registrado', 'en_revision', 'notificado', 'cerrado', 'cancelado'])],
            'observaciones'      => ['nullable', 'string'],
        ]);

        $validated['id_usuario_reporta'] = Auth::id() ?? 1; // Fallback por si acaso en entorno dev
        
        LlamadoAtencion::create($validated);

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
            'estado_llamado'     => ['required', Rule::in(['registrado', 'en_revision', 'notificado', 'cerrado', 'cancelado'])],
            'observaciones'      => ['nullable', 'string'],
        ]);

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

        $llamado = LlamadoAtencion::findOrFail($llamado);
        $llamado->update(['estado_llamado' => $request->input('estado_llamado')]);

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
        
        // Comprobar relaciones (faltas o procesos) para no romper FK
        if ($llamadoModel->faltas()->exists() || $llamadoModel->procesosDisciplinarios()->exists()) {
            return redirect()
                ->route('coordinacion.llamados.index')
                ->withErrors(['login' => 'No se puede eliminar el llamado porque tiene faltas o procesos asociados.']);
        }
        
        $llamadoModel->delete();

        return redirect()
            ->route('coordinacion.llamados.index')
            ->with('success', 'Llamado de atención eliminado correctamente.');
    }
}
