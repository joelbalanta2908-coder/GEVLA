<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Muestra el formulario para editar el perfil (para Coordinador e Instructor).
     */
    public function edit(): View
    {
        $usuario = Auth::user();
        
        // Bloqueamos al aprendiz de acceder a la vista de edición directamente
        if ($usuario->tieneRol('Aprendiz') && !$usuario->tieneRol('Coordinador') && !$usuario->tieneRol('Instructor')) {
            abort(403, 'No tienes permisos para editar tu perfil.');
        }

        $layout = $this->getLayoutName($usuario);
        return view('perfil.edit', compact('usuario', 'layout'));
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

        if ($usuario->tieneRol('Aprendiz') && !$usuario->tieneRol('Coordinador') && !$usuario->tieneRol('Instructor')) {
            abort(403, 'No tienes permisos para actualizar tu perfil.');
        }

        $validated = $request->validate([
            'nombres'   => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'correo'    => ['required', 'email', 'max:255', 'unique:usuario,correo,' . $usuario->id_usuario . ',id_usuario'],
        ]);

        $usuario->update($validated);

        return redirect()->route('perfil.show')->with('success', 'Perfil actualizado exitosamente.');
    }
}
