<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CreaUsuarios;
use App\Models\ActaCoordinacion;
use App\Models\Aprendiz;
use App\Models\Ficha;
use App\Models\Instructor;
use App\Models\LlamadoAtencion;
use App\Models\Matricula;
use App\Models\ProcesoDisciplinario;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CoordinacionController extends Controller
{
    use CreaUsuarios;

    /**
     * Muestra el dashboard del coordinador con las estadísticas generales.
     */
    public function dashboard(): View
    {
        $totalLlamados = LlamadoAtencion::count();
        $llamadosPendientes = LlamadoAtencion::whereIn('estado_llamado', ['registrado', 'en_revision'])->count();

        $totalActas = ActaCoordinacion::count();
        $actasExpedidas = ActaCoordinacion::where('estado_acta', 'expedido')->count();

        $totalProcesos = ProcesoDisciplinario::count();
        $procesosActivos = ProcesoDisciplinario::where('estado_proceso', 'activo')->count();

        $llamadosRecientes = LlamadoAtencion::with(['aprendiz.usuario', 'instructor.usuario'])
            ->orderByDesc('fecha_llamado')
            ->limit(5)
            ->get();

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

        $actasPorMes = ActaCoordinacion::selectRaw('DATE_FORMAT(fecha_expedicion, "%Y-%m") as month')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('fecha_expedicion', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $procesosPorMes = ProcesoDisciplinario::selectRaw('DATE_FORMAT(fecha_inicio, "%Y-%m") as month')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('fecha_inicio', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $llamadosTrend = array_map(fn ($key) => $llamadosPorMes[$key] ?? 0, $monthKeys);
        $actasTrend = array_map(fn ($key) => $actasPorMes[$key] ?? 0, $monthKeys);
        $llamadosPorEstado = LlamadoAtencion::selectRaw('estado_llamado as estado')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado_llamado')
            ->pluck('total', 'estado')
            ->toArray();

        $actasPorEstado = ActaCoordinacion::selectRaw('estado_acta as estado')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado_acta')
            ->pluck('total', 'estado')
            ->toArray();

        $procesosPorEstado = ProcesoDisciplinario::selectRaw('estado_proceso as estado')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado_proceso')
            ->pluck('total', 'estado')
            ->toArray();

        $llamadosTrend = array_map(fn ($key) => $llamadosPorMes[$key] ?? 0, $monthKeys);
        $actasTrend = array_map(fn ($key) => $actasPorMes[$key] ?? 0, $monthKeys);
        $procesosTrend = array_map(fn ($key) => $procesosPorMes[$key] ?? 0, $monthKeys);

        return view('dashboards.coordinador', compact(
            'totalLlamados',
            'llamadosPendientes',
            'totalActas',
            'actasExpedidas',
            'totalProcesos',
            'procesosActivos',
            'llamadosRecientes',
            'trendLabels',
            'llamadosTrend',
            'actasTrend',
            'procesosTrend',
            'llamadosPorEstado',
            'actasPorEstado',
            'procesosPorEstado',
        ));
    }

    /**
     * Listado de aprendices con buscador y resumen disciplinario.
     */
    public function aprendices(Request $request): View
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $estado = $request->input('estado_academico');

        $aprendices = Aprendiz::query()
            ->with('usuario')
            ->withCount(['llamadosAtencion', 'procesosDisciplinarios', 'actasCoordinacion'])
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->whereHas('usuario', function ($sub) use ($buscar) {
                    $sub->where('nombres', 'like', "%{$buscar}%")
                        ->orWhere('apellidos', 'like', "%{$buscar}%")
                        ->orWhere('correo', 'like', "%{$buscar}%");
                });
            })
            ->when($estado, fn ($q) => $q->where('estado_academico', $estado))
            ->orderBy('id_aprendiz')
            ->paginate(15)
            ->withQueryString();

        $estados = ['en_formacion', 'aplazado', 'cancelado', 'certificado'];

        return view('coordinacion.aprendices.index', compact('aprendices', 'buscar', 'estado', 'estados'));
    }

    /**
     * Formulario para dar de alta un aprendiz. Puede preseleccionarse una ficha
     * (por ejemplo, al llegar desde el detalle de una ficha) para matricularlo.
     */
    public function crearAprendizForm(Request $request): View
    {
        $fichas = Ficha::with('programa')
            ->where('estado_ficha', Ficha::ESTADO_EN_EJECUCION)
            ->orderByDesc('fecha_inicio')
            ->get();

        $fichaSeleccionada = $request->input('id_ficha');

        return view('coordinacion.aprendices.create', compact('fichas', 'fichaSeleccionada'));
    }

    /**
     * Crea un aprendiz (usuario + perfil + rol) y, si se indica una ficha, lo
     * matricula en ella. Todo dentro de una sola transacción.
     */
    public function crearAprendiz(Request $request): RedirectResponse
    {
        $datos = $this->validarPersona($request, [
            'correo_institucional' => ['nullable', 'email', 'max:120'],
            'id_ficha'             => ['nullable', 'integer', 'exists:ficha,id_ficha'],
        ]);

        $aprendizCreado = DB::transaction(function () use ($datos, $request) {
            $usuario = $this->crearUsuarioConRol($datos, Roles::APRENDIZ);

            $aprendiz = Aprendiz::create([
                'id_usuario'                => $usuario->id_usuario,
                'correo_institucional'      => $request->input('correo_institucional') ?: $datos['correo'],
                'correo_personal'           => $datos['correo'],
                'estado_academico'          => 'en_formacion',
                'tiene_apoyo_sostenimiento' => 0,
            ]);

            if (! empty($datos['id_ficha'])) {
                Matricula::create([
                    'id_aprendiz'      => $aprendiz->id_aprendiz,
                    'id_ficha'         => (int) $datos['id_ficha'],
                    'fecha_matricula'  => now()->toDateString(),
                    'estado_matricula' => 'activa',
                ]);
            }

            return $aprendiz;
        });

        $mensaje = empty($datos['id_ficha'])
            ? 'Aprendiz creado correctamente. Su contraseña inicial es el número de documento.'
            : 'Aprendiz creado y matriculado en la ficha. Su contraseña inicial es el número de documento.';

        return redirect()
            ->route('coordinacion.aprendices.show', $aprendizCreado->id_aprendiz)
            ->with('success', $mensaje);
    }

    /**
     * Hoja de vida consolidada de un aprendiz (vista compartida).
     */
    public function aprendizShow(string $id): View
    {
        $aprendiz = Aprendiz::with([
            'usuario',
            'llamadosAtencion' => fn ($q) => $q->orderByDesc('fecha_llamado'),
            'llamadosAtencion.instructor.usuario',
            'actasCoordinacion' => fn ($q) => $q->orderByDesc('fecha_expedicion'),
            'procesosDisciplinarios' => fn ($q) => $q->orderByDesc('fecha_inicio'),
            'matriculas.ficha.programa',
        ])->findOrFail($id);

        $volver = route('coordinacion.aprendices.index');
        $layout = 'layouts.coordinador';

        return view('aprendices.show', compact('aprendiz', 'volver', 'layout'));
    }

    /*
    |--------------------------------------------------------------------------
    | Docentes (instructores a cargo de la coordinación)
    |--------------------------------------------------------------------------
    */

    /**
     * Listado de docentes con sus fichas asignadas, si lideran alguna ficha y
     * el tipo de docente (materia o transversal).
     */
    public function docentes(Request $request): View
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $tipo   = $request->input('tipo_docente');
        $estado = $request->input('estado_instructor');

        $docentes = Instructor::query()
            ->with('usuario')
            ->withCount(['fichas', 'fichasLideradas'])
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where('codigo_instructor', 'like', "%{$buscar}%")
                    ->orWhere('area_formacion', 'like', "%{$buscar}%")
                    ->orWhereHas('usuario', fn ($u) => $u
                        ->where('nombres', 'like', "%{$buscar}%")
                        ->orWhere('apellidos', 'like', "%{$buscar}%")
                        ->orWhere('numero_documento', 'like', "%{$buscar}%"));
            })
            ->when($tipo, fn ($q) => $q->where('tipo_docente', $tipo))
            ->when($estado, fn ($q) => $q->where('estado_instructor', $estado))
            ->orderBy('id_instructor')
            ->paginate(15)
            ->withQueryString();

        $tipos = Instructor::tiposDocente();

        return view('coordinacion.docentes.index', compact('docentes', 'tipos', 'buscar', 'tipo', 'estado'));
    }

    /**
     * Detalle de un docente: datos, tipo, fichas asignadas y en cuáles es líder.
     */
    public function docenteShow(Instructor $instructor): View
    {
        $instructor->load([
            'usuario',
            'fichas.programa',
            'fichas.instructorLider',
            'fichasLideradas',
        ]);

        $tipos = Instructor::tiposDocente();

        return view('coordinacion.docentes.show', compact('instructor', 'tipos'));
    }

    /**
     * Clasifica al docente como de materia o transversal.
     */
    public function actualizarTipoDocente(Request $request, Instructor $instructor): RedirectResponse
    {
        $validated = $request->validate([
            'tipo_docente' => ['nullable', Rule::in(array_keys(Instructor::tiposDocente()))],
        ]);

        $instructor->update(['tipo_docente' => $validated['tipo_docente'] ?? null]);

        return back()->with('success', 'Tipo de docente actualizado correctamente.');
    }

    // La gestión de fichas (listado, CRUD, asociaciones e instructor líder) se
    // trasladó a App\Http\Controllers\FichaController.
}
