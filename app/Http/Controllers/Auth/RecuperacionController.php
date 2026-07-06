<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Support\CorreoRecuperacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Recuperacion de contrasena en 3 pasos:
 *   1. El usuario ingresa el correo asociado a su perfil.
 *   2. Recibe un codigo de 6 digitos (via PHPMailer) y lo digita.
 *   3. Define la nueva contrasena y su confirmacion.
 *
 * El estado del flujo vive en la sesion: el codigo se guarda HASHEADO,
 * vence a los 10 minutos y admite maximo 5 intentos.
 */
class RecuperacionController extends Controller
{
    private const MINUTOS_VIGENCIA = 10;
    private const MAX_INTENTOS = 5;
    private const SEGUNDOS_REENVIO = 60;

    /**
     * Paso 1: formulario para ingresar el correo.
     */
    public function mostrarSolicitud(): View
    {
        return view('auth.recuperar.solicitud');
    }

    /**
     * Paso 1 (envio): valida el correo, genera el codigo y lo envia.
     */
    public function enviarCodigo(Request $request): RedirectResponse
    {
        $request->validate([
            'correo' => ['required', 'email'],
        ], [
            'correo.required' => 'Ingresa el correo asociado a tu perfil.',
            'correo.email'    => 'Ingresa un correo válido.',
        ]);

        $correo = mb_strtolower(trim((string) $request->input('correo')));
        $usuario = $this->buscarUsuarioPorCorreo($correo);

        if (! $usuario) {
            return back()
                ->withInput()
                ->withErrors(['correo' => 'No encontramos ningún perfil asociado a ese correo.']);
        }

        if ($usuario->estado_usuario !== 'activo') {
            return back()
                ->withInput()
                ->withErrors(['correo' => 'La cuenta asociada a ese correo no está activa. Contacta a coordinación.']);
        }

        return $this->generarYEnviar($request, $usuario, $correo);
    }

    /**
     * Paso 2: formulario para digitar el codigo recibido.
     */
    public function mostrarCodigo(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('recuperacion')) {
            return redirect()->route('recuperar.solicitud');
        }

        $datos = $request->session()->get('recuperacion');

