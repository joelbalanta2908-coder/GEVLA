<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Support\ImportadorUsuarios;
use App\Support\PlantillaImportacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

/**
 * Carga masiva de usuarios mediante Excel.
 *
 * Permisos (validados por las rutas y de nuevo aquí):
 *  · Coordinador: aprendices, instructores y coordinadores.
 *  · Instructor: SOLO aprendices, y únicamente en sus propias fichas.
 *
 * Cada importación queda auditada en storage/logs/importaciones.log (usuario,
 * rol, fecha/hora, IP, tipo, cantidad y resultado) sin tocar la base de datos.
 */
class ImportacionController extends Controller
{
    private const TIPOS = ['aprendices', 'instructores', 'coordinadores'];

    /*
    |--------------------------------------------------------------------------
    | Coordinador
    |--------------------------------------------------------------------------
    */

    /**
     * Descarga la plantilla Excel del tipo indicado.
     */
    public function plantilla(string $tipo): Response
    {
        abort_unless(in_array($tipo, self::TIPOS, true), 404);

        return response(PlantillaImportacion::generar($tipo), 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="plantilla-carga-masiva-' . $tipo . '.xlsx"',
        ]);
    }

    /**
     * Importa el Excel del tipo indicado (coordinador).
     */
    public function importar(Request $request, string $tipo): RedirectResponse
    {
        abort_unless(in_array($tipo, self::TIPOS, true), 404);

        return $this->procesar($request, $tipo, null);
    }

    /*
    |--------------------------------------------------------------------------
    | Instructor (solo aprendices, solo en sus fichas)
    |--------------------------------------------------------------------------
    */

    public function plantillaInstructor(): Response
    {
        $instructor = $this->getInstructor();
        $fichas = $this->fichasActivasDelInstructor($instructor);

        return response(PlantillaImportacion::generar('aprendices', $fichas), 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="plantilla-carga-masiva-aprendices.xlsx"',
        ]);
    }

    public function importarInstructor(Request $request): RedirectResponse
    {
        $instructor = $this->getInstructor();
        $fichasPermitidas = $this->fichasActivasDelInstructor($instructor)
            ->pluck('id_ficha')->map(fn ($id) => (int) $id)->all();

        return $this->procesar($request, 'aprendices', $fichasPermitidas);
    }

    /*
    |--------------------------------------------------------------------------
    | Núcleo compartido
    |--------------------------------------------------------------------------
    */

    /**
     * Valida el archivo, ejecuta la importación (todo o nada) y deja auditoría.
     *
     * @param  array<int, int>|null  $fichasPermitidas
     */
    private function procesar(Request $request, string $tipo, ?array $fichasPermitidas): RedirectResponse
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [
            'archivo.required' => 'Selecciona el archivo Excel que quieres importar.',
            'archivo.mimes'    => 'Solo se permiten archivos de Excel (.xlsx o .xls).',
            'archivo.max'      => 'El archivo no puede superar los 5 MB.',
        ]);

        $resultado = (new ImportadorUsuarios())->importar($tipo, $request->file('archivo'), $fichasPermitidas);

        $this->auditar($request, $tipo, $resultado);

        if (! $resultado['exito']) {
            return back()
                ->with('import_fallo', 'No fue posible realizar la carga masiva: se encontraron errores en el archivo. No se registró ningún usuario. Corrige los errores e intenta nuevamente.')
                ->with('import_errores', $resultado['errores']);
        }

        return back()->with('success',
            'Carga masiva realizada correctamente. Se registraron ' . $resultado['creados'] . ' ' . $tipo . '. No se encontraron errores.');
    }

    /**
     * Auditoría de la importación (archivo de log, sin tocar la base de datos).
     *
     * @param  array{exito: bool, creados: int, errores: array}  $resultado
     */
    private function auditar(Request $request, string $tipo, array $resultado): void
    {
        $usuario = Auth::user();

        $linea = json_encode([
            'fecha'      => now()->toDateTimeString(),
            'usuario'    => trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellidos ?? '')),
            'id_usuario' => $usuario->id_usuario ?? null,
            'rol'        => session('rol_activo') ?? 'desconocido',
            'ip'         => $request->ip(),
            'tipo'       => $tipo,
            'registros'  => $resultado['exito'] ? $resultado['creados'] : count($resultado['errores']),
            'resultado'  => $resultado['exito']
                ? 'EXITOSA: ' . $resultado['creados'] . ' usuarios registrados'
                : 'FALLIDA: ' . count($resultado['errores']) . ' errores, ningún usuario registrado',
        ], JSON_UNESCAPED_UNICODE);

        File::append(storage_path('logs/importaciones.log'), $linea . PHP_EOL);
    }

    private function getInstructor(): Instructor
    {
        $instructor = Auth::user()->instructor;
        if (! $instructor) {
            abort(403, 'Acceso denegado: El usuario no es un instructor.');
        }

        return $instructor;
    }

    /**
     * Fichas ACTIVAS donde el instructor imparte clases (asociado o líder).
     */
    private function fichasActivasDelInstructor(Instructor $instructor)
    {
        return $instructor->fichas()->where('estado_ficha', 'en_ejecucion')->get()
            ->merge($instructor->fichasLideradas()->where('estado_ficha', 'en_ejecucion')->get())
            ->unique('id_ficha')
            ->sortBy('numero_ficha')
            ->values();
    }
}
