<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Manejo del campo "pruebas aportadas" de un llamado de atención, que ahora
 * puede contener una descripción de texto y/o varias fotos de evidencia.
 *
 * IMPORTANTE (sin cambios en la base de datos): las fotos se guardan como
 * archivos en storage/app/public y sus rutas se serializan como JSON dentro de
 * la MISMA columna de texto `pruebas_aportadas`. Los llamados antiguos, que
 * guardan solo texto plano, se siguen leyendo igual (parse() los detecta y los
 * devuelve como "texto" sin fotos), así que no se rompe nada existente.
 *
 * Formato nuevo almacenado en la columna:
 *   {"texto": "Descripción...", "fotos": ["pruebas_llamados/ab12.jpg", ...]}
 */
class PruebasLlamado
{
    /** Carpeta dentro del disco "public" donde se guardan las evidencias. */
    public const CARPETA = 'pruebas_llamados';

    /**
     * Interpreta el valor almacenado y devuelve siempre la misma estructura,
     * sea texto plano antiguo o el JSON nuevo con fotos.
     *
     * @return array{texto: string, fotos: array<int, string>}
     */
    public static function parse(?string $valor): array
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return ['texto' => '', 'fotos' => []];
        }

        // Solo intentamos JSON si parece un objeto; así el texto plano que
        // casualmente empiece por "{" pero no sea válido cae al modo texto.
        if (str_starts_with($valor, '{')) {
            $data = json_decode($valor, true);
            if (is_array($data) && (array_key_exists('texto', $data) || array_key_exists('fotos', $data))) {
                return [
                    'texto' => trim((string) ($data['texto'] ?? '')),
                    'fotos' => array_values(array_filter(array_map('strval', (array) ($data['fotos'] ?? [])))),
                ];
            }
        }

        // Llamado antiguo: solo texto.
        return ['texto' => $valor, 'fotos' => []];
    }

    /**
     * Construye el valor a guardar en la columna. Si no hay fotos, se guarda el
     * texto plano tal cual (o null), para no cambiar el comportamiento de los
     * llamados que solo tienen descripción. Si hay fotos, se guarda el JSON.
     *
     * @param  array<int, string>  $fotos
     */
    public static function construir(?string $texto, array $fotos): ?string
    {
        $texto = trim((string) $texto);
        $fotos = array_values(array_filter($fotos));

        if ($fotos === []) {
            return $texto === '' ? null : $texto;
        }

        return json_encode(
            ['texto' => $texto, 'fotos' => $fotos],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: $texto;
    }

    /**
     * Procesa la petición (creación o edición) y devuelve el valor final para
     * la columna `pruebas_aportadas`: conserva las fotos existentes menos las
     * marcadas para eliminar, agrega las nuevas subidas y toma el texto.
     */
    public static function desdeRequest(Request $request, ?string $existente = null): ?string
    {
        $actual = self::parse($existente);
        $fotos  = $actual['fotos'];

        // Fotos existentes marcadas para eliminar (solo en edición).
        $eliminar = array_map('strval', (array) $request->input('pruebas_fotos_eliminar', []));
        if ($eliminar !== []) {
            $fotos = array_values(array_diff($fotos, $eliminar));
            self::eliminarArchivos($eliminar);
        }

        // Fotos nuevas subidas en este envío.
        if ($request->hasFile('pruebas_fotos')) {
            $fotos = array_merge($fotos, self::guardarArchivos((array) $request->file('pruebas_fotos')));
        }

        return self::construir($request->input('pruebas_aportadas'), $fotos);
    }

    /**
     * Guarda los archivos subidos en el disco público y devuelve sus rutas
     * relativas (p. ej. "pruebas_llamados/ab12.jpg").
     *
     * @param  array<int, UploadedFile|null>  $archivos
     * @return array<int, string>
     */
    public static function guardarArchivos(array $archivos): array
    {
        $rutas = [];
        foreach ($archivos as $archivo) {
            if ($archivo instanceof UploadedFile && $archivo->isValid()) {
                $rutas[] = $archivo->store(self::CARPETA, 'public');
            }
        }

        return $rutas;
    }

    /**
     * Elimina del disco público las fotos indicadas (por su ruta relativa).
     *
     * @param  array<int, string>  $rutas
     */
    public static function eliminarArchivos(array $rutas): void
    {
        foreach ($rutas as $ruta) {
            $ruta = trim((string) $ruta);
            if ($ruta !== '') {
                Storage::disk('public')->delete($ruta);
            }
        }
    }
}
