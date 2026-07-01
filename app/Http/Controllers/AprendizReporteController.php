<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActaCoordinacion;
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

    public function llamados(string $formato): Response
    {
        $aprendiz = $this->getAprendiz();

        $registros = LlamadoAtencion::with('instructor.usuario')
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->orderByDesc('fecha_llamado')
            ->get();

        $encabezados = ['#', 'Fecha', 'Instructor', 'Asunto', 'Estado'];
        $filas = $registros->map(fn ($l) => [
            $l->id_llamado,
            $l->fecha_llamado ? Carbon::parse($l->fecha_llamado)->format('d/m/Y') : '—',
            trim(($l->instructor?->usuario?->nombres ?? '') . ' ' . ($l->instructor?->usuario?->apellidos ?? '')) ?: 'No asignado',
            $l->asunto,
            ucfirst(str_replace('_', ' ', (string) $l->estado_llamado)),
        ])->all();

        return $this->responder($formato, 'Mis llamados de atención', 'mis-llamados', $encabezados, $filas, $registros->count());
    }

    public function actas(string $formato): Response
    {
        $aprendiz = $this->getAprendiz();

        $registros = ActaCoordinacion::where('id_aprendiz', $aprendiz->id_aprendiz)
            ->orderByDesc('fecha_expedicion')
            ->get();

        $encabezados = ['#', 'N° Acta', 'Fecha expedición', 'Tipo', 'Estado'];
        $filas = $registros->map(fn ($a) => [
            $a->id_acta,
            $a->numero_acta ?? '—',
            $a->fecha_expedicion ? Carbon::parse($a->fecha_expedicion)->format('d/m/Y') : '—',
            ucfirst(str_replace('_', ' ', (string) $a->tipo_acta)),
            ucfirst(str_replace('_', ' ', (string) $a->estado_acta)),
        ])->all();

        return $this->responder($formato, 'Mis actas de coordinación', 'mis-actas', $encabezados, $filas, $registros->count());
    }

    public function procesos(string $formato): Response
    {
        $aprendiz = $this->getAprendiz();

        $registros = ProcesoDisciplinario::where('id_aprendiz', $aprendiz->id_aprendiz)
            ->orderByDesc('fecha_inicio')
            ->get();

        $encabezados = ['#', 'Fecha inicio', 'Etapa actual', 'Estado'];
        $filas = $registros->map(fn ($p) => [
            $p->id_proceso,
            $p->fecha_inicio ? Carbon::parse($p->fecha_inicio)->format('d/m/Y') : '—',
            ucfirst(str_replace('_', ' ', (string) $p->etapa_actual)),
            ucfirst(str_replace('_', ' ', (string) $p->estado_proceso)),
        ])->all();

        return $this->responder($formato, 'Mis procesos disciplinarios', 'mis-procesos', $encabezados, $filas, $registros->count());
    }

    /**
     * Genera la respuesta según el formato solicitado.
     *
     * @param  array<int, string>              $encabezados
     * @param  array<int, array<int, mixed>>   $filas
     */
    private function responder(string $formato, string $titulo, string $slug, array $encabezados, array $filas, int $total): Response
    {
        $usuario = Auth::user();
        $aprendiz = trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellidos ?? ''));

        $meta = [
            ['label' => 'Aprendiz', 'value' => $aprendiz !== '' ? $aprendiz : 'No registrado'],
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
