<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Aprendiz;
use App\Models\Ficha;
use App\Models\HistorialInstructorLider;
use App\Models\Instructor;
use App\Models\Matricula;
use App\Models\ProgramaFormacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestión de Fichas (Coordinador Misional).
 *
 * Reúne el CRUD de fichas, la asociación de aprendices e instructores y la
 * designación del instructor líder. Todas las operaciones que tocan varias
 * tablas se ejecutan dentro de una transacción y validan permisos en backend.
 */
class FichaController extends Controller
{
    /**
     * Aborta si el usuario autenticado no es Coordinador Misional. Se usa en las
     * acciones sensibles (designar líder) que el reglamento reserva a ese rol.
     */
    private function exigirCoordinadorMisional(): void
    {
        if (! optional(Auth::user())->esCoordinadorMisional()) {
            abort(403, 'Solo el Coordinador Misional puede realizar esta acción.');
        }
    }

    /**
     * Listado de fichas con filtros combinables y paginación (evita N+1 con
     * carga previa de relaciones y conteos agregados).
     */
    public function index(Request $request): View
    {
        $buscar    = trim((string) $request->input('buscar', ''));
        $estado    = $request->input('estado_ficha');
        $modalidad = $request->input('modalidad');
        $programa  = $request->input('id_programa');

        $fichas = Ficha::query()
            ->with(['programa', 'instructorLider.usuario'])
            ->withCount([
                'matriculas',
                'instructores',
            ])
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($sub) use ($buscar) {
                    $sub->where('numero_ficha', 'like', "%{$buscar}%")
                        ->orWhereHas('programa', fn ($p) => $p->where('nombre_programa', 'like', "%{$buscar}%")
                            ->orWhere('codigo_programa', 'like', "%{$buscar}%"));
                });
            })
            ->when($estado, fn ($q) => $q->where('estado_ficha', $estado))
            ->when($modalidad, fn ($q) => $q->where('modalidad', $modalidad))
            ->when($programa, fn ($q) => $q->where('id_programa', $programa))
            ->orderByDesc('fecha_inicio')
            ->paginate(12)
            ->withQueryString();

        $programas   = ProgramaFormacion::orderBy('nombre_programa')->get();
        $estados     = Ficha::estados();
        $modalidades = Ficha::modalidades();

        return view('coordinacion.fichas.index', compact(
            'fichas', 'programas', 'estados', 'modalidades',
            'buscar', 'estado', 'modalidad', 'programa'
        ));
    }

    /**
     * Formulario de creación de una ficha.
     */
    public function create(): View
    {
        $programas   = ProgramaFormacion::orderBy('nombre_programa')->get();
        $instructores = Instructor::with('usuario')->where('estado_instructor', 'activo')->get();
        $estados     = Ficha::estados();
        $modalidades = Ficha::modalidades();

        return view('coordinacion.fichas.create', compact('programas', 'instructores', 'estados', 'modalidades'));
    }

    /**
     * Persiste una ficha nueva junto con su instructor líder (que queda también
     * asociado en el pivote) dentro de una transacción.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validarFicha($request);

        DB::transaction(function () use ($validated) {
            $validated['fecha_asignacion_lider'] = now()->toDateString();
            $ficha = Ficha::create($validated);

            // El líder siempre queda asociado a la ficha.
            $ficha->instructores()->syncWithoutDetaching([
                $ficha->id_instructor_lider => ['fecha_asignacion' => now()->toDateString()],
            ]);

            HistorialInstructorLider::create([
                'id_ficha'               => $ficha->id_ficha,
                'id_instructor_anterior' => null,
                'id_instructor_nuevo'    => $ficha->id_instructor_lider,
                'id_usuario_registra'    => Auth::id(),
                'fecha_cambio'           => now(),
            ]);
        });

        return redirect()
            ->route('coordinacion.fichas.index')
            ->with('success', 'Ficha creada correctamente.');
    }

    /**
     * Detalle de la ficha: aprendices matriculados, instructores asociados,
     * instructor líder, historial de líder y catálogos para las acciones.
     */
    public function show(Ficha $ficha): View
    {
        $ficha->load([
            'programa',
            'instructorLider.usuario',
            'instructores.usuario',
            'matriculas.aprendiz.usuario',
            'historialLider.instructorAnterior.usuario',
            'historialLider.instructorNuevo.usuario',
            'historialLider.usuarioRegistra',
        ]);

        // Instructores que aún no están asociados (para el selector de "agregar").
        $asociadosIds = $ficha->instructores->pluck('id_instructor')->all();
        $instructoresDisponibles = Instructor::with('usuario')
            ->where('estado_instructor', 'activo')
            ->whereNotIn('id_instructor', $asociadosIds)
            ->get();

        // Aprendices que se pueden matricular: los que no tienen ya matrícula
        // activa en esta ficha.
        $matriculadosIds = $ficha->matriculas->pluck('id_aprendiz')->all();
        $aprendicesDisponibles = Aprendiz::with('usuario')
            ->whereNotIn('id_aprendiz', $matriculadosIds)
            ->get();

        $puedeDesignarLider = optional(Auth::user())->esCoordinadorMisional();

        return view('coordinacion.fichas.show', compact(
            'ficha', 'instructoresDisponibles', 'aprendicesDisponibles', 'puedeDesignarLider'
        ));
    }

    /**
     * Formulario de edición.
     */
    public function edit(Ficha $ficha): View
    {
        $programas   = ProgramaFormacion::orderBy('nombre_programa')->get();
        $instructores = $ficha->instructores()->with('usuario')->get();
        $estados     = Ficha::estados();
        $modalidades = Ficha::modalidades();

        return view('coordinacion.fichas.edit', compact('ficha', 'programas', 'instructores', 'estados', 'modalidades'));
    }

    /**
     * Actualiza los datos de una ficha. El instructor líder se cambia desde su
     * acción dedicada; aquí no se toca para respetar la auditoría.
     */
    public function update(Request $request, Ficha $ficha): RedirectResponse
    {
        $validated = $this->validarFicha($request, $ficha);

        // El líder no se modifica en la edición general (tiene flujo propio).
        unset($validated['id_instructor_lider']);

        $ficha->update($validated);

        return redirect()
            ->route('coordinacion.fichas.show', $ficha)
            ->with('success', 'Ficha actualizada correctamente.');
    }

    /**
     * Elimina una ficha siempre que no tenga aprendices matriculados (para
     * proteger la integridad de los datos disciplinarios asociados).
     */
    public function destroy(Ficha $ficha): RedirectResponse
    {
        if ($ficha->matriculas()->exists()) {
            return redirect()
                ->route('coordinacion.fichas.show', $ficha)
                ->withErrors(['error' => 'No se puede eliminar una ficha con aprendices matriculados. Retíralos primero.']);
        }

        DB::transaction(function () use ($ficha) {
            $ficha->historialLider()->delete();
            $ficha->instructores()->detach();
            $ficha->delete();
        });

        return redirect()
            ->route('coordinacion.fichas.index')
            ->with('success', 'Ficha eliminada correctamente.');
    }

    /**
     * Cambia únicamente el estado de la ficha (Activa/Terminada/Cancelada).
     */
    public function actualizarEstado(Request $request, Ficha $ficha): RedirectResponse
    {
        $validated = $request->validate([
            'estado_ficha' => ['required', Rule::in(array_keys(Ficha::estados()))],
        ]);

        $ficha->update(['estado_ficha' => $validated['estado_ficha']]);

        return redirect()
            ->route('coordinacion.fichas.show', $ficha)
            ->with('success', 'Estado de la ficha actualizado a «' . Ficha::estados()[$validated['estado_ficha']] . '».');
    }

    /*
    |--------------------------------------------------------------------------
    | Asociación de instructores
    |--------------------------------------------------------------------------
    */

    /**
     * Asocia uno o varios instructores a la ficha (sin desasociar los ya
     * existentes).
     */
    public function asociarInstructores(Request $request, Ficha $ficha): RedirectResponse
    {
        $validated = $request->validate([
            'instructores'   => ['required', 'array', 'min:1'],
            'instructores.*' => ['integer', 'exists:instructor,id_instructor'],
        ], [], ['instructores' => 'instructores']);

        $datos = collect($validated['instructores'])
            ->mapWithKeys(fn ($id) => [(int) $id => ['fecha_asignacion' => now()->toDateString()]])
            ->all();

        $ficha->instructores()->syncWithoutDetaching($datos);

        return redirect()
            ->route('coordinacion.fichas.show', $ficha)
            ->with('success', 'Instructor(es) asociado(s) a la ficha correctamente.');
    }

    /**
     * Elimina la asociación de un instructor con la ficha. No permite quitar al
     * instructor líder (primero hay que designar otro líder).
     */
    public function eliminarInstructor(Ficha $ficha, Instructor $instructor): RedirectResponse
    {
        if ((int) $ficha->id_instructor_lider === (int) $instructor->id_instructor) {
            return redirect()
                ->route('coordinacion.fichas.show', $ficha)
                ->withErrors(['error' => 'No puedes desasociar al instructor líder. Designa otro líder antes de retirarlo.']);
        }

        $ficha->instructores()->detach($instructor->id_instructor);

        return redirect()
            ->route('coordinacion.fichas.show', $ficha)
            ->with('success', 'Instructor desasociado de la ficha.');
    }

    /**
     * Designa el instructor líder de la ficha. Solo el Coordinador Misional; el
     * instructor debe estar previamente asociado. Registra fecha e historial.
     */
    public function asignarLider(Request $request, Ficha $ficha): RedirectResponse
    {
        $this->exigirCoordinadorMisional();

        $validated = $request->validate([
            'id_instructor_lider' => [
                'required',
                'integer',
                // El instructor debe estar asociado a esta ficha.
                Rule::exists('ficha_instructor', 'id_instructor')->where('id_ficha', $ficha->id_ficha),
            ],
        ], [
            'id_instructor_lider.exists' => 'El instructor debe estar asociado a la ficha antes de designarlo líder.',
        ]);

        $nuevoLider = (int) $validated['id_instructor_lider'];
        $liderAnterior = $ficha->id_instructor_lider !== null ? (int) $ficha->id_instructor_lider : null;

        if ($liderAnterior === $nuevoLider) {
            return redirect()
                ->route('coordinacion.fichas.show', $ficha)
                ->with('success', 'El instructor seleccionado ya es el líder actual de la ficha.');
        }

        DB::transaction(function () use ($ficha, $nuevoLider, $liderAnterior) {
            $ficha->update([
                'id_instructor_lider'    => $nuevoLider,
                'fecha_asignacion_lider' => now()->toDateString(),
            ]);

            HistorialInstructorLider::create([
                'id_ficha'               => $ficha->id_ficha,
                'id_instructor_anterior' => $liderAnterior,
                'id_instructor_nuevo'    => $nuevoLider,
                'id_usuario_registra'    => Auth::id(),
                'fecha_cambio'           => now(),
            ]);
        });

        return redirect()
            ->route('coordinacion.fichas.show', $ficha)
            ->with('success', 'Instructor líder designado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | Asociación de aprendices
    |--------------------------------------------------------------------------
    */

    /**
     * Matricula uno o varios aprendices en la ficha. Valida que ningún aprendiz
     * tenga ya una matrícula activa en otra ficha del mismo programa.
     */
    public function asociarAprendices(Request $request, Ficha $ficha): RedirectResponse
    {
        $validated = $request->validate([
            'aprendices'   => ['required', 'array', 'min:1'],
            'aprendices.*' => ['integer', 'exists:aprendiz,id_aprendiz'],
        ]);

        $ids = array_map('intval', $validated['aprendices']);

        // Aprendices con matrícula activa en otra ficha del mismo programa.
        $conflictivos = Matricula::query()
            ->where('estado_matricula', 'activa')
            ->whereIn('id_aprendiz', $ids)
            ->where('id_ficha', '!=', $ficha->id_ficha)
            ->whereHas('ficha', fn ($q) => $q->where('id_programa', $ficha->id_programa))
            ->pluck('id_aprendiz')
            ->unique()
            ->all();

        if (! empty($conflictivos)) {
            return back()->withErrors([
                'aprendices' => 'Uno o más aprendices ya tienen una matrícula activa en otra ficha del mismo programa. Retíralos de esa ficha primero.',
            ]);
        }

        DB::transaction(function () use ($ficha, $ids) {
            foreach ($ids as $idAprendiz) {
                // Si ya existe matrícula (retirada/aplazada) se reactiva; si no, se crea.
                Matricula::updateOrCreate(
                    ['id_aprendiz' => $idAprendiz, 'id_ficha' => $ficha->id_ficha],
                    ['fecha_matricula' => now()->toDateString(), 'estado_matricula' => 'activa']
                );
            }
        });

        return redirect()
            ->route('coordinacion.fichas.show', $ficha)
            ->with('success', 'Aprendiz(ces) matriculado(s) en la ficha correctamente.');
    }

    /**
     * Retira a un aprendiz de la ficha (marca su matrícula como "retirada" en
     * lugar de borrarla para conservar el historial disciplinario).
     */
    public function retirarAprendiz(Ficha $ficha, Matricula $matricula): RedirectResponse
    {
        if ((int) $matricula->id_ficha !== (int) $ficha->id_ficha) {
            abort(404);
        }

        $matricula->update(['estado_matricula' => 'retirada']);

        return redirect()
            ->route('coordinacion.fichas.show', $ficha)
            ->with('success', 'Aprendiz retirado de la ficha.');
    }

    /*
    |--------------------------------------------------------------------------
    | Validación compartida
    |--------------------------------------------------------------------------
    */

    /**
     * Reglas de validación de la ficha. `numero_ficha` único, fecha de inicio
     * anterior a la de finalización y programa/instructor existentes.
     *
     * @return array<string, mixed>
     */
    private function validarFicha(Request $request, ?Ficha $ficha = null): array
    {
        $numeroUnico = Rule::unique('ficha', 'numero_ficha');
        if ($ficha) {
            $numeroUnico->ignore($ficha->id_ficha, 'id_ficha');
        }

        return $request->validate([
            'id_programa'          => ['required', 'integer', 'exists:programa_formacion,id_programa'],
            'id_instructor_lider'  => ['required', 'integer', 'exists:instructor,id_instructor'],
            'numero_ficha'         => ['required', 'string', 'max:20', $numeroUnico],
            'modalidad'            => ['required', Rule::in(array_keys(Ficha::modalidades()))],
            'estado_ficha'         => ['required', Rule::in(array_keys(Ficha::estados()))],
            'fecha_inicio'         => ['required', 'date'],
            'fecha_fin_programada' => ['nullable', 'date', 'after:fecha_inicio'],
        ], [
            'fecha_fin_programada.after' => 'La fecha de finalización debe ser posterior a la fecha de inicio.',
            'numero_ficha.unique'        => 'Ya existe una ficha con ese número.',
        ]);
    }
}
