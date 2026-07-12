<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CreaUsuarios;
use App\Models\Aprendiz;
use App\Models\Ficha;
use App\Models\Instructor;
use App\Models\Matricula;
use App\Support\Busqueda;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Gestión de aprendices desde el rol INSTRUCTOR, limitada a las fichas donde
 * el instructor imparte clases (asociado o líder):
 *
 *  · Ver los aprendices matriculados en sus fichas.
 *  · Crear un aprendiz nuevo y matricularlo en una de sus fichas.
 *  · Asociar (matricular) un aprendiz ya existente en una de sus fichas.
 *
 * Reutiliza la misma lógica de alta de personas del coordinador (trait
 * CreaUsuarios) y las mismas reglas de matrícula (sin dobles matrículas
 * activas en el mismo programa y sin solaparse con su rol de instructor).
 */
class InstructorAprendizController extends Controller
{
    use CreaUsuarios;

    /**
     * Valida que el usuario autenticado sea instructor y lo devuelve.
     */
    private function getInstructor(): Instructor
    {
        $instructor = Auth::user()->instructor;
        if (! $instructor) {
            abort(403, 'Acceso denegado: El usuario no es un instructor.');
        }

        return $instructor;
    }

    /**
     * Fichas donde el instructor imparte clases (asociado o líder).
     *
     * @return Collection<int, Ficha>
     */
    private function fichasDelInstructor(Instructor $instructor): Collection
    {
        return $instructor->fichas()->with('programa')->get()
            ->merge($instructor->fichasLideradas()->with('programa')->get())
            ->unique('id_ficha')
            ->sortBy('numero_ficha')
            ->values();
    }

    /**
     * Lista los aprendices matriculados en las fichas del instructor, con
     * buscador y filtro por ficha.
     */
    public function index(Request $request): View
    {
        $instructor = $this->getInstructor();
        $fichas = $this->fichasDelInstructor($instructor);
        $fichasIds = $fichas->pluck('id_ficha')->map(fn ($id) => (int) $id)->all();

        $buscar = trim((string) $request->input('buscar', ''));
        $idFicha = (int) $request->input('id_ficha', 0);

        $matriculas = Matricula::query()
            ->with(['aprendiz.usuario', 'ficha.programa'])
            ->whereIn('id_ficha', $fichasIds)
            ->when($idFicha > 0, fn ($q) => $q->where('id_ficha', $idFicha))
            // Búsqueda con inferencia por nombre, apellido o documento.
            ->when($buscar !== '', function ($q) use ($buscar) {
                foreach (Busqueda::tokens($buscar) as $token) {
                    $q->whereHas('aprendiz.usuario', function ($u) use ($token) {
                        $u->where(function ($sub) use ($token) {
                            $sub->where('nombres', 'like', "%{$token}%")
                                ->orWhere('apellidos', 'like', "%{$token}%")
                                ->orWhere('numero_documento', 'like', "%{$token}%");
                        });
                    });
                }
            })
            ->orderByDesc('fecha_matricula')
            ->paginate(10)
            ->withQueryString();

        // Aprendices del sistema para el buscador de "asociar existente"
        // (se excluyen los que ya tienen matrícula activa en alguna ficha del instructor).
        $yaEnMisFichas = Matricula::whereIn('id_ficha', $fichasIds)
            ->where('estado_matricula', 'activa')
            ->pluck('id_aprendiz')
            ->all();

        $aprendicesAsociables = Aprendiz::with('usuario')
            ->whereNotIn('id_aprendiz', $yaEnMisFichas)
            ->get()
            ->map(fn (Aprendiz $a) => [
                'id'    => $a->id_aprendiz,
                'label' => trim(($a->usuario->nombres ?? '') . ' ' . ($a->usuario->apellidos ?? '')) ?: 'Aprendiz #' . $a->id_aprendiz,
                'doc'   => (string) ($a->usuario->numero_documento ?? ''),
            ])
            ->values();

        return view('instructor.aprendices.index', compact(
            'matriculas', 'fichas', 'buscar', 'idFicha', 'aprendicesAsociables'
        ));
    }

    /**
     * Formulario para crear un aprendiz nuevo (matriculado en una de sus fichas).
     */
    public function crearForm(): View
    {
        $instructor = $this->getInstructor();
        $fichas = $this->fichasDelInstructor($instructor);

        if ($fichas->isEmpty()) {
            return view('instructor.aprendices.create', compact('fichas'));
        }

        return view('instructor.aprendices.create', compact('fichas'));
    }

