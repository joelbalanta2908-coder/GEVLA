<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PerfilController extends Controller
{
    /**
     * Determina el layout base según el rol.
     */
    private function getLayoutName($usuario): string
    {
        return match (true) {
            $usuario->tieneRol('Coordinador') => 'layouts.coordinador',
            $usuario->tieneRol('Instructor')  => 'layouts.instructor',
            default => 'layouts.aprendiz',
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

        $validated = $request->validate([
            'nombres'     => ['required', 'string', 'max:255'],
            'apellidos'   => ['required', 'string', 'max:255'],
            'correo'      => ['required', 'email', 'max:255', 'unique:usuario,correo,' . $usuario->id_usuario . ',id_usuario'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
