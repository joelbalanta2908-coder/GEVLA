<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActaCoordinacion;
use App\Models\Aprendiz;
use App\Models\Falta;
use App\Models\LlamadoAtencion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $actas = $query->orderByDesc('fecha_expedicion')->paginate(15)->withQueryString();

        return view('coordinacion.actas.index', compact('actas'));
    }

    /**
     * Muestra el formulario de creación de un acta.
     */
    public function create(Request $request): View
    {
        $aprendices = Aprendiz::with('usuario')->get();
        $faltas = Falta::all();

        // Si viene desde el detalle de un llamado, precargamos el llamado seleccionado.
        $llamadoSeleccionado = null;
        if ($idLlamado = $request->input('llamado')) {
            $llamadoSeleccionado = LlamadoAtencion::find($idLlamado);
        }

        return view('coordinacion.actas.create', compact(
            'aprendices',
            'faltas',
            'llamadoSeleccionado',
        ));
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
            'numero_acta'          => ['required', 'string', 'max:30', 'unique:acta_coordinacion,numero_acta'],
            'fecha_expedicion'     => ['required', 'date'],
            'sancion_descripcion'  => ['required', 'string'],
            'meses_inhabilitacion' => ['nullable', 'integer', 'min:0'],
        ]);

        ActaCoordinacion::create($validated);

        return redirect()
            ->route('coordinacion.actas.index')
            ->with('success', 'Acta de coordinación expedida correctamente.');
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
