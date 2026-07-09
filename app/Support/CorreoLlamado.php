<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\LlamadoAtencion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Envío del correo de notificación al aprendiz cuando se le registra un
 * llamado de atención. Reutiliza exactamente la misma configuración SMTP del
 * .env que CorreoRecuperacion (PHPMailer), incluido el modo de prueba "log".
 *
 * IMPORTANTE: este correo se envía ÚNICAMENTE en el momento de crear un
 * llamado nuevo (desde los store() de instructor y coordinación). No existe
 * ningún proceso que reenvíe correos de llamados antiguos: los registros
 * anteriores pertenecen a correos reales de personas ajenas y no deben
 * recibir mensajes automáticos.
 *
 * El envío es "best-effort": si el SMTP falla, se registra en el log pero NO
 * se interrumpe la creación del llamado (la operación principal debe
 * completarse igual). Por eso enviar() nunca lanza excepciones.
 */
class CorreoLlamado
{
    /**
     * Envía al aprendiz el correo con el detalle del llamado de atención.
     * Devuelve true si el correo se envió (o se registró en modo log), y
     * false si no se pudo enviar o no había un correo de destino válido.
     */
    public static function enviar(LlamadoAtencion $llamado): bool
    {
        try {
            // Se cargan las relaciones necesarias sin asumir que ya vienen
            // cargadas desde el controlador.
            $llamado->loadMissing(['aprendiz.usuario', 'articulo']);

            $aprendiz = $llamado->aprendiz;
            $usuario  = $aprendiz?->usuario;

            $destinos = self::correosDestino($llamado);
            if ($destinos === []) {
                Log::warning("[LLAMADO] Sin correo de destino para el llamado #{$llamado->id_llamado}; no se envía notificación.");

                return false;
            }

            $nombre = trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellidos ?? '')) ?: 'Aprendiz';

            // Modo de prueba sin SMTP configurado: se registra en el log en
            // lugar de enviar por internet (igual que CorreoRecuperacion).
            if (config('mail.default') === 'log') {
                Log::info("[LLAMADO] Notificación para {$nombre} <" . implode(', ', $destinos) . "> — llamado #{$llamado->id_llamado}: {$llamado->asunto}");

                return true;
            }

            $smtp   = (array) config('mail.mailers.smtp');
            $puerto = (int) ($smtp['port'] ?? 587);

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = (string) ($smtp['host'] ?? 'smtp.gmail.com');
            $mail->Port       = $puerto;
            $mail->SMTPAuth   = true;
            $mail->Username   = (string) ($smtp['username'] ?? '');
            $mail->Password   = (string) ($smtp['password'] ?? '');
            // 465 = TLS implícito (SMTPS); 587 = STARTTLS.
            $mail->SMTPSecure = $puerto === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet    = PHPMailer::CHARSET_UTF8;
            $mail->SMTPDebug  = SMTP::DEBUG_OFF;

