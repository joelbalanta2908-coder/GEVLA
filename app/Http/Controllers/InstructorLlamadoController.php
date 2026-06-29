<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LlamadoAtencion;
use App\Models\Aprendiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InstructorLlamadoController extends Controller
{
    /**
     * Valida que el instructor tenga acceso y devuelve el modelo del instructor.
     */
    private function getInstructor()
    {
        $instructor = Auth::user()->instructor;
        if (!$instructor) {
            abort(403, 'Acceso denegado: El usuario no es un instructor.');
        }
        return $instructor;
    }

    /**
     * Lista los llamados de atención creados por el instructor actual.
     */
    public function index(): View
    {
        $instructor = $this->getInstructor();

        $llamados = LlamadoAtencion::with('aprendiz.usuario')
            ->where('id_instructor', $instructor->id_instructor)
            ->orderByDesc('fecha_llamado')
            ->paginate(15);

        return view('instructor.llamados.index', compact('llamados'));
    }

    /**
     * Muestra el formulario para crear un nuevo llamado de atención.
     */
    public function create(): View
    {
        $this->getInstructor(); // Validar acceso
        $aprendices = Aprendiz::with('usuario')->get();

        return view('instructor.llamados.create', compact('aprendices'));
    }

    /**
     * Almacena un nuevo llamado de atención en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $instructor = $this->getInstructor();

        $validated = $request->validate([
            'id_aprendiz'        => ['required', 'integer', 'exists:aprendiz,id_aprendiz'],
            'fecha_llamado'      => ['required', 'date', 'before_or_equal:today'],
            'asunto'             => ['required', 'string', 'max:255'],
            'descripcion_hechos' => ['required', 'string'],
            'pruebas_aportadas'  => ['nullable', 'string'],
            'tipo_llamado'       => ['required', Rule::in(['verbal', 'escrito'])],
            'categoria'          => ['required', Rule::in(['academico', 'disciplinario'])],
        ]);

        $validated['id_instructor'] = $instructor->id_instructor;
        $validated['estado_llamado'] = 'registrado'; // Estado inicial

        LlamadoAtencion::create($validated);

        return redirect()
            ->route('instructor.llamados.index')
            ->with('success', 'Llamado de atención reportado correctamente.');
    }

    /**
     * Muestra el detalle de un llamado de atención.
     */
    public function show(string $llamado): View
    {
        $instructor = $this->getInstructor();

        $llamado = LlamadoAtencion::with([
            'aprendiz.usuario',
            'coordinacion',
            'faltas'
        ])
        ->where('id_instructor', $instructor->id_instructor)
        ->findOrFail($llamado);

        return view('instructor.llamados.show', compact('llamado'));
    }

    /**
     * Muestra el formulario para editar un llamado de atención (solo si está registrado).
     */
    public function edit(string $llamado): View
    {
        $instructor = $this->getInstructor();

        $llamadoModel = LlamadoAtencion::where('id_instructor', $instructor->id_instructor)
            ->findOrFail($llamado);

        if ($llamadoModel->estado_llamado !== 'registrado') {
            return redirect()->route('instructor.llamados.show', $llamadoModel->id_llamado)
                ->withErrors(['error' => 'No puedes editar un llamado que ya está en proceso de revisión o cerrado.']);
        }

        $aprendices = Aprendiz::with('usuario')->get();

        return view('instructor.llamados.edit', compact('llamadoModel', 'aprendices'));
    }

    /**
     * Actualiza un llamado de atención.
     */
    public function update(Request $request, string $llamado): RedirectResponse
    {
        $instructor = $this->getInstructor();

        $llamadoModel = LlamadoAtencion::where('id_instructor', $instructor->id_instructor)
            ->findOrFail($llamado);

        if ($llamadoModel->estado_llamado !== 'registrado') {
            return redirect()->route('instructor.llamados.show', $llamadoModel->id_llamado)
                ->withErrors(['error' => 'No puedes modificar un llamado que ya no está en estado Registrado.']);
        }

        $validated = $request->validate([
            'id_aprendiz'        => ['required', 'integer', 'exists:aprendiz,id_aprendiz'],
            'fecha_llamado'      => ['required', 'date', 'before_or_equal:today'],
            'asunto'             => ['required', 'string', 'max:255'],
            'descripcion_hechos' => ['required', 'string'],
            'pruebas_aportadas'  => ['nullable', 'string'],
            'tipo_llamado'       => ['required', Rule::in(['verbal', 'escrito'])],
            'categoria'          => ['required', Rule::in(['academico', 'disciplinario'])],
        ]);

        $llamadoModel->update($validated);

        return redirect()
            ->route('instructor.llamados.show', $llamadoModel->id_llamado)
            ->with('success', 'Llamado de atención actualizado correctamente.');
    }

    /**
     * Elimina un llamado de atención (solo si está registrado).
     */
    public function destroy(string $llamado): RedirectResponse
    {
        $instructor = $this->getInstructor();

        $llamadoModel = LlamadoAtencion::where('id_instructor', $instructor->id_instructor)
            ->findOrFail($llamado);

        if ($llamadoModel->estado_llamado !== 'registrado') {
            return redirect()->route('instructor.llamados.index')
                ->withErrors(['error' => 'No puedes eliminar un llamado que ya está siendo procesado por coordinación.']);
        }

        // Eliminar faltas asociadas (opcional, dependiendo de si el instructor puede agregar faltas)
        $llamadoModel->faltas()->delete();
        $llamadoModel->delete();

        return redirect()
            ->route('instructor.llamados.index')
            ->with('success', 'Llamado de atención eliminado correctamente.');
    }
}
