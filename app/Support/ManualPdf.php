<?php

declare(strict_types=1);

namespace App\Support;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

/**
 * Genera los manuales del sistema (Manual de Usuario y Manual Técnico) como
 * PDF de descarga directa usando Dompdf (misma librería open source con la
 * que se genera el documento del llamado de atención).
 *
 * Rendimiento:
 *  - Las capturas se reducen y comprimen una sola vez (caché en disco) antes
 *    de incrustarlas, porque Dompdf es lento con imágenes grandes.
 *  - El PDF final se cachea con una firma de las fuentes (vistas + capturas +
 *    logo); si nada cambió, la descarga se sirve al instante desde caché.
 */
class ManualPdf
{
    /** Ancho máximo (px) al que se reducen las capturas antes de incrustarlas. */
    private const CAPTURA_ANCHO_MAX = 1200;

    /** Carpeta de caché (PDF e imágenes optimizadas). */
    private static function carpetaCache(): string
    {
        $dir = storage_path('app/manuales-cache');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir;
    }

    /**
     * Renderiza la vista Blade del manual y responde con el PDF descargable.
     * Usa caché: si las fuentes no cambiaron, sirve el PDF ya generado.
     *
     * @param string $vista   Vista Blade del manual (compatible con Dompdf).
     * @param string $archivo Nombre del archivo de descarga (sin extensión).
     * @param string $titulo  Título corto para el pie de página.
     */
    public static function descargar(string $vista, string $archivo, string $titulo): Response
    {
        // Firma de las fuentes: si cambia una vista o una captura, cambia el hash.
        $firma = self::firmaFuentes($vista);
        $pdfCache = self::carpetaCache() . DIRECTORY_SEPARATOR . $archivo . '-' . $firma . '.pdf';

        if (is_file($pdfCache)) {
            return self::respuestaPdf((string) file_get_contents($pdfCache), $archivo);
        }

        $html = view($vista)->render();

        // Sin Dompdf instalado (falta composer install) se entrega la vista
        // imprimible como respaldo para no dejar al usuario sin el manual.
        if (! class_exists(Dompdf::class)) {
            return response($html, 200, [
                'Content-Type'        => 'text/html; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$archivo}.html\"",
            ]);
        }

        $opciones = new Options();
        $opciones->set('isRemoteEnabled', false);
        $opciones->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($opciones);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Encabezado y pie de página en todas las páginas menos la portada.
        $canvas = $dompdf->getCanvas();
        $ancho = $canvas->get_width();
        $alto = $canvas->get_height();
        $fuente = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $gris = [0.45, 0.49, 0.45];
        $verde = [0.22, 0.66, 0.0];

        $canvas->page_script(function ($pagina, $total, $canvas, $fontMetrics) use ($ancho, $alto, $fuente, $gris, $verde, $titulo) {
            if ($pagina === 1) {
                return; // la portada va limpia
            }

            $canvas->text(48, 24, 'GEVLA · ' . $titulo, $fuente, 7.5, $gris);
            $canvas->line(48, 38, $ancho - 48, 38, $verde, 0.8);

            $canvas->line(48, $alto - 40, $ancho - 48, $alto - 40, [0.85, 0.89, 0.84], 0.8);
            $canvas->text(48, $alto - 32, 'Servicio Nacional de Aprendizaje — SENA', $fuente, 7.5, $gris);
            $textoPagina = 'Página ' . $pagina . ' de ' . $total;
            $anchoTexto = $fontMetrics->getTextWidth($textoPagina, $fuente, 7.5);
            $canvas->text($ancho - 48 - $anchoTexto, $alto - 32, $textoPagina, $fuente, 7.5, $gris);
        });

        $pdf = (string) $dompdf->output();

        // Guardar en caché para las próximas descargas.
        @file_put_contents($pdfCache, $pdf);
        self::limpiarCacheViejo($archivo, $pdfCache);

        return self::respuestaPdf($pdf, $archivo);
    }

