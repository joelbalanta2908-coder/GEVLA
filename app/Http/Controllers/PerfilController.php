<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Firmas;
use App\Support\Texto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PerfilController extends Controller
{
    /**
     * Determina el layout base según el ROL ACTIVO de la sesión (la misma
     * fuente de verdad de todo el sistema), para que el perfil conserve el
     * panel desde el que se abrió aunque el usuario tenga varios roles.
     */
    private function getLayoutName($usuario): string
    {
        $rol = session('rol_activo') ?? \App\Support\Roles::porDefecto($usuario);

        return match ($rol) {
            \App\Support\Roles::COORDINADOR => 'layouts.coordinador',
            \App\Support\Roles::INSTRUCTOR  => 'layouts.instructor',
            default                          => 'layouts.aprendiz',
        };
    }

    /**
     * Muestra la vista de solo lectura del perfil (para el Aprendiz).
     */
    public function show(): View
    {
        $usuario = Auth::user();
        $layout = $this->getLayoutName($usuario);
        return view('perfil.show', compact('usuario', 'layout'));
    }

    /**
     * La edición ahora vive dentro de "Ver mi perfil"; redirigimos al perfil.
     */
    public function edit(): RedirectResponse
    {
        return redirect()->route('perfil.show');
    }

    /**
     * Muestra la vista de ayuda y soporte del perfil.
     */
    public function help(): View
    {
        $usuario = Auth::user();
        $layout = $this->getLayoutName($usuario);

        return view('perfil.help', compact('usuario', 'layout'));
    }

    /**
     * Actualiza los datos del usuario en la base de datos.
     */
    public function update(Request $request): RedirectResponse
    {
        $usuario = Auth::user();

        // Recorta y colapsa espacios ("John   Fredy " -> "John Fredy") antes
        // de validar, para que la regla min:2 y el valor guardado sean sobre
        // el texto ya limpio.
        $request->merge([
            'nombres'   => Texto::normalizarEspacios($request->input('nombres')),
            'apellidos' => Texto::normalizarEspacios($request->input('apellidos')),
        ]);

        $validated = $request->validate([
            'nombres'     => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'apellidos'   => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'correo'      => ['required', 'email', 'max:255', 'unique:usuario,correo,' . $usuario->id_usuario . ',id_usuario'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'nombres.regex'   => 'Los nombres solo pueden contener letras y espacios, sin números ni caracteres especiales.',
            'nombres.min'     => 'Los nombres deben tener al menos 2 caracteres.',
            'apellidos.regex' => 'Los apellidos solo pueden contener letras y espacios, sin números ni caracteres especiales.',
            'apellidos.min'   => 'Los apellidos deben tener al menos 2 caracteres.',
            'correo.email'    => 'El correo debe ser una dirección válida (debe contener @).',
        ]);

        $datos = [
            'nombres'   => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'correo'    => $validated['correo'],
        ];

        if ($request->hasFile('foto_perfil')) {
            // Eliminamos la foto anterior si existía.
            if ($usuario->foto_perfil) {
                Storage::disk('public')->delete($usuario->foto_perfil);
            }
            $datos['foto_perfil'] = $request->file('foto_perfil')->store('perfiles', 'public');
        }

        // Si cambió el correo, se sincronizan TODAS las copias con las que el
        // login acepta identificarse (username y correos del perfil de
        // aprendiz): así el correo antiguo deja de servir para iniciar sesión
        // y solo funciona el nuevo.
        $correoAnterior = (string) $usuario->correo;
        $correoNuevo    = (string) $validated['correo'];

        if ($correoAnterior !== '' && $correoAnterior !== $correoNuevo) {
            if ((string) $usuario->username === $correoAnterior) {
                $datos['username'] = $correoNuevo;
            }

            if ($aprendiz = $usuario->aprendiz) {
                $cambiosAprendiz = [];
                if ((string) $aprendiz->correo_personal === $correoAnterior) {
                    $cambiosAprendiz['correo_personal'] = $correoNuevo;
                }
                if ((string) $aprendiz->correo_institucional === $correoAnterior) {
                    $cambiosAprendiz['correo_institucional'] = $correoNuevo;
                }
                if ($cambiosAprendiz !== []) {
                    $aprendiz->update($cambiosAprendiz);
                }
            }
        }

        $usuario->update($datos);

        return redirect()->route('perfil.show')->with('success', 'Perfil actualizado exitosamente.');
    }

    /**
     * Cambia la contraseña del usuario autenticado: exige la contraseña
     * actual y la nueva con confirmación.
     */
    public function cambiarPassword(Request $request): RedirectResponse
    {
        $usuario = Auth::user();

        $request->validate([
            'password_actual' => ['required', 'string'],
            'password_nueva'  => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
        ], [
            'password_actual.required' => 'Debes escribir tu contraseña actual.',
            'password_nueva.required'  => 'Debes escribir la nueva contraseña.',
            'password_nueva.min'       => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password_nueva.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        if (! Hash::check((string) $request->input('password_actual'), (string) $usuario->password_hash)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.']);
        }

        $usuario->update(['password_hash' => Hash::make((string) $request->input('password_nueva'))]);

        return redirect()->route('perfil.show')->with('success', 'Contraseña actualizada correctamente. Úsala en tu próximo inicio de sesión.');
    }

    /*
    |--------------------------------------------------------------------------
    | Firma manuscrita (sección "Firma" de Mi Perfil)
    |--------------------------------------------------------------------------
    | La imagen vive en el disco PRIVADO (storage/app/firmas): solo su dueño
    | puede verla y el sistema la incrusta en los documentos de llamados.
    */

    /**
     * Sirve la imagen de la firma del usuario AUTENTICADO (nunca la de otro).
     */
    public function verFirma(): BinaryFileResponse
    {
        $ruta = Firmas::rutaAbsoluta(Auth::user());

        abort_if($ruta === null, 404);

        return response()->file($ruta, [
            // Sin caché: al reemplazar la firma se ve la nueva de inmediato.
            'Cache-Control' => 'no-store, max-age=0',
        ]);
    }

    /**
     * Registra o reemplaza la firma del usuario autenticado.
     */
    public function guardarFirma(Request $request): RedirectResponse
    {
        $request->validate([
            'firma' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ], [
            'firma.required' => 'Selecciona la imagen de tu firma.',
            'firma.image'    => 'La firma debe ser una imagen (preferiblemente PNG con fondo transparente).',
            'firma.mimes'    => 'Formatos admitidos: PNG, JPG o WEBP.',
            'firma.max'      => 'La imagen de la firma no puede superar los 2 MB.',
        ]);

        Firmas::guardar(Auth::user(), $request->file('firma'));

        return redirect()->route('perfil.show')->with('success', 'Firma registrada correctamente. Se usará automáticamente en los documentos de llamados de atención.');
    }

    /**
     * Elimina la firma registrada del usuario autenticado.
     */
    public function eliminarFirma(): RedirectResponse
    {
        Firmas::eliminar(Auth::user());

        return redirect()->route('perfil.show')->with('success', 'Firma eliminada correctamente.');
    }
}
