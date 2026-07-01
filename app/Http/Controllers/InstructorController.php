<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Aprendiz;
use App\Models\Ficha;
use App\Models\LlamadoAtencion;
use App\Models\Notificacion;
use App\Models\ProcesoDisciplinario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InstructorController extends Controller
{
    /**
     * Devuelve el instructor autenticado o aborta el acceso.
     */
    private function getInstructor()
    {
        $instructor = Auth::user()->instructor;
        if (! $instructor) {
            abort(403, 'Acceso denegado: El usuario no es un instructor.');
        }
        return $instructor;
    }

    /**
     * Muestra el panel de control del Instructor.
     */
    public function dashboard(): View
    {
        $instructor = $this->getInstructor();

        // Obtener llamados reportados por este instructor
        $llamados = LlamadoAtencion::with('aprendiz.usuario')
                        ->where('id_instructor', $instructor->id_instructor)
                        ->orderByDesc('fecha_llamado')
                        ->get();

        return view('dashboards.instructor', compact('instructor', 'llamados'));
    }

    /**
     * Fichas en las que el instructor está asignado (como líder o como
     * instructor asociado), con sus aprendices.
     */
    public function fichas(): View
    {
        $instructor = $this->getInstructor();

        $fichaIds = $this->fichasDelInstructor($instructor);

        $fichas = Ficha::query()
            ->whereIn('id_ficha', $fichaIds)
            ->with(['programa', 'instructorLider.usuario', 'matriculas.aprendiz.usuario'])
            ->orderByDesc('fecha_inicio')
            ->get();

        return view('instructor.fichas.index', compact('fichas', 'instructor'));
    }

    /**
     * Historial disciplinario de todos los aprendices de una ficha. Cualquier
     * instructor asociado a la ficha puede consultarlo (solo lectura); el
     * instructor propietario de un registro conserva sus permisos de edición.
     */
    public function fichaShow(Ficha $ficha): View
    {
        $instructor = $this->getInstructor();

        // Verificación de permiso en backend: debe estar asignado a la ficha.
        if (! $this->fichasDelInstructor($instructor)->contains($ficha->id_ficha)) {
            abort(403, 'No estás asignado a esta ficha.');
        }

        $ficha->load([
            'programa',
            'instructorLider.usuario',
            'matriculas.aprendiz.usuario',
            'matriculas.aprendiz.llamadosAtencion' => fn ($q) => $q->orderByDesc('fecha_llamado'),
            'matriculas.aprendiz.llamadosAtencion.instructor.usuario',
            'matriculas.aprendiz.procesosDisciplinarios' => fn ($q) => $q->orderByDesc('fecha_inicio'),
            'matriculas.aprendiz.procesosDisciplinarios.llamadoAtencion.instructor.usuario',
        ]);

        return view('instructor.fichas.show', compact('ficha', 'instructor'));
    }

    /**
     * IDs de las fichas a las que el instructor está asignado (pivote
     * ficha_instructor) o que lidera. Se calcula una sola vez por petición.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function fichasDelInstructor($instructor): \Illuminate\Support\Collection
    {
        return $instructor->fichas()->pluck('ficha.id_ficha')
            ->merge($instructor->fichasLideradas()->pluck('id_ficha'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    /**
     * Hoja de vida consolidada de un aprendiz (solo lectura).
     */
    public function aprendizShow(string $id): View
    {
        $this->getInstructor();

        $aprendiz = Aprendiz::with([
            'usuario',
            'llamadosAtencion' => fn ($q) => $q->orderByDesc('fecha_llamado'),
            'llamadosAtencion.instructor.usuario',
            'actasCoordinacion' => fn ($q) => $q->orderByDesc('fecha_expedicion'),
            'procesosDisciplinarios' => fn ($q) => $q->orderByDesc('fecha_inicio'),
            'matriculas.ficha.programa',
        ])->findOrFail($id);

        $volver = route('instructor.fichas.index');
        $layout = 'layouts.instructor';

        return view('aprendices.show', compact('aprendiz', 'volver', 'layout'));
    }

    /**
     * Seguimiento (solo lectura) de los procesos disciplinarios originados
     * por los llamados que reportó el instructor.
     */
    public function procesos(): View
    {
        $instructor = $this->getInstructor();

        $procesos = ProcesoDisciplinario::with(['aprendiz.usuario', 'llamadoAtencion'])
            ->whereHas('llamadoAtencion', fn ($q) => $q->where('id_instructor', $instructor->id_instructor))
            ->orderByDesc('fecha_inicio')
            ->paginate(15);

        return view('instructor.procesos.index', compact('procesos'));
    }

    /**
     * Notificaciones generadas a partir de los llamados del instructor.
     */
    public function notificaciones(): View
    {
        $instructor = $this->getInstructor();

        $notificaciones = Notificacion::with(['aprendiz.usuario', 'llamado'])
            ->whereHas('llamado', fn ($q) => $q->where('id_instructor', $instructor->id_instructor))
            ->orderByDesc('fecha_envio')
            ->paginate(15);

        return view('instructor.notificaciones.index', compact('notificaciones'));
    }
}
