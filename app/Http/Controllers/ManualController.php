<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\ManualPdf;
use App\Support\Roles;
use Illuminate\Http\Response;

/**
 * Descarga de los manuales del sistema desde Soporte y Ayuda:
 *
 *  · Manual de Usuario: disponible para Aprendiz, Instructor y Coordinador.
 *  · Manual Técnico: solo Instructor y Coordinador (nunca Aprendiz).
 *
 * Ambos se generan como PDF de descarga directa con Dompdf.
 */
class ManualController extends Controller
{
    public function usuario(): Response
    {
        return ManualPdf::descargar(
            'manuales.usuario',
            'GEVLA-manual-de-usuario',
            'Manual de Usuario'
        );
    }

    public function tecnico(): Response
    {
        // El manual técnico no está disponible para el rol Aprendiz.
        $rolActivo = session('rol_activo');
        abort_unless(
            in_array($rolActivo, [Roles::INSTRUCTOR, Roles::COORDINADOR], true),
            403,
            'El manual técnico solo está disponible para instructores y coordinadores.'
        );

        return ManualPdf::descargar(
            'manuales.tecnico',
            'GEVLA-manual-tecnico',
            'Manual Técnico'
        );
    }
}
