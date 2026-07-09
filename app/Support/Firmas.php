<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Usuario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Manejo de la imagen de firma manuscrita de cada usuario.
 *
 * La imagen se guarda en el disco PRIVADO (storage/app/firmas), nunca en el
 * público: solo su dueño puede verla (ruta autenticada de Mi Perfil) y el
 * sistema la incrusta en base64 dentro de los documentos de llamados de
 * atención al generarlos. Así ninguna firma queda expuesta por URL.
 */
class Firmas
{
    /** Carpeta dentro del disco privado (local) donde viven las firmas. */
    public const CARPETA = 'firmas';

    /** Extensiones de imagen admitidas (PNG con fondo transparente, idealmente). */
    private const EXTENSIONES = ['png', 'jpg', 'jpeg', 'webp'];

    /**
     * Ruta relativa (dentro del disco local) de la firma del usuario, o null
     * si no tiene firma registrada.
     */
    public static function rutaDe(Usuario $usuario): ?string
    {
        foreach (self::EXTENSIONES as $ext) {
            $ruta = self::CARPETA . '/firma_' . $usuario->id_usuario . '.' . $ext;
            if (Storage::disk('local')->exists($ruta)) {
                return $ruta;
            }
        }

        return null;
    }

    /**
     * Indica si el usuario tiene una firma registrada.
     */
    public static function tiene(Usuario $usuario): bool
    {
        return self::rutaDe($usuario) !== null;
    }

    /**
     * Guarda (o reemplaza) la firma del usuario y devuelve su ruta relativa.
     */
    public static function guardar(Usuario $usuario, UploadedFile $archivo): string
    {
        self::eliminar($usuario);

        $ext = strtolower($archivo->getClientOriginalExtension() ?: 'png');
        if (! in_array($ext, self::EXTENSIONES, true)) {
            $ext = 'png';
        }

        return $archivo->storeAs(self::CARPETA, 'firma_' . $usuario->id_usuario . '.' . $ext, 'local');
    }

    /**
     * Elimina la firma registrada del usuario (si existe).
     */
    public static function eliminar(Usuario $usuario): void
    {
        foreach (self::EXTENSIONES as $ext) {
            $ruta = self::CARPETA . '/firma_' . $usuario->id_usuario . '.' . $ext;
            if (Storage::disk('local')->exists($ruta)) {
                Storage::disk('local')->delete($ruta);
            }
        }
    }

    /**
     * La firma como data URI base64 (para incrustarla en los documentos
     * generados sin exponer ninguna URL), o null si el usuario no tiene firma.
     */
    public static function base64(Usuario $usuario): ?string
    {
        $ruta = self::rutaDe($usuario);
        if ($ruta === null) {
            return null;
        }

        $mime = match (strtolower(pathinfo($ruta, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp'        => 'image/webp',
            default       => 'image/png',
        };

        $contenido = Storage::disk('local')->get($ruta);

        return 'data:' . $mime . ';base64,' . base64_encode((string) $contenido);
    }

    /**
     * Ruta absoluta del archivo (para servirlo con response()->file en la
     * ruta autenticada del perfil), o null si no hay firma.
     */
    public static function rutaAbsoluta(Usuario $usuario): ?string
    {
        $ruta = self::rutaDe($usuario);

        return $ruta !== null ? Storage::disk('local')->path($ruta) : null;
    }
}
