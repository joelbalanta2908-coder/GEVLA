<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ProgramaFormacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestión de Programas de Formación (Coordinación).
 *
 * CRUD del catálogo `programa_formacion` (código, nombre, nivel y duración),
 * usado como base para crear fichas. No permite eliminar un programa que tenga
 * fichas asociadas, para conservar la integridad referencial.
 */
class ProgramaController extends Controller
{
    /**
     * Listado con buscador, número de fichas por programa y paginación.
     */
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->input('buscar', ''));

        $programas = ProgramaFormacion::query()
            ->withCount('fichas')
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where('nombre_programa', 'like', "%{$buscar}%")
                    ->orWhere('codigo_programa', 'like', "%{$buscar}%");
            })
            ->orderBy('nombre_programa')
            ->paginate(12)
            ->withQueryString();

        $niveles = ProgramaFormacion::niveles();

        return view('coordinacion.programas.index', compact('programas', 'niveles', 'buscar'));
    }

    public function create(): View
    {
        $niveles = ProgramaFormacion::niveles();

        return view('coordinacion.programas.create', compact('niveles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validar($request);

        ProgramaFormacion::create($validated);

        return redirect()
            ->route('coordinacion.programas.index')
            ->with('success', 'Programa de formación creado correctamente.');
    }

    public function edit(ProgramaFormacion $programa): View
    {
        $niveles = ProgramaFormacion::niveles();

        return view('coordinacion.programas.edit', compact('programa', 'niveles'));
    }

    public function update(Request $request, ProgramaFormacion $programa): RedirectResponse
    {
        $validated = $this->validar($request, $programa);

        $programa->update($validated);

        return redirect()
            ->route('coordinacion.programas.index')
            ->with('success', 'Programa de formación actualizado correctamente.');
    }

    public function destroy(ProgramaFormacion $programa): RedirectResponse
    {
        if ($programa->fichas()->exists()) {
            return redirect()
                ->route('coordinacion.programas.index')
                ->withErrors(['error' => 'No se puede eliminar un programa con fichas asociadas.']);
        }

        $programa->delete();

        return redirect()
            ->route('coordinacion.programas.index')
            ->with('success', 'Programa de formación eliminado correctamente.');
    }

    /**
     * Reglas de validación. El código debe ser único.
     *
     * @return array<string, mixed>
     */
    private function validar(Request $request, ?ProgramaFormacion $programa = null): array
    {
        $codigoUnico = Rule::unique('programa_formacion', 'codigo_programa');
        if ($programa) {
            $codigoUnico->ignore($programa->id_programa, 'id_programa');
        }

        return $request->validate([
            'codigo_programa' => ['required', 'string', 'max:20', $codigoUnico],
            'nombre_programa' => ['required', 'string', 'max:150'],
            'nivel'           => ['required', Rule::in(array_keys(ProgramaFormacion::niveles()))],
            'duracion_meses'  => ['required', 'integer', 'min:1', 'max:120'],
        ], [
            'codigo_programa.unique' => 'Ya existe un programa con ese código.',
        ]);
    }
}
