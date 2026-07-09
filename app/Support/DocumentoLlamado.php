<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\FirmaLlamado;
use App\Models\LlamadoAtencion;
use Illuminate\Http\Response;

/**
 * Genera el documento imprimible (PDF vía impresión del navegador, igual que
 * el resto de reportes del sistema) de un llamado de atención, con el diseño
 * del formato institucional F002-008-25 y las firmas que existan registradas
 * (Instructor / Coordinador / Aprendiz) incrustadas en base64.
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

        return response()->view('llamados.documento', [
            'llamado'  => $llamado,
            'ficha'    => $ficha,
            'firmas'   => $firmas,
            'imprimir' => true,
        ]);
    }
}