            // Redes con inspección TLS (antivirus/proxy) reemplazan el
            // certificado del servidor y OpenSSL no puede validarlo. Con
            // MAIL_VERIFICAR_TLS=false en el .env se relaja la verificación.
            if (! filter_var($smtp['verificar_tls'] ?? true, FILTER_VALIDATE_BOOL)) {
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ],
                ];
            }

            $mail->setFrom((string) config('mail.from.address'), (string) config('mail.from.name'));
            foreach ($destinos as $destino) {
                $mail->addAddress($destino, $nombre);
            }

            $mail->isHTML(true);
            $mail->Subject = 'GEVLA - Llamado de atención registrado';
            $mail->Body    = self::plantilla($llamado, $nombre);
            $mail->AltBody = self::textoPlano($llamado, $nombre);

            // Adjunta las fotos de evidencia (si las hay) desde el disco público.
            foreach ($llamado->pruebas_fotos as $foto) {
                $ruta = storage_path('app/public/' . $foto);
                if (is_file($ruta)) {
                    $mail->addAttachment($ruta);
                }
            }

            $mail->send();

            return true;
        } catch (\Throwable $e) {
            // El correo es secundario: si falla, la creación del llamado sigue
            // siendo válida. Solo se deja constancia en el log.
            Log::error("[LLAMADO] No se pudo enviar la notificación del llamado #{$llamado->id_llamado}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Notifica por correo al APRENDIZ y al INSTRUCTOR el nuevo estado del
     * llamado de atención (registrado, en revisión, notificado, cerrado o
     * cancelado). Best-effort: si el envío falla, la actualización del estado
     * sigue siendo válida y solo queda constancia en el log.
     */
    public static function notificarEstado(LlamadoAtencion $llamado): bool
    {
        try {
            $llamado->loadMissing(['aprendiz.usuario', 'instructor.usuario']);

            $estadoLabel = $llamado->estado_label;
            $nombreAprendiz = trim(($llamado->aprendiz?->usuario?->nombres ?? '') . ' ' . ($llamado->aprendiz?->usuario?->apellidos ?? '')) ?: 'Aprendiz';

            // Destinos: todos los correos del aprendiz + el correo del instructor.
            $destinos = self::correosDestino($llamado);
            $correoInstructor = trim((string) ($llamado->instructor?->usuario?->correo ?? ''));
            if ($correoInstructor !== '' && filter_var($correoInstructor, FILTER_VALIDATE_EMAIL)) {
                $destinos[] = $correoInstructor;
            }
            $destinos = array_values(array_unique(array_map('mb_strtolower', $destinos)));

            if ($destinos === []) {
                Log::warning("[LLAMADO] Sin correos de destino para notificar el estado del llamado #{$llamado->id_llamado}.");

                return false;
            }

            if (config('mail.default') === 'log') {
                Log::info("[LLAMADO] Estado '{$estadoLabel}' del llamado #{$llamado->id_llamado} notificado a: " . implode(', ', $destinos));

                return true;
            }

            $mail = self::mailer();
            foreach ($destinos as $destino) {
                $mail->addAddress($destino);
            }

            $fecha = Carbon::parse($llamado->fecha_llamado)->translatedFormat('d \d\e F \d\e Y');
            $mail->isHTML(true);
            $mail->Subject = 'GEVLA - Actualización del llamado de atención N.° ' . $llamado->id_llamado;
            $mail->Body    = self::plantillaEstado($llamado, $nombreAprendiz, $estadoLabel, $fecha);
            $mail->AltBody = "El llamado de atención N.° {$llamado->id_llamado} ({$llamado->asunto}) del aprendiz {$nombreAprendiz}, "
                . "registrado el {$fecha}, cambió su estado a: {$estadoLabel}. Mensaje automático de GEVLA - SENA.";

            $mail->send();

            return true;
        } catch (\Throwable $e) {
            Log::error("[LLAMADO] No se pudo notificar el estado del llamado #{$llamado->id_llamado}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Instancia de PHPMailer ya configurada con el SMTP del .env (compartida
     * por los distintos correos de llamados).
     */
    private static function mailer(): PHPMailer
    {
        $smtp   = (array) config('mail.mailers.smtp');
        $puerto = (int) ($smtp['port'] ?? 587);

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = (string) ($smtp['host'] ?? 'smtp.gmail.com');
        $mail->Port       = $puerto;
        $mail->SMTPAuth   = true;
        $mail->Username   = (string) ($smtp['username'] ?? '');
        $mail->Password   = (string) ($smtp['password'] ?? '');
        $mail->SMTPSecure = $puerto === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = PHPMailer::CHARSET_UTF8;
        $mail->SMTPDebug  = SMTP::DEBUG_OFF;

        if (! filter_var($smtp['verificar_tls'] ?? true, FILTER_VALIDATE_BOOL)) {
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];
        }

        $mail->setFrom((string) config('mail.from.address'), (string) config('mail.from.name'));

        return $mail;
    }

    /**
     * Plantilla HTML del correo de cambio de estado (paleta institucional).
     */
    private static function plantillaEstado(LlamadoAtencion $llamado, string $nombreAprendiz, string $estadoLabel, string $fecha): string
    {
        $e = fn ($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
        $anio = date('Y');
        $logo = 'https://commons.wikimedia.org/wiki/Special:FilePath/Sena_Colombia_logo.svg?width=110';

        $filas  = self::fila('Llamado', 'N.° ' . $e((string) $llamado->id_llamado));
        $filas .= self::fila('Aprendiz', $e($nombreAprendiz));
        $filas .= self::fila('Asunto', $e($llamado->asunto));
        $filas .= self::fila('Fecha del llamado', $e($fecha));
        $filas .= self::fila('Nuevo estado', '<strong style="color:#247200;">' . $e($estadoLabel) . '</strong>');

        return <<<HTML
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f0;padding:32px 12px;">
            <tr>
                <td align="center">
                    <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;font-family:'Segoe UI',Arial,Helvetica,sans-serif;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
                        <tr>
                            <td style="padding:30px 40px 22px;">
                                <table role="presentation" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="vertical-align:middle;"><img src="{$logo}" alt="SENA" width="54" style="display:block;"></td>
                                        <td style="vertical-align:middle;padding-left:14px;">
                                            <div style="font-size:26px;font-weight:800;color:#39A900;letter-spacing:2px;line-height:1;">GEVLA</div>
                                            <div style="font-size:10px;font-weight:700;color:#94a3b8;letter-spacing:3px;padding-top:4px;">SERVICIO NACIONAL DE APRENDIZAJE</div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr><td style="height:4px;background:#39A900;font-size:0;line-height:0;">&nbsp;</td></tr>
                        <tr>
                            <td style="padding:34px 40px 6px;">
                                <p style="margin:0 0 6px;font-size:19px;font-weight:700;color:#0f172a;">Actualizaci&oacute;n del llamado de atenci&oacute;n</p>
                                <p style="margin:0 0 20px;font-size:14px;line-height:1.7;color:#475569;">
                                    El estado del llamado de atenci&oacute;n ha cambiado. Este es el detalle:
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 40px;">
                                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                                    {$filas}
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:20px 40px 30px;">
                                <p style="margin:0;font-size:13px;line-height:1.7;color:#94a3b8;">
                                    Puedes consultar el detalle completo ingresando a tu portal GEVLA.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="background:#00324D;padding:18px 40px;">
                                <p style="margin:0;font-size:11px;line-height:1.6;color:#cbd5e1;">
                                    Mensaje autom&aacute;tico de <strong style="color:#ffffff;">GEVLA</strong> &middot; Sistema de Gesti&oacute;n Disciplinaria y Formativa<br>
                                    &copy; {$anio} SENA &mdash; Servicio Nacional de Aprendizaje. No respondas a este correo.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        HTML;
    }

    /**
     * Correos del aprendiz a los que se enviará la notificación: se usan TODAS
     * las direcciones válidas y distintas (institucional, personal y la de la
     * cuenta), para maximizar la entrega. Así el aprendiz recibe el aviso sin
     * importar cuál correo revise.
     *
     * @return array<int, string>
     */
    private static function correosDestino(LlamadoAtencion $llamado): array
    {
        $aprendiz = $llamado->aprendiz;

        $candidatos = [
            $aprendiz?->correo_institucional,
            $aprendiz?->correo_personal,
            $aprendiz?->usuario?->correo,
        ];

        $validos = [];
        foreach ($candidatos as $correo) {
            $correo = trim((string) $correo);
            if ($correo !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                // Se compara en minúsculas para no duplicar la misma dirección.
                $validos[mb_strtolower($correo)] = $correo;
            }
        }

        return array_values($validos);
    }

    /**
     * Versión de texto plano (para clientes que no muestran HTML).
     */
    private static function textoPlano(LlamadoAtencion $llamado, string $nombre): string
    {
        $fecha = Carbon::parse($llamado->fecha_llamado)->translatedFormat('d \d\e F \d\e Y');

        $lineas = [
            "Hola {$nombre},",
            '',
            'Se te ha registrado un llamado de atención en GEVLA con los siguientes datos:',
            '',
            "Fecha del llamado: {$fecha}",
            'Tipo de llamado: ' . $llamado->tipo_label,
            'Categoría: ' . $llamado->categoria_label,
        ];

        if ($llamado->calificacion_falta) {
            $lineas[] = 'Calificación de la falta: ' . $llamado->calificacion_label;
        }
        if ($llamado->articulo) {
            $lineas[] = 'Artículo / falta del reglamento: '
                . $llamado->articulo->numero_articulo . ' — ' . $llamado->articulo->titulo;
        }

        $lineas[] = 'Asunto: ' . $llamado->asunto;
        $lineas[] = 'Descripción de los hechos: ' . $llamado->descripcion_hechos;

        if ($llamado->tiene_pruebas) {
            if ($llamado->pruebas_texto !== '') {
                $lineas[] = 'Pruebas aportadas: ' . $llamado->pruebas_texto;
            }
            if (count($llamado->pruebas_fotos)) {
                $lineas[] = 'Fotos de evidencia: ' . count($llamado->pruebas_fotos) . ' adjunta(s) a este correo.';
            }
        }

        $lineas[] = '';
        $lineas[] = 'Mensaje automático de GEVLA - SENA. No respondas a este correo.';

        return implode("\n", $lineas);
    }

    /**
     * Plantilla HTML del correo, con la paleta institucional de GEVLA,
     * maquetada con tablas (el estándar de los clientes de correo).
     */
    private static function plantilla(LlamadoAtencion $llamado, string $nombre): string
    {
        $e = fn ($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

        $nombreSeguro = $e($nombre);
        $fecha        = $e(Carbon::parse($llamado->fecha_llamado)->translatedFormat('d \d\e F \d\e Y'));
        $tipo         = $e($llamado->tipo_label);
        $categoria    = $e($llamado->categoria_label);
        $asunto       = $e($llamado->asunto);
        $descripcion  = nl2br($e($llamado->descripcion_hechos));
        $anio         = date('Y');
        $logo         = 'https://commons.wikimedia.org/wiki/Special:FilePath/Sena_Colombia_logo.svg?width=110';

        // Filas opcionales: solo se muestran si el llamado tiene el dato.
        $filas = '';
        $filas .= self::fila('Fecha del llamado', $fecha);
        $filas .= self::fila('Tipo de llamado', $tipo);
        $filas .= self::fila('Categoría', $categoria);

        if ($llamado->calificacion_falta) {
            $filas .= self::fila('Calificación de la falta', $e($llamado->calificacion_label));
        }
        if ($llamado->articulo) {
            $filas .= self::fila(
                'Artículo / falta del reglamento',
                $e($llamado->articulo->numero_articulo . ' — ' . $llamado->articulo->titulo)
            );
        }

        $filas .= self::fila('Asunto', $asunto);

        // Bloques de texto largo (descripción y pruebas) van a ancho completo.
        $bloques = self::bloque('Descripción de los hechos', $descripcion);
        if ($llamado->tiene_pruebas) {
            $contenidoPruebas = '';
            if ($llamado->pruebas_texto !== '') {
                $contenidoPruebas .= nl2br($e($llamado->pruebas_texto));
            }
            if (count($llamado->pruebas_fotos)) {
                $contenidoPruebas .= ($contenidoPruebas !== '' ? '<br><br>' : '')
                    . '<em>' . count($llamado->pruebas_fotos) . ' foto(s) de evidencia adjunta(s) a este correo.</em>';
            }
            $bloques .= self::bloque('Pruebas aportadas', $contenidoPruebas);
        }

        return <<<HTML
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f0;padding:32px 12px;">
            <tr>
                <td align="center">
                    <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;font-family:'Segoe UI',Arial,Helvetica,sans-serif;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

                        <!-- Cabecera con logo sobre blanco -->
                        <tr>
                            <td style="padding:30px 40px 22px;">
                                <table role="presentation" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="vertical-align:middle;"><img src="{$logo}" alt="SENA" width="54" style="display:block;"></td>
                                        <td style="vertical-align:middle;padding-left:14px;">
                                            <div style="font-size:26px;font-weight:800;color:#39A900;letter-spacing:2px;line-height:1;">GEVLA</div>
                                            <div style="font-size:10px;font-weight:700;color:#94a3b8;letter-spacing:3px;padding-top:4px;">SERVICIO NACIONAL DE APRENDIZAJE</div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Barra de acento institucional -->
                        <tr><td style="height:4px;background:#39A900;font-size:0;line-height:0;">&nbsp;</td></tr>

                        <!-- Cuerpo -->
                        <tr>
                            <td style="padding:34px 40px 6px;">
                                <p style="margin:0 0 6px;font-size:19px;font-weight:700;color:#0f172a;">Llamado de atenci&oacute;n registrado</p>
                                <p style="margin:0 0 20px;font-size:14px;line-height:1.7;color:#475569;">
                                    Hola <strong style="color:#0f172a;">{$nombreSeguro}</strong>,<br>
                                    se te ha registrado un llamado de atenci&oacute;n en GEVLA. A continuaci&oacute;n
                                    encontrar&aacute;s el detalle:
                                </p>
                            </td>
                        </tr>

                        <!-- Tabla de datos -->
                        <tr>
                            <td style="padding:0 40px;">
                                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                                    {$filas}
                                </table>
                            </td>
                        </tr>

                        <!-- Bloques de texto largo -->
                        <tr>
                            <td style="padding:20px 40px 10px;">
                                {$bloques}
                            </td>
                        </tr>

                        <!-- Nota -->
                        <tr>
                            <td style="padding:6px 40px 30px;">
                                <p style="margin:0;font-size:13px;line-height:1.7;color:#94a3b8;">
                                    Si consideras que se trata de un error, comun&iacute;cate con tu instructor o con la
                                    coordinaci&oacute;n de tu programa de formaci&oacute;n.
                                </p>
                            </td>
                        </tr>

                        <!-- Pie -->
                        <tr>
                            <td style="background:#00324D;padding:18px 40px;">
                                <p style="margin:0;font-size:11px;line-height:1.6;color:#cbd5e1;">
                                    Mensaje autom&aacute;tico de <strong style="color:#ffffff;">GEVLA</strong> &middot; Sistema de Gesti&oacute;n Disciplinaria y Formativa<br>
                                    &copy; {$anio} SENA &mdash; Servicio Nacional de Aprendizaje. No respondas a este correo.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        HTML;
    }

    /**
     * Fila "etiqueta / valor" de la tabla de datos del correo.
     */
    private static function fila(string $etiqueta, string $valor): string
    {
        return <<<HTML
        <tr>
            <td style="padding:11px 16px;background:#f8faf6;border-bottom:1px solid #edf2ec;font-size:12px;font-weight:700;color:#64748b;width:42%;vertical-align:top;">{$etiqueta}</td>
            <td style="padding:11px 16px;border-bottom:1px solid #edf2ec;font-size:13px;color:#0f172a;vertical-align:top;">{$valor}</td>
        </tr>
        HTML;
    }

    /**
     * Bloque de texto largo (título arriba, contenido debajo).
     */
    private static function bloque(string $titulo, string $contenido): string
    {
        return <<<HTML
        <div style="margin-bottom:16px;">
            <p style="margin:0 0 4px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#94a3b8;">{$titulo}</p>
            <p style="margin:0;font-size:13px;line-height:1.7;color:#334155;">{$contenido}</p>
        </div>
        HTML;
    }
}
