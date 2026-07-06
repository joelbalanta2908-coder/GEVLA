<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Envio del codigo de recuperacion de contrasena usando PHPMailer.
 *
 * La configuracion SMTP se toma del .env estandar de Laravel:
 *   MAIL_MAILER=smtp
 *   MAIL_HOST=smtp.gmail.com
 *   MAIL_PORT=587
 *   MAIL_USERNAME=tu_correo@gmail.com
 *   MAIL_PASSWORD=clave_de_aplicacion_de_16_letras
 *   MAIL_FROM_ADDRESS=tu_correo@gmail.com
 *   MAIL_FROM_NAME="GEVLA SENA"
 *
 * Modo de prueba: mientras MAIL_MAILER sea "log" (valor por defecto del
 * proyecto), el codigo NO se envia por internet; se escribe en
 * storage/logs/laravel.log para poder probar el flujo completo sin haber
 * configurado Gmail todavia.
 */
class CorreoRecuperacion
{
    /**
     * Envia el codigo de verificacion al correo indicado.
     *
     * @throws \PHPMailer\PHPMailer\Exception si el envio SMTP falla.
     */
    public static function enviarCodigo(string $destino, string $nombre, string $codigo): void
    {
        // Modo de prueba sin SMTP configurado.
        if (config('mail.default') === 'log') {
            Log::info("[RECUPERACION] Codigo para {$nombre} <{$destino}>: {$codigo}");

            return;
        }

        $smtp = (array) config('mail.mailers.smtp');
        $puerto = (int) ($smtp['port'] ?? 587);

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = (string) ($smtp['host'] ?? 'smtp.gmail.com');
        $mail->Port       = $puerto;
        $mail->SMTPAuth   = true;
        $mail->Username   = (string) ($smtp['username'] ?? '');
        $mail->Password   = (string) ($smtp['password'] ?? '');
        // 465 = TLS implicito (SMTPS); 587 = STARTTLS.
        $mail->SMTPSecure = $puerto === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = PHPMailer::CHARSET_UTF8;
        $mail->SMTPDebug  = SMTP::DEBUG_OFF;

        // Redes con inspección TLS (antivirus/proxy) reemplazan el certificado
        // del servidor y OpenSSL no puede validarlo. Con MAIL_VERIFICAR_TLS=false
        // en el .env se relaja la verificación (solo para desarrollo).
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
        $mail->addAddress($destino, $nombre);

        $mail->isHTML(true);
        $mail->Subject = 'GEVLA - Codigo de recuperacion de contrasena';
        $mail->Body    = self::plantilla($nombre, $codigo);
        $mail->AltBody = "Hola {$nombre}. Tu codigo de recuperacion de contrasena en GEVLA es: {$codigo}. "
            . 'Vence en 10 minutos. Si no solicitaste este cambio, ignora este mensaje.';

        $mail->send();
    }

    /**
     * Plantilla HTML del correo, con la paleta institucional de GEVLA.
     * Maquetada con tablas (el estándar de los clientes de correo) y con el
     * logosímbolo SENA verde alojado en un CDN público, ya que Gmail y otros
     * clientes no muestran imágenes incrustadas en base64.
     */
    private static function plantilla(string $nombre, string $codigo): string
    {
        $nombreSeguro = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
        $anio = date('Y');
        $logo = 'https://commons.wikimedia.org/wiki/Special:FilePath/Sena_Colombia_logo.svg?width=110';

        return <<<HTML
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f0;padding:32px 12px;">
            <tr>
                <td align="center">
                    <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;font-family:'Segoe UI',Arial,Helvetica,sans-serif;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

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
                            <td style="padding:34px 40px 10px;">
                                <p style="margin:0 0 6px;font-size:19px;font-weight:700;color:#0f172a;">Recuperaci&oacute;n de contrase&ntilde;a</p>
                                <p style="margin:0 0 22px;font-size:14px;line-height:1.7;color:#475569;">
                                    Hola <strong style="color:#0f172a;">{$nombreSeguro}</strong>,<br>
                                    recibimos una solicitud para restablecer la contrase&ntilde;a de tu cuenta en GEVLA.
                                    Ingresa el siguiente c&oacute;digo de verificaci&oacute;n para continuar:
                                </p>
                            </td>
                        </tr>

                        <!-- Codigo -->
                        <tr>
                            <td align="center" style="padding:0 40px;">
                                <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td align="center" style="background:#f4f9ee;border:1px solid #cde8b8;border-radius:12px;padding:22px 10px;">
                                            <div style="font-size:36px;font-weight:800;letter-spacing:14px;color:#247200;font-family:'Segoe UI',Arial,sans-serif;padding-left:14px;">{$codigo}</div>
                                            <div style="font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:1px;padding-top:8px;">V&Aacute;LIDO POR 10 MINUTOS</div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Notas -->
                        <tr>
                            <td style="padding:26px 40px 34px;">
                                <p style="margin:0 0 8px;font-size:13px;line-height:1.7;color:#64748b;">
                                    Por tu seguridad, no compartas este c&oacute;digo con nadie. El equipo de GEVLA
                                    nunca te lo pedir&aacute; por tel&eacute;fono ni por mensaje.
                                </p>
                                <p style="margin:0;font-size:13px;line-height:1.7;color:#94a3b8;">
                                    Si no solicitaste este cambio, puedes ignorar este mensaje:
                                    tu contrase&ntilde;a seguir&aacute; siendo la misma.
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
}
