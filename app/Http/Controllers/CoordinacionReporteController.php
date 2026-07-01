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
 * Exporta los reportes del coordinador (llamados, actas y procesos) a
 * PDF (vista imprimible), Excel (.xls) o Word (.doc). Implementación nativa,
 * sin librerías externas, apoyada en la vista genérica reportes.tabla.
 */
class CoordinacionReporteController extends Controller
{
    public function llamados(string $formato): Response
    {
        $registros = LlamadoAtencion::with(['aprendiz.usuario', 'instructor.usuario'])
            ->orderByDesc('fecha_llamado')
            ->get();

        $encabezados = ['#', 'Fecha', 'Aprendiz', 'Instructor', 'Asunto', 'Estado'];
        $filas = $registros->map(fn ($l) => [
            $l->id_llamado,
            $l->fecha_llamado ? Carbon::parse($l->fecha_llamado)->format('d/m/Y') : '—',
            trim(($l->aprendiz?->usuario?->nombres ?? '') . ' ' . ($l->aprendiz?->usuario?->apellidos ?? '')),
            trim(($l->instructor?->usuario?->nombres ?? '') . ' ' . ($l->instructor?->usuario?->apellidos ?? '')) ?: 'No asignado',
            $l->asunto,
            ucfirst(str_replace('_', ' ', (string) $l->estado_llamado)),
        ])->all();

        return $this->responder($formato, 'Reporte de llamados de atención', 'llamados', $encabezados, $filas, $registros->count());
    }

    public function actas(string $formato): Response
    {
        $registros = ActaCoordinacion::with('aprendiz.usuario')
            ->orderByDesc('fecha_expedicion')
            ->get();

        $encabezados = ['#', 'N° Acta', 'Fecha expedición', 'Aprendiz', 'Tipo', 'Estado'];
        $filas = $registros->map(fn ($a) => [
            $a->id_acta,
            $a->numero_acta ?? '—',
            $a->fecha_expedicion ? Carbon::parse($a->fecha_expedicion)->format('d/m/Y') : '—',
            trim(($a->aprendiz?->usuario?->nombres ?? '') . ' ' . ($a->aprendiz?->usuario?->apellidos ?? '')),
            ucfirst(str_replace('_', ' ', (string) $a->tipo_acta)),
            ucfirst(str_replace('_', ' ', (string) $a->estado_acta)),
        ])->all();

        return $this->responder($formato, 'Reporte de actas de coordinación', 'actas', $encabezados, $filas, $registros->count());
    }

    public function procesos(string $formato): Response
    {
        $registros = ProcesoDisciplinario::with('aprendiz.usuario')
            ->orderByDesc('fecha_inicio')
            ->get();

        $encabezados = ['#', 'Fecha inicio', 'Aprendiz', 'Etapa actual', 'Estado'];
        $filas = $registros->map(fn ($p) => [
            $p->id_proceso,
            $p->fecha_inicio ? Carbon::parse($p->fecha_inicio)->format('d/m/Y') : '—',
            trim(($p->aprendiz?->usuario?->nombres ?? '') . ' ' . ($p->aprendiz?->usuario?->apellidos ?? '')),
            ucfirst(str_replace('_', ' ', (string) $p->etapa_actual)),
            ucfirst(str_replace('_', ' ', (string) $p->estado_proceso)),
        ])->all();

        return $this->responder($formato, 'Reporte de procesos disciplinarios', 'procesos', $encabezados, $filas, $registros->count());
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
        $coordinador = trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellidos ?? ''));

        $meta = [
            ['label' => 'Coordinador', 'value' => $coordinador !== '' ? $coordinador : 'No registrado'],
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
