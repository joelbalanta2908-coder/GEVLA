<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActaCoordinacion;
use App\Models\Ficha;
use App\Models\LlamadoAtencion;
use App\Models\ProcesoDisciplinario;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Exporta los reportes del aprendiz (sus llamados, actas y procesos) a
 * PDF (vista imprimible), Excel (.xls) o Word (.doc). Solo incluye la
 * información del propio aprendiz autenticado, conforme a sus facultades de
 * solo lectura. Reutiliza la vista genérica reportes.tabla.
 */
class AprendizReporteController extends Controller
{
    /**
     * Devuelve el aprendiz autenticado o aborta el acceso.
     */
    private function getAprendiz()
    {
        $aprendiz = Auth::user()->aprendiz;
        if (! $aprendiz) {
            abort(403, 'Acceso denegado: El usuario no es un aprendiz.');
        }
        return $aprendiz;
    }

    /**
     * Ficha del aprendiz (matrícula activa o, en su defecto, la más reciente).
     */
    private function fichaDelAprendiz($aprendiz): ?Ficha
    {
        $aprendiz->loadMissing('matriculas.ficha.instructorLider.usuario', 'matriculas.ficha.programa');

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

    public function llamados(string $formato): Response
    {
        $aprendiz = $this->getAprendiz();
        $ficha = $this->fichaDelAprendiz($aprendiz);

        $registros = LlamadoAtencion::with('instructor.usuario')
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->orderByDesc('fecha_llamado')
            ->get();

        $encabezados = ['#', 'Fecha', 'Ficha', 'Instructor líder', 'Instructor que reportó', 'Asunto', 'Estado'];
        $filas = $registros->map(fn ($l) => [
            $l->id_llamado,
            $l->fecha_llamado ? Carbon::parse($l->fecha_llamado)->format('d/m/Y') : '—',
            $ficha?->numero_ficha ?? '—',
            $this->liderDeFicha($ficha),
            trim(($l->instructor?->usuario?->nombres ?? '') . ' ' . ($l->instructor?->usuario?->apellidos ?? '')) ?: 'No asignado',
            $l->asunto,
            ucfirst(str_replace('_', ' ', (string) $l->estado_llamado)),
        ])->all();

        return $this->responder($formato, 'Mis llamados de atención', 'mis-llamados', $encabezados, $filas, $registros->count(), $ficha);
    }

    public function actas(string $formato): Response
    {
        $aprendiz = $this->getAprendiz();
        $ficha = $this->fichaDelAprendiz($aprendiz);

        $registros = ActaCoordinacion::where('id_aprendiz', $aprendiz->id_aprendiz)
            ->orderByDesc('fecha_expedicion')
            ->get();

        $encabezados = ['#', 'N° Acta', 'Fecha expedición', 'Ficha', 'Instructor líder', 'Tipo', 'Estado'];
        $filas = $registros->map(fn ($a) => [
            $a->id_acta,
            $a->numero_acta ?? '—',
            $a->fecha_expedicion ? Carbon::parse($a->fecha_expedicion)->format('d/m/Y') : '—',
            $ficha?->numero_ficha ?? '—',
            $this->liderDeFicha($ficha),
            ucfirst(str_replace('_', ' ', (string) $a->tipo_acta)),
            ucfirst(str_replace('_', ' ', (string) $a->estado_acta)),
        ])->all();

        return $this->responder($formato, 'Mis actas de coordinación', 'mis-actas', $encabezados, $filas, $registros->count(), $ficha);
    }

    public function procesos(string $formato): Response
    {
        $aprendiz = $this->getAprendiz();
        $ficha = $this->fichaDelAprendiz($aprendiz);

        $registros = ProcesoDisciplinario::with('llamadoAtencion.instructor.usuario')
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->orderByDesc('fecha_inicio')
            ->get();

        $encabezados = ['#', 'Fecha inicio', 'Ficha', 'Instructor líder', 'Instructor que reportó', 'Etapa actual', 'Estado'];
        $filas = $registros->map(function ($p) use ($ficha) {
            $reporta = $p->llamadoAtencion?->instructor?->usuario;

            return [
                $p->id_proceso,
                $p->fecha_inicio ? Carbon::parse($p->fecha_inicio)->format('d/m/Y') : '—',
                $ficha?->numero_ficha ?? '—',
                $this->liderDeFicha($ficha),
                $reporta ? trim($reporta->nombres . ' ' . $reporta->apellidos) : '—',
                ucfirst(str_replace('_', ' ', (string) $p->etapa_actual)),
                ucfirst(str_replace('_', ' ', (string) $p->estado_proceso)),
            ];
        })->all();

        return $this->responder($formato, 'Mis procesos disciplinarios', 'mis-procesos', $encabezados, $filas, $registros->count(), $ficha);
    }

    /**
     * Genera la respuesta según el formato solicitado.
     *
     * @param  array<int, string>              $encabezados
     * @param  array<int, array<int, mixed>>   $filas
     */
    private function responder(string $formato, string $titulo, string $slug, array $encabezados, array $filas, int $total, ?Ficha $ficha = null): Response
    {
        $usuario = Auth::user();
        $aprendiz = trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellidos ?? ''));

        $meta = [
            ['label' => 'Aprendiz', 'value' => $aprendiz !== '' ? $aprendiz : 'No registrado'],
            ['label' => 'Ficha', 'value' => $ficha ? 'Ficha ' . $ficha->numero_ficha . ' — ' . ($ficha->programa?->nombre_programa ?? '') : 'Sin ficha activa'],
            ['label' => 'Instructor líder', 'value' => $this->liderDeFicha($ficha)],
            ['label' => 'Generado', 'value' => Carbon::now('America/Bogota')->locale('es')->translatedFormat('d \d\e F \d\e Y, h:i A')],
            ['label' => 'Total de registros', 'value' => (string) $total],
        ];
        $fecha = Carbon::now('America/Bogota')->format('Y-m-d_His');
        $data = compact('titulo', 'meta', 'encabezados', 'filas');

        if ($formato === 'pdf') {
            return response()->view('reportes.tabla', $data + ['imprimir' => true]);
        }

        $html = view('reportes.tabla', $data + ['imprimir' => false])->render();

        [$mime, $ext] = $formato === 'excel'
            ? ['application/vnd.ms-excel', 'xls']
            : ['application/msword', 'doc'];

        return response($html, 200, [
            'Content-Type'        => $mime . '; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$slug}_{$fecha}.{$ext}\"",
        ]);
    }
}