        return view('auth.recuperar.codigo', [
            'correoEnmascarado' => $this->enmascarar((string) $datos['correo']),
        ]);
    }

    /**
     * Paso 2 (verificacion): compara el codigo digitado con el enviado.
     */
    public function verificarCodigo(Request $request): RedirectResponse
    {
        $datos = $request->session()->get('recuperacion');

        if (! $datos) {
            return redirect()->route('recuperar.solicitud');
        }

        $request->validate([
            'codigo' => ['required', 'digits:6'],
        ], [
            'codigo.required' => 'Digita el código que enviamos a tu correo.',
            'codigo.digits'   => 'El código es de 6 dígitos.',
        ]);

        if (now()->getTimestamp() > (int) $datos['expira']) {
            $request->session()->forget('recuperacion');

            return redirect()
                ->route('recuperar.solicitud')
                ->withErrors(['correo' => 'El código venció. Solicita uno nuevo.']);
        }

        if ((int) $datos['intentos'] >= self::MAX_INTENTOS) {
            $request->session()->forget('recuperacion');

            return redirect()
                ->route('recuperar.solicitud')
                ->withErrors(['correo' => 'Demasiados intentos fallidos. Solicita un código nuevo.']);
        }

        if (! Hash::check((string) $request->input('codigo'), (string) $datos['codigo_hash'])) {
            $datos['intentos'] = (int) $datos['intentos'] + 1;
            $request->session()->put('recuperacion', $datos);
            $restantes = self::MAX_INTENTOS - $datos['intentos'];

            return back()->withErrors([
                'codigo' => $restantes > 0
                    ? "Código incorrecto. Te quedan {$restantes} intento(s)."
                    : 'Código incorrecto. Solicita un código nuevo.',
            ]);
        }

        $datos['verificado'] = true;
        $request->session()->put('recuperacion', $datos);

        return redirect()->route('recuperar.nueva');
    }

    /**
     * Reenvia un codigo nuevo al mismo correo (maximo uno por minuto).
     */
    public function reenviarCodigo(Request $request): RedirectResponse
    {
        $datos = $request->session()->get('recuperacion');

        if (! $datos) {
            return redirect()->route('recuperar.solicitud');
        }

        $transcurridos = now()->getTimestamp() - (int) ($datos['ultimo_envio'] ?? 0);
        if ($transcurridos < self::SEGUNDOS_REENVIO) {
            $espera = self::SEGUNDOS_REENVIO - $transcurridos;

            return back()->withErrors(['codigo' => "Espera {$espera} segundos para reenviar el código."]);
        }

        $usuario = Usuario::find((int) $datos['id_usuario']);

        if (! $usuario || $usuario->estado_usuario !== 'activo') {
            $request->session()->forget('recuperacion');

            return redirect()->route('recuperar.solicitud');
        }

        return $this->generarYEnviar($request, $usuario, (string) $datos['correo']);
    }

    /**
     * Paso 3: formulario de nueva contrasena (solo con el codigo ya verificado).
     */
    public function mostrarNueva(Request $request): View|RedirectResponse
    {
        $datos = $request->session()->get('recuperacion');

        if (! $datos || empty($datos['verificado'])) {
            return redirect()->route('recuperar.solicitud');
        }

        return view('auth.recuperar.nueva');
    }

    /**
     * Paso 3 (guardado): actualiza la contrasena y cierra el flujo.
     */
    public function guardarNueva(Request $request): RedirectResponse
    {
        $datos = $request->session()->get('recuperacion');

        if (! $datos || empty($datos['verificado'])) {
            return redirect()->route('recuperar.solicitud');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required'  => 'Ingresa la nueva contraseña.',
            'password.min'       => 'La contraseña debe tener mínimo 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $usuario = Usuario::find((int) $datos['id_usuario']);

        if (! $usuario) {
            $request->session()->forget('recuperacion');

            return redirect()->route('recuperar.solicitud');
        }

        $usuario->update(['password_hash' => Hash::make((string) $request->input('password'))]);
        $request->session()->forget('recuperacion');

        return redirect()
            ->route('login')
            ->with('status', 'Tu contraseña fue cambiada correctamente. Ya puedes iniciar sesión con ella.');
    }

    /**
     * Genera un codigo de 6 digitos, lo guarda hasheado en sesion y lo envia.
     */
    private function generarYEnviar(Request $request, Usuario $usuario, string $correo): RedirectResponse
    {
        $codigo = (string) random_int(100000, 999999);

        try {
            CorreoRecuperacion::enviarCodigo($correo, trim("{$usuario->nombres} {$usuario->apellidos}"), $codigo);
        } catch (\Throwable $e) {
            Log::error('Fallo el envio del codigo de recuperacion: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['correo' => 'No pudimos enviar el correo en este momento. Verifica la configuración de correo o inténtalo más tarde.']);
        }

        $request->session()->put('recuperacion', [
            'id_usuario'   => (int) $usuario->id_usuario,
            'correo'       => $correo,
            'codigo_hash'  => Hash::make($codigo),
            'expira'       => now()->addMinutes(self::MINUTOS_VIGENCIA)->getTimestamp(),
            'intentos'     => 0,
            'verificado'   => false,
            'ultimo_envio' => now()->getTimestamp(),
        ]);

        return redirect()
            ->route('recuperar.codigo')
            ->with('status', 'Enviamos un código de 6 dígitos a tu correo. Revisa también la carpeta de spam.');
    }

    /**
     * Busca el usuario por cualquiera de sus correos: el principal de la
     * cuenta o, para aprendices, el personal y el institucional.
     */
    private function buscarUsuarioPorCorreo(string $correo): ?Usuario
    {
        return Usuario::query()
            ->where('correo', $correo)
            ->orWhereIn('id_usuario', function ($query) use ($correo) {
                $query->select('id_usuario')
                    ->from('aprendiz')
                    ->where('correo_personal', $correo)
                    ->orWhere('correo_institucional', $correo);
            })
            ->first();
    }

    /**
     * Enmascara el correo para mostrarlo sin exponerlo completo.
     * Ej.: juan.perez@gmail.com -> ju•••@gmail.com
     */
    private function enmascarar(string $correo): string
    {
        [$local, $dominio] = array_pad(explode('@', $correo, 2), 2, '');

        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));

        return "{$visible}•••@{$dominio}";
    }
}
