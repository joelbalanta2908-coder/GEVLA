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
use App\Models\Rol;
use App\Models\Usuario;
use App\Support\Busqueda;
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
     * Centro de reportes del coordinador: generación y exportación de los
     * reportes de llamados, actas y procesos, con filtro por ficha.
     */
    public function reportes(): View
    {
        $fichas = Ficha::orderBy('numero_ficha')->get();

        $resumen = [
            'llamados' => LlamadoAtencion::count(),
            'actas'    => ActaCoordinacion::count(),
            'procesos' => ProcesoDisciplinario::count(),
        ];

        return view('coordinacion.reportes.index', compact('fichas', 'resumen'));
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
            // Búsqueda con inferencia: coincidencia parcial por cada palabra.
            ->when($buscar !== '', function ($q) use ($buscar) {
                foreach (Busqueda::tokens($buscar) as $token) {
                    $q->whereHas('usuario', function ($sub) use ($token) {
                        $sub->where('nombres', 'like', "%{$token}%")
                            ->orWhere('apellidos', 'like', "%{$token}%")
                            ->orWhere('correo', 'like', "%{$token}%")
                            ->orWhere('numero_documento', 'like', "%{$token}%");
                    });
                }
            })
            ->when($estado, fn ($q) => $q->where('estado_academico', $estado))
            ->orderBy('id_aprendiz')
            ->paginate(10)
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
        $roles = $this->validarRolesSolicitados($request->input('roles', []), Roles::APRENDIZ);

        $aprendizCreado = DB::transaction(function () use ($datos, $request, $roles) {
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

            // Roles adicionales marcados en el formulario (Instructor y/o
            // Coordinador), respetando la matriz de compatibilidad.
            $this->sincronizarRolesAdicionales($usuario, $roles, Roles::APRENDIZ);

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
     * Activa o inactiva la cuenta de un aprendiz. Una cuenta inactiva no puede
     * iniciar sesión (lo valida LoginController). No toca cuentas bloqueadas
     * por el administrador.
     */
    public function actualizarEstadoAprendiz(Aprendiz $aprendiz): RedirectResponse
    {
        $usuario = $aprendiz->usuario;

        if (! $usuario) {
            return back()->withErrors(['error' => 'El aprendiz no tiene una cuenta de usuario asociada.']);
        }

        if ($usuario->estado_usuario === 'bloqueado') {
            return back()->withErrors(['error' => 'La cuenta está bloqueada por el administrador; no puede modificarse desde coordinación.']);
        }

        $nuevo = $usuario->estado_usuario === 'activo' ? 'inactivo' : 'activo';
        $usuario->update(['estado_usuario' => $nuevo]);

        $nombre = trim($usuario->nombres . ' ' . $usuario->apellidos);

        return back()->with('success', $nuevo === 'activo'
            ? "Aprendiz {$nombre} activado correctamente."
            : "Aprendiz {$nombre} inactivado correctamente. No podrá iniciar sesión mientras esté inactivo.");
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
        // El coordinador puede editar los datos del aprendiz desde su hoja de vida.
        $editarUrl = route('coordinacion.aprendices.editar', $aprendiz->id_aprendiz);

        return view('aprendices.show', compact('aprendiz', 'volver', 'layout', 'editarUrl'));
    }

    /**
     * Formulario para editar los datos personales de un aprendiz ya creado.
     */
    public function editarAprendizForm(Aprendiz $aprendiz): View|RedirectResponse
    {
        $usuario = $aprendiz->usuario;

        if (! $usuario) {
            return back()->withErrors(['error' => 'El aprendiz no tiene una cuenta de usuario asociada.']);
        }

        if ($usuario->estado_usuario === 'bloqueado') {
            return back()->withErrors(['error' => 'La cuenta está bloqueada por el administrador; no puede editarse desde coordinación.']);
        }

        $estadosAcademicos = ['en_formacion', 'aplazado', 'cancelado', 'certificado'];

        return view('coordinacion.aprendices.edit', compact('aprendiz', 'usuario', 'estadosAcademicos'));
    }

    /**
     * Actualiza los datos personales y académicos de un aprendiz (usuario +
     * perfil) dentro de una sola transacción.
     */
    public function actualizarAprendiz(Request $request, Aprendiz $aprendiz): RedirectResponse
    {
        $usuario = $aprendiz->usuario;

        if (! $usuario) {
            return back()->withErrors(['error' => 'El aprendiz no tiene una cuenta de usuario asociada.']);
        }

        if ($usuario->estado_usuario === 'bloqueado') {
            return back()->withErrors(['error' => 'La cuenta está bloqueada por el administrador; no puede editarse desde coordinación.']);
        }

        $datos = $this->validarPersonaEdicion($request, $usuario, [
            'correo_institucional' => ['nullable', 'email', 'max:120'],
            'estado_academico'     => ['required', Rule::in(['en_formacion', 'aplazado', 'cancelado', 'certificado'])],
        ]);
        $roles = $this->validarRolesSolicitados($request->input('roles', []), Roles::APRENDIZ);

        DB::transaction(function () use ($datos, $usuario, $aprendiz, $roles) {
            $this->actualizarDatosUsuario($usuario, $datos);

            $aprendiz->update([
                // El correo personal sigue al correo principal de la cuenta,
                // como en el alta.
                'correo_personal'      => $datos['correo'],
                'correo_institucional' => $datos['correo_institucional'] ?: $datos['correo'],
                'estado_academico'     => $datos['estado_academico'],
            ]);

            // Roles adicionales: el rol Aprendiz lo sigue gestionando el
            // estado_academico de arriba; aquí solo se sincronizan
            // Instructor/Coordinador si se marcaron o se quitaron.
            $this->sincronizarRolesAdicionales($usuario, $roles, Roles::APRENDIZ);
        });

        return redirect()
            ->route('coordinacion.aprendices.show', $aprendiz->id_aprendiz)
            ->with('success', 'Datos del aprendiz actualizados correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | Coordinadores (gestión de los perfiles de coordinación)
    |--------------------------------------------------------------------------
    */

    /**
     * Listado de coordinadores con búsqueda por inferencia y filtro de estado.
     */
    public function coordinadores(Request $request): View
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $estado = $request->input('estado_coordinacion');

        $coordinadores = \App\Models\Coordinacion::query()
            ->with('usuario')
            ->withCount('llamadosAtencion')
            ->when($buscar !== '', function ($q) use ($buscar) {
                foreach (Busqueda::tokens($buscar) as $token) {
                    $q->where(function ($sub) use ($token) {
                        $sub->where('cargo', 'like', "%{$token}%")
                            ->orWhere('dependencia', 'like', "%{$token}%")
                            ->orWhereHas('usuario', fn ($u) => $u
                                ->where('nombres', 'like', "%{$token}%")
                                ->orWhere('apellidos', 'like', "%{$token}%")
                                ->orWhere('numero_documento', 'like', "%{$token}%"));
                    });
                }
            })
            ->when($estado, fn ($q) => $q->where('estado_coordinacion', $estado))
            ->orderBy('id_coordinacion')
            ->paginate(10)
            ->withQueryString();

        return view('coordinacion.coordinadores.index', compact('coordinadores', 'buscar', 'estado'));
    }

    /**
     * Formulario para dar de alta un coordinador.
     */
    public function crearCoordinadorForm(): View
    {
        return view('coordinacion.coordinadores.create');
    }

    /**
     * Crea un coordinador (usuario + perfil + rol) en una sola transacción.
     */
    public function crearCoordinador(Request $request): RedirectResponse
    {
        $datos = $this->validarPersona($request, [
            'cargo'       => ['nullable', 'string', 'max:100'],
            'dependencia' => ['nullable', 'string', 'max:120'],
        ]);
        $roles = $this->validarRolesSolicitados($request->input('roles', []), Roles::COORDINADOR);

        DB::transaction(function () use ($datos, $request, $roles) {
            $usuario = $this->crearUsuarioConRol($datos, Roles::COORDINADOR);

            \App\Models\Coordinacion::create([
                'id_usuario'           => $usuario->id_usuario,
                'cargo'                => $request->input('cargo') ?: 'Coordinador Misional',
                'dependencia'          => $request->input('dependencia'),
                'estado_coordinacion'  => 'activo',
            ]);

            // Roles adicionales marcados en el formulario (solo Instructor es
            // compatible con Coordinador; Aprendiz queda bloqueado por la
            // matriz de compatibilidad).
            $this->sincronizarRolesAdicionales($usuario, $roles, Roles::COORDINADOR);
        });

        return redirect()
            ->route('coordinacion.coordinadores.index')
            ->with('success', 'Coordinador creado correctamente. Si no indicaste contraseña, la inicial es su número de documento.');
    }

    /**
     * Formulario para editar los datos de un coordinador ya creado.
     */
    public function editarCoordinadorForm(\App\Models\Coordinacion $coordinador): View|RedirectResponse
    {
        $usuario = $coordinador->usuario;

        if (! $usuario) {
            return back()->withErrors(['error' => 'El coordinador no tiene una cuenta de usuario asociada.']);
        }

        if ($usuario->estado_usuario === 'bloqueado') {
            return back()->withErrors(['error' => 'La cuenta está bloqueada por el administrador; no puede editarse desde coordinación.']);
        }

        return view('coordinacion.coordinadores.edit', compact('coordinador', 'usuario'));
    }

    /**
     * Actualiza los datos de un coordinador (usuario + perfil) en transacción.
     */
    public function actualizarCoordinador(Request $request, \App\Models\Coordinacion $coordinador): RedirectResponse
    {
        $usuario = $coordinador->usuario;

        if (! $usuario) {
            return back()->withErrors(['error' => 'El coordinador no tiene una cuenta de usuario asociada.']);
        }

        if ($usuario->estado_usuario === 'bloqueado') {
            return back()->withErrors(['error' => 'La cuenta está bloqueada por el administrador; no puede editarse desde coordinación.']);
        }

        $datos = $this->validarPersonaEdicion($request, $usuario, [
            'cargo'       => ['nullable', 'string', 'max:100'],
            'dependencia' => ['nullable', 'string', 'max:120'],
        ]);
        $roles = $this->validarRolesSolicitados($request->input('roles', []), Roles::COORDINADOR);

        DB::transaction(function () use ($datos, $usuario, $coordinador, $roles) {
            $this->actualizarDatosUsuario($usuario, $datos);

            $coordinador->update([
                'cargo'       => $datos['cargo'] ?: $coordinador->cargo,
                'dependencia' => $datos['dependencia'] ?? null,
            ]);

            $this->sincronizarRolesAdicionales($usuario, $roles, Roles::COORDINADOR);
        });

        return redirect()
            ->route('coordinacion.coordinadores.index')
            ->with('success', 'Datos del coordinador actualizados correctamente.');
    }

    /**
     * Activa o inactiva un coordinador (perfil + cuenta). Nadie puede
     * inactivarse a sí mismo ni tocar cuentas bloqueadas.
     */
    public function actualizarEstadoCoordinador(\App\Models\Coordinacion $coordinador): RedirectResponse
    {
        $usuario = $coordinador->usuario;

        if ($usuario && (int) $usuario->id_usuario === (int) auth()->id()) {
            return back()->withErrors(['error' => 'No puedes cambiar el estado de tu propia cuenta.']);
        }

        if ($usuario && $usuario->estado_usuario === 'bloqueado') {
            return back()->withErrors(['error' => 'La cuenta está bloqueada por el administrador; no puede modificarse desde coordinación.']);
        }

        $nuevo = $coordinador->estado_coordinacion === 'activo' ? 'inactivo' : 'activo';

        DB::transaction(function () use ($coordinador, $usuario, $nuevo) {
            $coordinador->update(['estado_coordinacion' => $nuevo]);
            $usuario?->update(['estado_usuario' => $nuevo]);
        });

        $nombre = $usuario ? trim($usuario->nombres . ' ' . $usuario->apellidos) : ('#' . $coordinador->id_coordinacion);

        return back()->with('success', $nuevo === 'activo'
            ? "Coordinador {$nombre} activado correctamente."
            : "Coordinador {$nombre} inactivado correctamente. No podrá iniciar sesión mientras esté inactivo.");
    }

    /**
     * Elimina un coordinador definitivamente. Solo si no tiene llamados
     * gestionados a su nombre y no es la propia cuenta.
     */
    public function eliminarCoordinador(\App\Models\Coordinacion $coordinador): RedirectResponse
    {
        $usuario = $coordinador->usuario;

        if ($usuario && (int) $usuario->id_usuario === (int) auth()->id()) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propia cuenta.']);
        }

        if ($usuario && $usuario->estado_usuario === 'bloqueado') {
            return back()->withErrors(['error' => 'La cuenta está bloqueada por el administrador; no puede eliminarse desde coordinación.']);
        }

        if ($coordinador->llamadosAtencion()->exists()) {
            return back()->withErrors([
                'error' => 'No se puede eliminar el coordinador porque tiene llamados de atención gestionados a su nombre. Inactívalo para conservar la trazabilidad.',
            ]);
        }

        $nombre = $usuario ? trim($usuario->nombres . ' ' . $usuario->apellidos) : ('#' . $coordinador->id_coordinacion);

        DB::transaction(function () use ($coordinador, $usuario) {
            $coordinador->delete();

            if (! $usuario) {
                return;
            }

            $rolCoordinador = \App\Models\Rol::where('nombre_rol', Roles::COORDINADOR)->value('id_rol');
            if ($rolCoordinador) {
                $usuario->roles()->detach($rolCoordinador);
            }

            if ($usuario->roles()->count() === 0) {
                try {
                    $usuario->delete();
                } catch (\Illuminate\Database\QueryException) {
                    $usuario->update(['estado_usuario' => 'inactivo']);
                }
            }
        });

        return redirect()
            ->route('coordinacion.coordinadores.index')
            ->with('success', "Coordinador {$nombre} eliminado correctamente.");
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
            // Búsqueda con inferencia: coincidencia parcial por cada palabra.
            ->when($buscar !== '', function ($q) use ($buscar) {
                foreach (Busqueda::tokens($buscar) as $token) {
                    $q->where(function ($sub) use ($token) {
                        $sub->where('codigo_instructor', 'like', "%{$token}%")
                            ->orWhere('area_formacion', 'like', "%{$token}%")
                            ->orWhereHas('usuario', fn ($u) => $u
                                ->where('nombres', 'like', "%{$token}%")
                                ->orWhere('apellidos', 'like', "%{$token}%")
                                ->orWhere('numero_documento', 'like', "%{$token}%"));
                    });
                }
            })
            ->when($tipo, fn ($q) => $q->where('tipo_docente', $tipo))
            ->when($estado, fn ($q) => $q->where('estado_instructor', $estado))
            ->orderBy('id_instructor')
            ->paginate(10)
            ->withQueryString();

        $tipos = Instructor::tiposDocente();

        return view('coordinacion.docentes.index', compact('docentes', 'tipos', 'buscar', 'tipo', 'estado'));
    }

    /**
     * Formulario para dar de alta un instructor desde la sección de Instructores.
     */
    public function crearDocenteForm(): View
    {
        $tipos = Instructor::tiposDocente();

        return view('coordinacion.docentes.create', compact('tipos'));
    }

    /**
     * Crea un instructor (usuario + perfil + rol) desde la sección de
     * Instructores, dentro de una sola transacción.
     */
    public function crearDocente(Request $request): RedirectResponse
    {
        $datos = $this->validarPersona($request, [
            'codigo_instructor' => ['nullable', 'string', 'max:30', 'unique:instructor,codigo_instructor'],
            'area_formacion'    => ['nullable', 'string', 'max:120'],
            'tipo_docente'      => ['nullable', Rule::in(array_keys(Instructor::tiposDocente()))],
        ]);
        $roles = $this->validarRolesSolicitados($request->input('roles', []), Roles::INSTRUCTOR);

        DB::transaction(function () use ($datos, $request, $roles) {
            $usuario = $this->crearUsuarioConRol($datos, Roles::INSTRUCTOR);

            Instructor::create([
                'id_usuario'        => $usuario->id_usuario,
                'codigo_instructor' => $request->input('codigo_instructor') ?: $this->generarCodigoInstructor(),
                'area_formacion'    => $request->input('area_formacion'),
                'tipo_docente'      => $request->input('tipo_docente') ?: null,
                'estado_instructor' => 'activo',
            ]);

            // Roles adicionales marcados en el formulario (Aprendiz y/o
            // Coordinador), respetando la matriz de compatibilidad.
            $this->sincronizarRolesAdicionales($usuario, $roles, Roles::INSTRUCTOR);
        });

        return redirect()
            ->route('coordinacion.docentes.index')
            ->with('success', 'Instructor creado correctamente. Si no indicaste contraseña, la inicial es su número de documento.');
    }

    /**
     * Genera el siguiente código de instructor consecutivo (INS-001, INS-002…).
     */
    private function generarCodigoInstructor(): string
    {
        $maximo = Instructor::where('codigo_instructor', 'like', 'INS-%')
            ->get()
            ->map(fn (Instructor $i) => (int) preg_replace('/\D/', '', (string) $i->codigo_instructor))
            ->max();

        return 'INS-' . str_pad((string) (((int) $maximo) + 1), 3, '0', STR_PAD_LEFT);
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
     * Elimina un instructor definitivamente. Solo se permite cuando no tiene
     * historia en el sistema (llamados, fichas o liderazgos); si la tiene, lo
     * correcto es inactivarlo para conservar la trazabilidad.
     */
    public function eliminarDocente(Instructor $instructor): RedirectResponse
    {
        $usuario = $instructor->usuario;

        if ($usuario && $usuario->estado_usuario === 'bloqueado') {
            return back()->withErrors(['error' => 'La cuenta está bloqueada por el administrador; no puede eliminarse desde coordinación.']);
        }

        // Validaciones de integridad: con historia en el sistema no se elimina.
        $motivos = [];
        if ($instructor->llamadosAtencion()->exists()) {
            $motivos[] = 'tiene llamados de atención registrados';
        }
        if ($instructor->fichas()->exists()) {
            $motivos[] = 'tiene fichas asignadas';
        }
        if ($instructor->fichasLideradas()->exists()) {
            $motivos[] = 'es instructor líder de una ficha';
        }
        if (\App\Models\HistorialInstructorLider::where('id_instructor_anterior', $instructor->id_instructor)
            ->orWhere('id_instructor_nuevo', $instructor->id_instructor)
            ->exists()) {
            $motivos[] = 'aparece en el historial de liderazgo de fichas';
        }

        if ($motivos !== []) {
            return back()->withErrors([
                'error' => 'No se puede eliminar el instructor porque ' . implode(', ', $motivos) . '. Inactívalo para que no pueda ingresar ni ser asignado, conservando la trazabilidad.',
            ]);
        }

        $nombre = $usuario ? trim($usuario->nombres . ' ' . $usuario->apellidos) : $instructor->codigo_instructor;

        DB::transaction(function () use ($instructor, $usuario) {
            $instructor->delete();

            if (! $usuario) {
                return;
            }

            // Se retira el rol de instructor; si la cuenta no tiene más roles,
            // se elimina también (si algo más la referencia, solo se inactiva).
            $rolInstructor = \App\Models\Rol::where('nombre_rol', Roles::INSTRUCTOR)->value('id_rol');
            if ($rolInstructor) {
                $usuario->roles()->detach($rolInstructor);
            }

            if ($usuario->roles()->count() === 0) {
                try {
                    $usuario->delete();
                } catch (\Illuminate\Database\QueryException) {
                    $usuario->update(['estado_usuario' => 'inactivo']);
                }
            }
        });

        return redirect()
            ->route('coordinacion.docentes.index')
            ->with('success', "Instructor {$nombre} eliminado correctamente.");
    }

    /**
     * Formulario para editar los datos de un instructor ya creado.
     */
    public function editarDocenteForm(Instructor $instructor): View|RedirectResponse
    {
        $usuario = $instructor->usuario;

        if (! $usuario) {
            return back()->withErrors(['error' => 'El instructor no tiene una cuenta de usuario asociada.']);
        }

        if ($usuario->estado_usuario === 'bloqueado') {
            return back()->withErrors(['error' => 'La cuenta está bloqueada por el administrador; no puede editarse desde coordinación.']);
        }

        $tipos = Instructor::tiposDocente();

        return view('coordinacion.docentes.edit', compact('instructor', 'usuario', 'tipos'));
    }

    /**
     * Actualiza los datos personales y de perfil de un instructor (usuario +
     * instructor) dentro de una sola transacción.
     */
    public function actualizarDocente(Request $request, Instructor $instructor): RedirectResponse
    {
        $usuario = $instructor->usuario;

        if (! $usuario) {
            return back()->withErrors(['error' => 'El instructor no tiene una cuenta de usuario asociada.']);
        }

        if ($usuario->estado_usuario === 'bloqueado') {
            return back()->withErrors(['error' => 'La cuenta está bloqueada por el administrador; no puede editarse desde coordinación.']);
        }

        $datos = $this->validarPersonaEdicion($request, $usuario, [
            'codigo_instructor' => ['nullable', 'string', 'max:30', Rule::unique('instructor', 'codigo_instructor')->ignore($instructor->id_instructor, 'id_instructor')],
            'area_formacion'    => ['nullable', 'string', 'max:120'],
            'tipo_docente'      => ['nullable', Rule::in(array_keys(Instructor::tiposDocente()))],
        ]);
        $roles = $this->validarRolesSolicitados($request->input('roles', []), Roles::INSTRUCTOR);

        DB::transaction(function () use ($datos, $usuario, $instructor, $roles) {
            $this->actualizarDatosUsuario($usuario, $datos);

            $instructor->update([
                // Si el código se deja vacío, se conserva el actual.
                'codigo_instructor' => $datos['codigo_instructor'] ?: $instructor->codigo_instructor,
                'area_formacion'    => $datos['area_formacion'] ?? null,
                'tipo_docente'      => $datos['tipo_docente'] ?: null,
            ]);

            $this->sincronizarRolesAdicionales($usuario, $roles, Roles::INSTRUCTOR);
        });

        return redirect()
            ->route('coordinacion.docentes.show', $instructor->id_instructor)
            ->with('success', 'Datos del instructor actualizados correctamente.');
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

    /**
     * Activa o inactiva un instructor. Sincroniza el perfil de instructor y su
     * cuenta de usuario: un instructor inactivo no aparece en los selectores de
     * fichas ni puede iniciar sesión. No toca cuentas bloqueadas por el
     * administrador.
     */
    public function actualizarEstadoDocente(Instructor $instructor): RedirectResponse
    {
        $usuario = $instructor->usuario;

        if ($usuario && $usuario->estado_usuario === 'bloqueado') {
            return back()->withErrors(['error' => 'La cuenta está bloqueada por el administrador; no puede modificarse desde coordinación.']);
        }

        $nuevo = $instructor->estado_instructor === 'activo' ? 'inactivo' : 'activo';

        // Validación de integridad: no se puede inactivar un instructor que
        // esté vinculado a fichas en ejecución (como líder o como asignado).
        // Primero hay que retirarlo o reemplazarlo desde la sección de Fichas.
        if ($nuevo === 'inactivo') {
            $motivos = [];

            if ($instructor->fichasLideradas()->where('estado_ficha', Ficha::ESTADO_EN_EJECUCION)->exists()) {
                $motivos[] = 'es instructor líder de fichas en ejecución (asigna otro líder primero)';
            }

            if ($instructor->fichas()->where('estado_ficha', Ficha::ESTADO_EN_EJECUCION)->exists()) {
                $motivos[] = 'está asignado a fichas en ejecución (retíralo desde la sección de Fichas)';
            }

            if ($motivos !== []) {
                return back()->withErrors([
                    'error' => 'No se puede inactivar el instructor porque ' . implode(' y ', $motivos) . '.',
                ]);
            }
        }

        DB::transaction(function () use ($instructor, $usuario, $nuevo) {
            $instructor->update(['estado_instructor' => $nuevo]);
            $usuario?->update(['estado_usuario' => $nuevo]);
        });

        $nombre = $usuario ? trim($usuario->nombres . ' ' . $usuario->apellidos) : $instructor->codigo_instructor;

        return back()->with('success', $nuevo === 'activo'
            ? "Instructor {$nombre} activado correctamente."
            : "Instructor {$nombre} inactivado correctamente. No podrá iniciar sesión mientras esté inactivo.");
    }

    // La gestión de fichas (listado, CRUD, asociaciones e instructor líder) se
    // trasladó a App\Http\Controllers\FichaController.
}
