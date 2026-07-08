<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Texto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

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

        $usuario->update($datos);

        return redirect()->route('perfil.show')->with('success', 'Perfil actualizado exitosamente.');
    }
}
