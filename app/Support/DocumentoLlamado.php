<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\FirmaLlamado;
use App\Models\LlamadoAtencion;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

/**
 * Genera el documento PDF de un llamado de atención con el diseño del formato
 * institucional F002-008-25 y las firmas que existan registradas
 * (Instructor / Coordinador / Aprendiz) incrustadas en base64.
 *
 * El PDF se genera en el servidor con Dompdf (librería open source, licencia
 * MIT, sin costos por firma ni servicios externos): las firmas quedan
 * aplanadas dentro del archivo y no pueden modificarse desde el documento.
 * Si Dompdf no está instalado (falta `composer install`), se degrada a la
 * vista imprimible del navegador para no romper el flujo.
 *
 * El documento se arma SIEMPRE desde la base de datos al momento de pedirlo,
 * por lo que se "actualiza" automáticamente cada vez que alguien firma.
 */
class DocumentoLlamado
{
    /**
     * Renderiza el documento del llamado con sus firmas actuales.
     */
    public static function render(LlamadoAtencion $llamado): Response
    {
        $llamado->loadMissing([
            'aprendiz.usuario',
            'aprendiz.matriculas.ficha.programa',
            'instructor.usuario',
            'articulo',
        ]);

        // Ficha del aprendiz: la matrícula activa o, en su defecto, la más reciente.
        $matricula = $llamado->aprendiz?->matriculas?->firstWhere('estado_matricula', 'activa')
            ?? $llamado->aprendiz?->matriculas?->sortByDesc('fecha_matricula')->first();
        $ficha = $matricula?->ficha;

        // Firmas registradas + imagen actual de cada firmante (en base64, para
        // que el documento sea autocontenido y la firma no pueda editarse).
        $firmas = [];
        foreach ([FirmaLlamado::ROL_INSTRUCTOR, FirmaLlamado::ROL_COORDINADOR, FirmaLlamado::ROL_APRENDIZ] as $rol) {
            $registro = FirmaLlamado::de((int) $llamado->id_llamado, $rol);
            $registro?->loadMissing('usuario');

            $firmas[$rol] = [
                'registro' => $registro,
                'imagen'   => $registro?->usuario ? Firmas::base64($registro->usuario) : null,
            ];
        }

        $datos = [
            'llamado' => $llamado,
            'ficha'   => $ficha,
            'firmas'  => $firmas,
        ];

        // Respaldo: si Dompdf aún no está instalado en este equipo, se sirve
        // la vista imprimible (el navegador la guarda como PDF).
        if (! class_exists(Dompdf::class)) {
            return response()->view('llamados.documento', $datos + ['imprimir' => true]);
        }

        $html = view('llamados.documento', $datos + ['imprimir' => false])->render();

        $opciones = new Options();
        $opciones->set('isRemoteEnabled', false);   // solo imágenes incrustadas (base64)
        $opciones->set('defaultFont', 'DejaVu Sans'); // tildes y eñes correctas

        $dompdf = new Dompdf($opciones);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            // inline: se abre en el visor de PDF del navegador, con opción de descargar.
            'Content-Disposition' => 'inline; filename="llamado-' . $llamado->id_llamado . '-firmado.pdf"',
        ]);
    }
}
