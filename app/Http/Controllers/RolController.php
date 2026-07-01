<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Cambio dinámico de rol activo sin cerrar sesión.
 *
 * El backend valida que el usuario solo pueda activar roles realmente
 * asignados; el rol activo queda persistido en la sesión y determina permisos,
 * menús, rutas y dashboard hasta que se cambie de nuevo o se cierre sesión.
 */
class RolController extends Controller
{
    public function cambiar(Request $request): RedirectResponse
    {
        $request->validate([
            'rol' => ['required', 'string'],
        ]);

        $usuario = Auth::user();
        $rol = (string) $request->input('rol');

        // Seguridad: no se puede activar un rol que no pertenece al usuario.
        if (! Roles::puede($usuario, $rol)) {
            return back()->withErrors([
                'rol' => 'No puedes activar un rol que no tienes asignado.',
            ]);
        }

        $request->session()->put('rol_activo', $rol);

        return redirect()
            ->route(Roles::dashboardRoute($rol))
            ->with('success', 'Ahora estás usando el rol: ' . Roles::etiqueta($rol, $usuario) . '.');
    }
}
