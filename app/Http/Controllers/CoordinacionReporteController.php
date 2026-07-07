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
