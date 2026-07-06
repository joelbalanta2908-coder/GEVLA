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
     */
    private static function plantilla(string $nombre, string $codigo): string
    {
        $nombreSeguro = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

        return <<<HTML
        <div style="margin:0 auto;max-width:520px;font-family:Arial,Helvetica,sans-serif;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
            <div style="background:#39A900;padding:20px 28px;">
                <p style="margin:0;font-size:22px;font-weight:bold;color:#ffffff;">GEVLA</p>
                <p style="margin:2px 0 0;font-size:12px;letter-spacing:2px;color:#eaffe0;">SENA &middot; RECUPERACI&Oacute;N DE CONTRASE&Ntilde;A</p>
            </div>
            <div style="padding:28px;background:#ffffff;color:#0f172a;">
                <p style="margin:0 0 12px;font-size:15px;">Hola <strong>{$nombreSeguro}</strong>,</p>
                <p style="margin:0 0 20px;font-size:14px;color:#475569;">
                    Recibimos una solicitud para restablecer tu contrase&ntilde;a en GEVLA.
                    Usa el siguiente c&oacute;digo para continuar:
                </p>
                <p style="margin:0 auto 20px;width:fit-content;background:#f4f9ee;border:1px dashed #39A900;border-radius:10px;padding:14px 28px;font-size:30px;font-weight:bold;letter-spacing:10px;color:#247200;">{$codigo}</p>
                <p style="margin:0 0 6px;font-size:13px;color:#475569;">El c&oacute;digo vence en <strong>10 minutos</strong>.</p>
                <p style="margin:0;font-size:13px;color:#94a3b8;">Si no solicitaste este cambio, ignora este mensaje: tu contrase&ntilde;a seguir&aacute; siendo la misma.</p>
            </div>
            <div style="background:#00324D;padding:14px 28px;">
                <p style="margin:0;font-size:11px;color:#cbd5e1;">Mensaje autom&aacute;tico de GEVLA &middot; SENA. No respondas a este correo.</p>
            </div>
        </div>
        HTML;
    }
}