    /**
     * Crea el aprendiz (usuario + perfil + rol Aprendiz) y lo matricula en la
     * ficha elegida, que debe ser una de las fichas del instructor.
     */
    public function crear(Request $request): RedirectResponse
    {
        $instructor = $this->getInstructor();
        $fichasIds = $this->fichasDelInstructor($instructor)->pluck('id_ficha')->map(fn ($id) => (int) $id)->all();

        $datos = $this->validarPersona($request, [
            'id_ficha' => ['required', 'integer', 'exists:ficha,id_ficha'],
        ]);

        if (! in_array((int) $datos['id_ficha'], $fichasIds, true)) {
            return back()->withInput()->withErrors([
                'id_ficha' => 'Solo puedes matricular aprendices en las fichas donde impartes clases.',
            ]);
        }

        DB::transaction(function () use ($datos) {
            // El instructor solo da de alta APRENDICES (sin roles adicionales).
            $usuario = $this->crearUsuarioConRol($datos, Roles::APRENDIZ);

            $aprendiz = Aprendiz::create([
                'id_usuario'                => $usuario->id_usuario,
                'correo_institucional'      => $datos['correo'],
                'correo_personal'           => $datos['correo'],
                'estado_academico'          => 'en_formacion',
                'tiene_apoyo_sostenimiento' => 0,
            ]);

            Matricula::create([
                'id_aprendiz'      => $aprendiz->id_aprendiz,
                'id_ficha'         => (int) $datos['id_ficha'],
                'fecha_matricula'  => now()->toDateString(),
                'estado_matricula' => 'activa',
            ]);
        });

        return redirect()
            ->route('instructor.aprendices.index')
            ->with('success', 'Aprendiz creado y matriculado en la ficha correctamente. Su contraseña inicial es el número de documento.');
    }

    /**
     * Asocia (matricula) un aprendiz EXISTENTE en una de las fichas del
     * instructor, con las mismas reglas del coordinador.
     */
    public function asociar(Request $request): RedirectResponse
    {
        $instructor = $this->getInstructor();
        $fichasIds = $this->fichasDelInstructor($instructor)->pluck('id_ficha')->map(fn ($id) => (int) $id)->all();

        $validated = $request->validate([
            'id_aprendiz' => ['required', 'integer', 'exists:aprendiz,id_aprendiz'],
            'id_ficha'    => ['required', 'integer', 'exists:ficha,id_ficha'],
        ], [
            'id_aprendiz.required' => 'Selecciona el aprendiz que quieres asociar.',
        ]);

        $idAprendiz = (int) $validated['id_aprendiz'];
        $idFicha = (int) $validated['id_ficha'];

        if (! in_array($idFicha, $fichasIds, true)) {
            return back()->withErrors([
                'id_ficha' => 'Solo puedes matricular aprendices en las fichas donde impartes clases.',
            ]);
        }

        $ficha = Ficha::findOrFail($idFicha);

        // Sin doble matrícula activa en otra ficha del mismo programa.
        $conflicto = Matricula::query()
            ->where('estado_matricula', 'activa')
            ->where('id_aprendiz', $idAprendiz)
            ->where('id_ficha', '!=', $ficha->id_ficha)
            ->whereHas('ficha', fn ($q) => $q->where('id_programa', $ficha->id_programa))
            ->exists();

        if ($conflicto) {
            return back()->withErrors([
                'id_aprendiz' => 'Este aprendiz ya tiene una matrícula activa en otra ficha del mismo programa.',
            ]);
        }

        // Un aprendiz que también sea instructor no puede matricularse en la
        // ficha donde él mismo imparte clases.
        if ($this->aprendizEsInstructorDeFicha($idAprendiz, $ficha->id_ficha)) {
            return back()->withErrors([
                'id_aprendiz' => 'Este aprendiz es instructor asignado a esta misma ficha y no puede matricularse en ella.',
            ]);
        }

        // Si ya existía matrícula (retirada/aplazada) en esta ficha se reactiva.
        Matricula::updateOrCreate(
            ['id_aprendiz' => $idAprendiz, 'id_ficha' => $ficha->id_ficha],
            ['fecha_matricula' => now()->toDateString(), 'estado_matricula' => 'activa']
        );

        return redirect()
            ->route('instructor.aprendices.index')
            ->with('success', 'Aprendiz matriculado en la ficha correctamente.');
    }

    /**
     * Reporte individual del aprendiz (PDF/Excel/Word) para el instructor:
     * solo disponible para aprendices matriculados en sus fichas. Reutiliza
     * el mismo generador del coordinador.
     */
    public function reporte(string $id, string $formato)
    {
        $instructor = $this->getInstructor();
        $fichasIds = $this->fichasDelInstructor($instructor)->pluck('id_ficha')->map(fn ($f) => (int) $f)->all();

        $esDeSusFichas = Matricula::where('id_aprendiz', (int) $id)
            ->whereIn('id_ficha', $fichasIds)
            ->exists();

        abort_unless($esDeSusFichas, 403, 'Solo puedes generar reportes de aprendices matriculados en tus fichas.');

        return app(CoordinacionReporteController::class)->aprendizIndividual($id, $formato);
    }

    /**
     * Indica si el aprendiz también tiene perfil de instructor asignado (o
     * líder) en esa misma ficha (misma regla que usa coordinación).
     */
    private function aprendizEsInstructorDeFicha(int $idAprendiz, int $idFicha): bool
    {
        $aprendiz = Aprendiz::find($idAprendiz);
        if (! $aprendiz || ! $aprendiz->id_usuario) {
            return false;
        }

        $instructor = Instructor::where('id_usuario', $aprendiz->id_usuario)->first();
        if (! $instructor) {
            return false;
        }

        return $instructor->fichas()->where('ficha.id_ficha', $idFicha)->exists()
            || $instructor->fichasLideradas()->where('id_ficha', $idFicha)->exists();
    }
}