    private static function respuestaPdf(string $pdf, string $archivo): Response
    {
        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$archivo}.pdf\"",
        ]);
    }

    /**
     * Firma (hash) de las fuentes del manual: nombre + fecha de modificación de
     * la vista, sus parciales, la carpeta de capturas y el logo. Si algo cambia,
     * el hash cambia y se regenera el PDF.
     */
    private static function firmaFuentes(string $vista): string
    {
        $partes = ['v3'];

        foreach ([
            resource_path('views/' . str_replace('.', '/', $vista) . '.blade.php'),
            resource_path('views/manuales/_estilos.blade.php'),
            resource_path('views/manuales/_captura.blade.php'),
            public_path('img/logo-sena-verde.png'),
        ] as $f) {
            $partes[] = is_file($f) ? $f . filemtime($f) : $f . '0';
        }

        foreach ((array) glob(public_path('manuales/capturas') . DIRECTORY_SEPARATOR . '*') as $captura) {
            $partes[] = basename($captura) . filemtime($captura) . filesize($captura);
        }

        return substr(md5(implode('|', $partes)), 0, 16);
    }

    /**
     * Borra versiones antiguas cacheadas del mismo manual (deja solo la vigente).
     */
    private static function limpiarCacheViejo(string $archivo, string $vigente): void
    {
        foreach ((array) glob(self::carpetaCache() . DIRECTORY_SEPARATOR . $archivo . '-*.pdf') as $f) {
            if ($f !== $vigente) {
                @unlink($f);
            }
        }
    }

    /**
     * Imagen local codificada en base64 para incrustarla en el PDF
     * (Dompdf trabaja sin acceso remoto). Devuelve null si no existe.
     */
    public static function imagen(string $rutaRelativaPublic): ?string
    {
        $ruta = public_path($rutaRelativaPublic);
        if (! is_file($ruta)) {
            return null;
        }

        $extension = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        $mime = $extension === 'jpg' || $extension === 'jpeg' ? 'image/jpeg' : 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($ruta));
    }

    /**
     * Captura de pantalla del manual: se buscan en public/manuales/capturas.
     * Si el archivo aún no se ha aportado, devuelve null y la vista
     * simplemente no muestra la imagen (nunca se inventan capturas).
     *
     * La imagen se reduce y comprime (caché en disco) para que Dompdf no tenga
     * que procesar archivos pesados en cada descarga. Es tolerante con la
     * extensión: encuentra "01-login.png", "01-login.png.png" o "01-login.jpg".
     */
    public static function captura(string $nombreArchivo): ?string
    {
        $ruta = self::rutaCaptura($nombreArchivo);

        return $ruta !== null ? self::imagenOptimizada($ruta) : null;
    }

    /**
     * Ubica el archivo real de una captura, tolerando doble extensión y jpg/png.
     */
    private static function rutaCaptura(string $nombreArchivo): ?string
    {
        $carpeta = public_path('manuales/capturas');

        $exacta = $carpeta . DIRECTORY_SEPARATOR . $nombreArchivo;
        if (is_file($exacta)) {
            return $exacta;
        }

        $base = pathinfo($nombreArchivo, PATHINFO_FILENAME);
        foreach ((array) glob($carpeta . DIRECTORY_SEPARATOR . $base . '.*') as $ruta) {
            if (in_array(strtolower(pathinfo($ruta, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg'], true)) {
                return $ruta;
            }
        }

        return null;
    }

    /**
     * Devuelve la captura como data URI, reducida a un ancho máximo y comprimida
     * en JPEG (sobre fondo blanco). El resultado se cachea por archivo para no
     * reprocesar en cada descarga. Si GD no está disponible, usa el original.
     */
    private static function imagenOptimizada(string $ruta): string
    {
        $clave = md5($ruta . filemtime($ruta) . filesize($ruta) . 'a' . self::CAPTURA_ANCHO_MAX);
        $cache = self::carpetaCache() . DIRECTORY_SEPARATOR . 'img-' . $clave . '.jpg';

        if (is_file($cache)) {
            return 'data:image/jpeg;base64,' . base64_encode((string) file_get_contents($cache));
        }

        if (! function_exists('imagecreatefromstring')) {
            return self::imagenDirecta($ruta); // sin GD: original
        }

        $img = @imagecreatefromstring((string) file_get_contents($ruta));
        if ($img === false) {
            return self::imagenDirecta($ruta);
        }

        $ancho = imagesx($img);
        $alto = imagesy($img);
        $nuevoAncho = min($ancho, self::CAPTURA_ANCHO_MAX);
        $nuevoAlto = (int) max(1, round($alto * $nuevoAncho / $ancho));

        // Lienzo con fondo blanco (aplana transparencias de PNG).
        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        $blanco = imagecolorallocate($destino, 255, 255, 255);
        imagefilledrectangle($destino, 0, 0, $nuevoAncho, $nuevoAlto, $blanco);
        imagecopyresampled($destino, $img, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
        imagedestroy($img);

        ob_start();
        imagejpeg($destino, null, 82);
        $jpeg = (string) ob_get_clean();
        imagedestroy($destino);

        @file_put_contents($cache, $jpeg);

        return 'data:image/jpeg;base64,' . base64_encode($jpeg);
    }

    private static function imagenDirecta(string $ruta): string
    {
        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        $mime = $ext === 'jpg' || $ext === 'jpeg' ? 'image/jpeg' : 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($ruta));
    }
}
