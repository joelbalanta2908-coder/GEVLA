<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActaCoordinacion;
use App\Models\Aprendiz;
use App\Models\Ficha;
use App\Models\LlamadoAtencion;
use App\Models\ProcesoDisciplinario;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Exporta los reportes del coordinador (llamados, actas y procesos) a
 * PDF (vista imprimible), Excel (.xls) o Word (.doc). Implementación nativa,
 * sin librerías externas, apoyada en la vista genérica reportes.tabla.
 * Admite el filtro opcional ?ficha=ID para clasificar el reporte por ficha.
 */
class CoordinacionReporteController extends Controller
{
    /**
     * Ficha del aprendiz (matrícula activa o, en su defecto, la más reciente).
     */
    private function fichaDelAprendiz(?Aprendiz $aprendiz): ?Ficha
    {
        if (! $aprendiz) {
            return null;
        }

        $matricula = $aprendiz->matriculas->firstWhere('estado_matricula', 'activa')
            ?? $aprendiz->matriculas->sortByDesc('fecha_matricula')->first();

        return $matricula?->ficha;
    }

    /**
     * Nombre completo del instructor líder de la ficha.
     */
    private function liderDeFicha(?Ficha $ficha): string
    {
        $lider = $ficha?->instructorLider?->usuario;

        return $lider ? trim($lider->nombres . ' ' . $lider->apellidos) : 'No asignado';
    }

    /**
     * Ficha seleccionada en el filtro (?ficha=ID) o null.
     */
    private function fichaFiltro(Request $request): ?Ficha
    {
        $id = (int) $request->input('ficha', 0);

        return $id > 0 ? Ficha::find($id) : null;
    }

    /**
     * Restringe la consulta a los aprendices matriculados en la ficha dada.
     */
    private function aplicarFiltroFicha($query, ?Ficha $ficha): void
    {
        if ($ficha) {
            $query->whereHas('aprendiz.matriculas', fn ($m) => $m->where('id_ficha', $ficha->id_ficha));
        }
    }

