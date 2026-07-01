<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LlamadoAtencion;
use App\Models\Aprendiz;
use App\Models\ProgramaFormacion;
use App\Models\ReglamentoArticulo;
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
     * Artículos del reglamento agrupados por calificación de falta,
     * en formato apto para los <select> dependientes del formulario.
     *
     * @return array<string, array<int, array{id:int, texto:string}>>
     */
    private function articulosPorCalificacion(): array
    {
        $grupos = array_fill_keys(array_keys(LlamadoAtencion::calificaciones()), []);

        ReglamentoArticulo::whereNotNull('calificacion')
            ->orderBy('id_articulo')
            ->get()
            ->each(function (ReglamentoArticulo $articulo) use (&$grupos) {
                $grupos[$articulo->calificacion][] = [
                    'id'    => $articulo->id_articulo,
                    'texto' => trim($articulo->numero_articulo . ' — ' . $articulo->titulo),
                ];
            });

        return $grupos;
    }

    /**
     * Columnas por las que se permite ordenar el listado de reportes.
     *
     * @var array<string, string>
     */
    private const COLUMNAS_ORDEN = [
        'id'     => 'id_llamado',
        'fecha'  => 'fecha_llamado',
        'asunto' => 'asunto',
        'estado' => 'estado_llamado',
    ];

    /**
     * Lista los llamados de atención creados por el instructor actual con un
     * buscador avanzado: filtros combinables (número de ficha, nombre/documento
     * del aprendiz, programa, estado, tipo de reporte y rango de fechas),
     * ordenamiento por columnas y paginación.
     */
    public function index(Request $request): View
    {
        $instructor = $this->getInstructor();

        // Valores de los filtros.
        $buscar       = trim((string) $request->input('buscar', ''));
        $numeroFicha  = trim((string) $request->input('numero_ficha', ''));
        $idPrograma   = $request->input('id_programa');
        $estado       = $request->input('estado');
        $tipo         = $request->input('tipo_llamado');
        $fechaDesde   = $request->input('fecha_desde');
        $fechaHasta   = $request->input('fecha_hasta');

        // Ordenamiento (con lista blanca para evitar inyección).
        $orden = (string) $request->input('orden', 'fecha');
        $dir   = strtolower((string) $request->input('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $columnaOrden = self::COLUMNAS_ORDEN[$orden] ?? 'fecha_llamado';

        $llamados = LlamadoAtencion::query()
            ->with(['aprendiz.usuario', 'aprendiz.matriculas.ficha.programa'])
            ->where('id_instructor', $instructor->id_instructor)
            // Búsqueda por nombre/apellido/documento del aprendiz o asunto.
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($sub) use ($buscar) {
                    $sub->where('asunto', 'like', "%{$buscar}%")
                        ->orWhereHas('aprendiz.usuario', function ($u) use ($buscar) {
                            $u->where('nombres', 'like', "%{$buscar}%")
                                ->orWhere('apellidos', 'like', "%{$buscar}%")
                                ->orWhere('numero_documento', 'like', "%{$buscar}%");
                        });
                });
            })
            // Filtro por número de ficha del aprendiz.
            ->when($numeroFicha !== '', function ($q) use ($numeroFicha) {
                $q->whereHas('aprendiz.matriculas.ficha', fn ($f) => $f->where('numero_ficha', 'like', "%{$numeroFicha}%"));
            })
            // Filtro por programa de formación.
            ->when($idPrograma, function ($q) use ($idPrograma) {
                $q->whereHas('aprendiz.matriculas.ficha', fn ($f) => $f->where('id_programa', $idPrograma));
            })
            ->when($estado, fn ($q) => $q->where('estado_llamado', $estado))
            ->when($tipo, fn ($q) => $q->where('tipo_llamado', $tipo))
            ->when($fechaDesde, fn ($q) => $q->whereDate('fecha_llamado', '>=', $fechaDesde))
            ->when($fechaHasta, fn ($q) => $q->whereDate('fecha_llamado', '<=', $fechaHasta))
            ->orderBy($columnaOrden, $dir)
            ->paginate(15)
            ->withQueryString();

        $programas = ProgramaFormacion::orderBy('nombre_programa')->get();
        $estados   = LlamadoAtencion::estados();
        $tipos     = LlamadoAtencion::tipos();

        return view('instructor.llamados.index', compact(
            'llamados', 'programas', 'estados', 'tipos',
            'buscar', 'numeroFicha', 'idPrograma', 'estado', 'tipo', 'fechaDesde', 'fechaHasta',
            'orden', 'dir'
        ));
    }

    /**
     * Exporta los llamados del instructor a PDF (vista imprimible), Excel (.xls)
     * o Word (.doc). Implementación nativa, sin librerías externas.
     */
    public function export(string $formato): \Illuminate\Http\Response
    {
        $instructor = $this->getInstructor();

        $llamados = LlamadoAtencion::with('aprendiz.usuario')
            ->where('id_instructor', $instructor->id_instructor)
            ->orderByDesc('fecha_llamado')
            ->get();

        $usuario = Auth::user();
        $nombreInstructor = trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellidos ?? ''));
        $fecha = now()->timezone('America/Bogota')->format('Y-m-d_His');

        // PDF: se sirve una vista imprimible; el navegador la guarda como PDF.
        if ($formato === 'pdf') {
            return response()->view('instructor.llamados.reporte', [
                'llamados'         => $llamados,
                'nombreInstructor' => $nombreInstructor,
                'imprimir'         => true,
            ]);
        }

        // Excel y Word comparten el HTML del reporte; solo cambian las cabeceras.
        $html = view('instructor.llamados.reporte', [
            'llamados'         => $llamados,
            'nombreInstructor' => $nombreInstructor,
            'imprimir'         => false,
        ])->render();

        [$mime, $ext] = $formato === 'excel'
            ? ['application/vnd.ms-excel', 'xls']
            : ['application/msword', 'doc'];

        return response($html, 200, [
            'Content-Type'        => $mime . '; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"mis-reportes_{$fecha}.{$ext}\"",
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo llamado de atención.
     */
    public function create(): View
    {
        $this->getInstructor(); // Validar acceso
        $aprendices = Aprendiz::with('usuario')->get();
        $calificaciones = LlamadoAtencion::calificaciones();
        $articulos = $this->articulosPorCalificacion();

        return view('instructor.llamados.create', compact('aprendices', 'calificaciones', 'articulos'));
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
            'asunto'             => ['required', 'string', 'max:200'],
            'descripcion_hechos' => ['required', 'string'],
            'pruebas_aportadas'  => ['nullable', 'string'],
            'tipo_llamado'       => ['required', Rule::in(array_keys(LlamadoAtencion::tipos()))],
            'categoria'          => ['required', Rule::in(array_keys(LlamadoAtencion::categorias()))],
            'calificacion_falta' => ['required', Rule::in(array_keys(LlamadoAtencion::calificaciones()))],
            'id_articulo'        => ['required', 'integer', Rule::exists('reglamento_articulo', 'id_articulo')->where('calificacion', $request->input('calificacion_falta'))],
        ]);

        // Regla del reglamento (Art. 46): máximo 2 llamados de atención por categoría.
        if (! LlamadoAtencion::puedeRegistrarseNuevoLlamado((int) $validated['id_aprendiz'], $validated['categoria'])) {
            return back()->withInput()->withErrors([
                'id_aprendiz' => 'Este aprendiz ya tiene los ' . LlamadoAtencion::MAX_LLAMADOS_REGLAMENTARIOS
                    . ' llamados de atención ' . LlamadoAtencion::categorias()[$validated['categoria']]
                    . 's permitidos por el reglamento (Art. 46). Procede un plan de mejoramiento.',
            ]);
        }

        $validated['id_instructor'] = $instructor->id_instructor;
        $validated['id_usuario_reporta'] = Auth::id();
        $validated['estado_llamado'] = LlamadoAtencion::ESTADO_REGISTRADO; // Estado inicial

        $llamado = LlamadoAtencion::create($validated);

        $mensaje = 'Llamado de atención reportado correctamente.';
        if ($llamado->requiereAcompanamiento()) {
            $mensaje .= ' Es el segundo llamado del aprendiz en esta categoría: según el Art. 46 del reglamento'
                . ' debe acompañarse de orientaciones académicas o recomendaciones de mejoramiento disciplinario.';
        }

        return redirect()
            ->route('instructor.llamados.index')
            ->with('success', $mensaje);
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
            'faltas',
            'articulo',
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
        $calificaciones = LlamadoAtencion::calificaciones();
        $articulos = $this->articulosPorCalificacion();

        return view('instructor.llamados.edit', compact('llamadoModel', 'aprendices', 'calificaciones', 'articulos'));
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
            'asunto'             => ['required', 'string', 'max:200'],
            'descripcion_hechos' => ['required', 'string'],
            'pruebas_aportadas'  => ['nullable', 'string'],
            'tipo_llamado'       => ['required', Rule::in(array_keys(LlamadoAtencion::tipos()))],
            'categoria'          => ['required', Rule::in(array_keys(LlamadoAtencion::categorias()))],
            'calificacion_falta' => ['required', Rule::in(array_keys(LlamadoAtencion::calificaciones()))],
            'id_articulo'        => ['required', 'integer', Rule::exists('reglamento_articulo', 'id_articulo')->where('calificacion', $request->input('calificacion_falta'))],
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