    /**
     * Reporte individual de UN aprendiz: sus datos y todo su historial
     * (llamados, actas y procesos) en PDF imprimible, Excel o Word.
     */
    public function aprendizIndividual(string $id, string $formato): Response
    {
        $aprendiz = Aprendiz::with([
            'usuario',
            'matriculas.ficha.programa',
            'llamadosAtencion' => fn ($q) => $q->orderByDesc('fecha_llamado'),
            'actasCoordinacion' => fn ($q) => $q->orderByDesc('fecha_expedicion'),
            'procesosDisciplinarios' => fn ($q) => $q->orderByDesc('fecha_inicio'),
        ])->findOrFail($id);

        $u = $aprendiz->usuario;
        $nombre = trim(($u->nombres ?? '') . ' ' . ($u->apellidos ?? '')) ?: 'Aprendiz #' . $aprendiz->id_aprendiz;
        $ficha = $this->fichaDelAprendiz($aprendiz);

        $meta = [
            ['label' => 'Aprendiz', 'value' => $nombre],
            ['label' => 'Documento', 'value' => trim(($u->tipo_documento ?? '') . ' ' . ($u->numero_documento ?? '')) ?: '—'],
            ['label' => 'Correo', 'value' => $u->correo ?? $aprendiz->correo_institucional ?? '—'],
            ['label' => 'Ficha', 'value' => $ficha ? 'Ficha ' . $ficha->numero_ficha . ' — ' . ($ficha->programa?->nombre_programa ?? '') : 'Sin ficha activa'],
            ['label' => 'Estado académico', 'value' => ['en_formacion' => 'En formación', 'aplazado' => 'Aplazado', 'cancelado' => 'Cancelado', 'certificado' => 'Certificado'][$aprendiz->estado_academico] ?? ucfirst((string) $aprendiz->estado_academico)],
            ['label' => 'Generado', 'value' => Carbon::now('America/Bogota')->locale('es')->translatedFormat('d \d\e F \d\e Y, h:i A')],
        ];

        // Historial unificado: llamados, actas y procesos en una sola tabla.
        $filas = collect();
        foreach ($aprendiz->llamadosAtencion as $l) {
            $filas->push(['orden' => (string) $l->fecha_llamado, 'fila' => [
                'Llamado de atención',
                Carbon::parse($l->fecha_llamado)->format('d/m/Y'),
                $l->asunto,
                $l->estado_label,
            ]]);
        }
        foreach ($aprendiz->actasCoordinacion as $a) {
            $filas->push(['orden' => (string) $a->fecha_expedicion, 'fila' => [
                'Acta de coordinación',
                Carbon::parse($a->fecha_expedicion)->format('d/m/Y'),
                'Acta ' . $a->numero_acta . ' — ' . ucfirst(str_replace('_', ' ', (string) $a->tipo_acta)),
                ['expedido' => 'Expedida', 'notificado' => 'Notificada', 'firme' => 'Firmada'][$a->estado_acta] ?? ucfirst((string) $a->estado_acta),
            ]]);
        }
        foreach ($aprendiz->procesosDisciplinarios as $p) {
            $filas->push(['orden' => (string) $p->fecha_inicio, 'fila' => [
                'Proceso disciplinario',
                Carbon::parse($p->fecha_inicio)->format('d/m/Y'),
                'Etapa: ' . (['llamado_escrito' => 'Llamado escrito', 'condicionamiento' => 'Condicionamiento', 'cancelacion_matricula' => 'Cancelación de matrícula', 'finalizado' => 'Finalizado'][$p->etapa_actual] ?? ucfirst((string) $p->etapa_actual)),
                ucfirst((string) $p->estado_proceso),
            ]]);
        }
        $filas = $filas->sortByDesc('orden')->pluck('fila')->values()->all();

        $encabezados = ['Tipo de registro', 'Fecha', 'Detalle', 'Estado'];
        $titulo = 'Reporte del aprendiz: ' . $nombre;
        $slug = 'reporte-aprendiz-' . $aprendiz->id_aprendiz;
        $fecha = Carbon::now('America/Bogota')->format('Y-m-d_His');
        $data = compact('titulo', 'meta', 'encabezados', 'filas');

        if ($formato === 'pdf') {
            return response()->view('reportes.tabla', $data + ['imprimir' => true]);
        }

        if ($formato === 'excel') {
            $binario = \App\Support\ReporteExcel::generar($titulo, $meta, $encabezados, $filas);

            return response($binario, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$slug}_{$fecha}.xlsx\"",
            ]);
        }

        $html = view('reportes.tabla', $data + ['imprimir' => false])->render();

        return response($html, 200, [
            'Content-Type'        => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$slug}_{$fecha}.doc\"",
        ]);
    }

    /**
     * Reporte de UN acta de coordinación específica (PDF/Excel/Word).
     */
    public function actaIndividual(string $id, string $formato): Response
    {
        $acta = ActaCoordinacion::with([
            'aprendiz.usuario',
            'aprendiz.matriculas.ficha.programa',
            'procesoDisciplinario',
        ])->findOrFail($id);

        $u = $acta->aprendiz?->usuario;
        $nombre = trim(($u->nombres ?? '') . ' ' . ($u->apellidos ?? '')) ?: 'Aprendiz #' . $acta->id_aprendiz;
        $ficha = $this->fichaDelAprendiz($acta->aprendiz);

        $tipoLabel = [
            'acondicionamiento_academico'     => 'Acondicionamiento académico',
            'cancelacion_academica'           => 'Cancelación académica',
            'acondicionamiento_disciplinario' => 'Acondicionamiento disciplinario',
            'cancelacion_disciplinaria'       => 'Cancelación disciplinaria',
        ][$acta->tipo_acta] ?? ucfirst(str_replace('_', ' ', (string) $acta->tipo_acta));
        $estadoLabel = ['expedido' => 'Expedida', 'notificado' => 'Notificada', 'firme' => 'En firme'][$acta->estado_acta] ?? ucfirst((string) $acta->estado_acta);

        $meta = [
            ['label' => 'Número de acta', 'value' => $acta->numero_acta ?: ('#' . $acta->id_acta)],
            ['label' => 'Aprendiz', 'value' => $nombre],
            ['label' => 'Documento', 'value' => trim(($u->tipo_documento ?? '') . ' ' . ($u->numero_documento ?? '')) ?: '—'],
            ['label' => 'Ficha', 'value' => $ficha ? 'Ficha ' . $ficha->numero_ficha . ' — ' . ($ficha->programa?->nombre_programa ?? '') : 'Sin ficha activa'],
            ['label' => 'Tipo de acta', 'value' => $tipoLabel],
            ['label' => 'Estado', 'value' => $estadoLabel],
            ['label' => 'Generado', 'value' => Carbon::now('America/Bogota')->locale('es')->translatedFormat('d \d\e F \d\e Y, h:i A')],
        ];

        $encabezados = ['Campo', 'Detalle'];
        $filas = [
            ['Fecha de expedición', $acta->fecha_expedicion ? Carbon::parse($acta->fecha_expedicion)->format('d/m/Y') : '—'],
            ['Fecha de notificación personal', $acta->fecha_notificacion_personal ? Carbon::parse($acta->fecha_notificacion_personal)->format('d/m/Y') : '—'],
            ['Fecha de firmeza', $acta->fecha_firmeza ? Carbon::parse($acta->fecha_firmeza)->format('d/m/Y') : '—'],
            ['Meses de inhabilitación', $acta->meses_inhabilitacion !== null ? (string) $acta->meses_inhabilitacion : '—'],
            ['Proceso disciplinario asociado', $acta->id_proceso ? ('Proceso #' . $acta->id_proceso) : '—'],
            ['Descripción de la sanción', $acta->sancion_descripcion ?: '—'],
        ];

        return $this->responderIndividual($formato, 'Acta de coordinación ' . ($acta->numero_acta ?: ('#' . $acta->id_acta)), 'acta-' . $acta->id_acta, $meta, $encabezados, $filas);
    }

    /**
     * Reporte de UN proceso disciplinario específico con su historial (PDF/Excel/Word).
     */
    public function procesoIndividual(string $id, string $formato): Response
    {
        $proceso = ProcesoDisciplinario::with([
            'aprendiz.usuario',
            'aprendiz.matriculas.ficha.programa',
            'llamadoAtencion',
            'historial' => fn ($q) => $q->orderBy('fecha_registro'),
            'historial.usuarioRegistra',
        ])->findOrFail($id);

        $u = $proceso->aprendiz?->usuario;
        $nombre = trim(($u->nombres ?? '') . ' ' . ($u->apellidos ?? '')) ?: 'Aprendiz #' . $proceso->id_aprendiz;
        $ficha = $this->fichaDelAprendiz($proceso->aprendiz);

        $etapas = ['llamado_escrito' => 'Llamado escrito', 'acondicionamiento' => 'Condicionamiento', 'condicionamiento' => 'Condicionamiento', 'cancelacion_matricula' => 'Cancelación de matrícula', 'finalizado' => 'Finalizado'];
        $etapaLabel = $etapas[$proceso->etapa_actual] ?? ucfirst(str_replace('_', ' ', (string) $proceso->etapa_actual));
        $estadoLabel = ['activo' => 'Activo', 'cerrado' => 'Cerrado', 'anulado' => 'Anulado'][$proceso->estado_proceso] ?? ucfirst((string) $proceso->estado_proceso);

        $meta = [
            ['label' => 'Proceso', 'value' => '#' . $proceso->id_proceso],
            ['label' => 'Aprendiz', 'value' => $nombre],
            ['label' => 'Documento', 'value' => trim(($u->tipo_documento ?? '') . ' ' . ($u->numero_documento ?? '')) ?: '—'],
            ['label' => 'Ficha', 'value' => $ficha ? 'Ficha ' . $ficha->numero_ficha . ' — ' . ($ficha->programa?->nombre_programa ?? '') : 'Sin ficha activa'],
            ['label' => 'Etapa actual', 'value' => $etapaLabel],
            ['label' => 'Estado', 'value' => $estadoLabel],
            ['label' => 'Fecha de inicio', 'value' => $proceso->fecha_inicio ? Carbon::parse($proceso->fecha_inicio)->format('d/m/Y') : '—'],
            ['label' => 'Generado', 'value' => Carbon::now('America/Bogota')->locale('es')->translatedFormat('d \d\e F \d\e Y, h:i A')],
        ];

        $encabezados = ['Fecha', 'Etapa', 'Descripción', 'Resultado', 'Registrado por'];
        $filas = [];
        foreach ($proceso->historial as $h) {
            $reg = $h->usuarioRegistra;
            $filas[] = [
                $h->fecha_registro ? Carbon::parse($h->fecha_registro)->timezone('America/Bogota')->format('d/m/Y h:i A') : '—',
                $etapas[$h->etapa] ?? ucfirst(str_replace('_', ' ', (string) $h->etapa)),
                $h->descripcion ?: '—',
                $h->resultado ?: '—',
                $reg ? trim(($reg->nombres ?? '') . ' ' . ($reg->apellidos ?? '')) : '—',
            ];
        }
        if (empty($filas)) {
            $filas[] = ['—', $etapaLabel, $proceso->observaciones ?: 'Sin movimientos registrados.', '—', '—'];
        }

        return $this->responderIndividual($formato, 'Proceso disciplinario #' . $proceso->id_proceso . ' — ' . $nombre, 'proceso-' . $proceso->id_proceso, $meta, $encabezados, $filas);
    }

    /**
     * Genera la respuesta (PDF imprimible / Excel / Word) para un reporte
     * individual con su propio bloque de metadatos.
     */
    private function responderIndividual(string $formato, string $titulo, string $slug, array $meta, array $encabezados, array $filas): Response
    {
        $fecha = Carbon::now('America/Bogota')->format('Y-m-d_His');
        $data = compact('titulo', 'meta', 'encabezados', 'filas');

        if ($formato === 'pdf') {
            return response()->view('reportes.tabla', $data + ['imprimir' => true]);
        }

        if ($formato === 'excel') {
            $binario = \App\Support\ReporteExcel::generar($titulo, $meta, $encabezados, $filas);

            return response($binario, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$slug}_{$fecha}.xlsx\"",
            ]);
        }

        $html = view('reportes.tabla', $data + ['imprimir' => false])->render();

        return response($html, 200, [
            'Content-Type'        => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$slug}_{$fecha}.doc\"",
        ]);
    }

    public function llamados(Request $request, string $formato): Response
    {
        $fichaFiltro = $this->fichaFiltro($request);

        $query = LlamadoAtencion::with([
            'aprendiz.usuario',
            'aprendiz.matriculas.ficha.instructorLider.usuario',
            'instructor.usuario',
        ])->orderByDesc('fecha_llamado');

        $this->aplicarFiltroFicha($query, $fichaFiltro);
        $registros = $query->get();

        $encabezados = ['#', 'Fecha', 'Aprendiz', 'Documento', 'Ficha', 'Programa', 'Instructor líder', 'Instructor que reportó', 'Asunto', 'Estado'];
        $filas = $registros->map(function ($l) {
            $ficha = $this->fichaDelAprendiz($l->aprendiz);

            return [
                $l->id_llamado,
                $l->fecha_llamado ? Carbon::parse($l->fecha_llamado)->format('d/m/Y') : '—',
                trim(($l->aprendiz?->usuario?->nombres ?? '') . ' ' . ($l->aprendiz?->usuario?->apellidos ?? '')),
                $l->aprendiz?->usuario?->numero_documento ?? '—',
                $ficha?->numero_ficha ?? '—',
                $ficha?->programa?->nombre_programa ?? '—',
                $this->liderDeFicha($ficha),
                trim(($l->instructor?->usuario?->nombres ?? '') . ' ' . ($l->instructor?->usuario?->apellidos ?? '')) ?: 'No asignado',
                $l->asunto,
                ucfirst(str_replace('_', ' ', (string) $l->estado_llamado)),
            ];
        })->all();

        return $this->responder($formato, 'Reporte de llamados de atención', 'llamados', $encabezados, $filas, $registros->count(), $fichaFiltro);
    }

    public function actas(Request $request, string $formato): Response
    {
        $fichaFiltro = $this->fichaFiltro($request);

        $query = ActaCoordinacion::with([
            'aprendiz.usuario',
            'aprendiz.matriculas.ficha.instructorLider.usuario',
        ])->orderByDesc('fecha_expedicion');

        $this->aplicarFiltroFicha($query, $fichaFiltro);
        $registros = $query->get();

        $encabezados = ['#', 'N° Acta', 'Fecha expedición', 'Aprendiz', 'Documento', 'Ficha', 'Programa', 'Instructor líder', 'Tipo', 'Estado'];
        $filas = $registros->map(function ($a) {
            $ficha = $this->fichaDelAprendiz($a->aprendiz);

            return [
                $a->id_acta,
                $a->numero_acta ?? '—',
                $a->fecha_expedicion ? Carbon::parse($a->fecha_expedicion)->format('d/m/Y') : '—',
                trim(($a->aprendiz?->usuario?->nombres ?? '') . ' ' . ($a->aprendiz?->usuario?->apellidos ?? '')),
                $a->aprendiz?->usuario?->numero_documento ?? '—',
                $ficha?->numero_ficha ?? '—',
                $ficha?->programa?->nombre_programa ?? '—',
                $this->liderDeFicha($ficha),
                ucfirst(str_replace('_', ' ', (string) $a->tipo_acta)),
                ucfirst(str_replace('_', ' ', (string) $a->estado_acta)),
            ];
        })->all();

        return $this->responder($formato, 'Reporte de actas de coordinación', 'actas', $encabezados, $filas, $registros->count(), $fichaFiltro);
    }

    public function procesos(Request $request, string $formato): Response
    {
        $fichaFiltro = $this->fichaFiltro($request);

        $query = ProcesoDisciplinario::with([
            'aprendiz.usuario',
            'aprendiz.matriculas.ficha.instructorLider.usuario',
            'llamadoAtencion.instructor.usuario',
        ])->orderByDesc('fecha_inicio');

        $this->aplicarFiltroFicha($query, $fichaFiltro);
        $registros = $query->get();

        $encabezados = ['#', 'Fecha inicio', 'Aprendiz', 'Documento', 'Ficha', 'Programa', 'Instructor líder', 'Instructor que reportó', 'Etapa actual', 'Estado'];
        $filas = $registros->map(function ($p) {
            $ficha = $this->fichaDelAprendiz($p->aprendiz);
            $reporta = $p->llamadoAtencion?->instructor?->usuario;

            return [
                $p->id_proceso,
                $p->fecha_inicio ? Carbon::parse($p->fecha_inicio)->format('d/m/Y') : '—',
                trim(($p->aprendiz?->usuario?->nombres ?? '') . ' ' . ($p->aprendiz?->usuario?->apellidos ?? '')),
                $p->aprendiz?->usuario?->numero_documento ?? '—',
                $ficha?->numero_ficha ?? '—',
                $ficha?->programa?->nombre_programa ?? '—',
                $this->liderDeFicha($ficha),
                $reporta ? trim($reporta->nombres . ' ' . $reporta->apellidos) : '—',
                ucfirst(str_replace('_', ' ', (string) $p->etapa_actual)),
                ucfirst(str_replace('_', ' ', (string) $p->estado_proceso)),
            ];
        })->all();

        return $this->responder($formato, 'Reporte de procesos disciplinarios', 'procesos', $encabezados, $filas, $registros->count(), $fichaFiltro);
    }

    /**
     * Genera la respuesta según el formato solicitado.
     *
     * @param  array<int, string>              $encabezados
     * @param  array<int, array<int, mixed>>   $filas
     */
    private function responder(string $formato, string $titulo, string $slug, array $encabezados, array $filas, int $total, ?Ficha $fichaFiltro = null): Response
    {
        $usuario = Auth::user();
        $coordinador = trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellidos ?? ''));

        $meta = [
            ['label' => 'Coordinador', 'value' => $coordinador !== '' ? $coordinador : 'No registrado'],
            ['label' => 'Ficha', 'value' => $fichaFiltro ? 'Ficha ' . $fichaFiltro->numero_ficha : 'Todas las fichas'],
            ['label' => 'Generado', 'value' => Carbon::now('America/Bogota')->locale('es')->translatedFormat('d \d\e F \d\e Y, h:i A')],
            ['label' => 'Total de registros', 'value' => (string) $total],
        ];
        $fecha = Carbon::now('America/Bogota')->format('Y-m-d_His');
        $data = compact('titulo', 'meta', 'encabezados', 'filas');

        if ($formato === 'pdf') {
            return response()->view('reportes.tabla', $data + ['imprimir' => true]);
        }

        // Excel: archivo .xlsx REAL (Office Open XML) generado con ZipArchive:
        // icono de Excel, doble clic abre Excel y sin advertencias de formato.
        if ($formato === 'excel') {
            $binario = \App\Support\ReporteExcel::generar($titulo, $meta, $encabezados, $filas);

            return response($binario, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$slug}_{$fecha}.xlsx\"",
            ]);
        }

        $html = view('reportes.tabla', $data + ['imprimir' => false])->render();

        return response($html, 200, [
            'Content-Type'        => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$slug}_{$fecha}.doc\"",
        ]);
    }
}
