<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Roles;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Normaliza y comparte el rol activo de la sesión.
 *
 * - Si no hay rol activo (o el guardado ya no está entre los roles del usuario)
 *   lo restablece al rol por defecto calculado por el backend. Así se persiste
 *   el rol elegido durante la sesión y se auto-corrige tras recargar.
 * - Comparte `rolActivo` y `rolesDisponibles` con todas las vistas para que el
 *   selector de roles y los menús se rendericen desde el backend.
 */
class ShareActiveRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Auth::user();

        if ($usuario) {
            $disponibles = Roles::disponiblesPara($usuario);
            $activo = $request->session()->get('rol_activo');

            if (! in_array($activo, $disponibles, true)) {
                $activo = Roles::porDefecto($usuario);
                $request->session()->put('rol_activo', $activo);
            }

            View::share('rolActivo', $activo);
            View::share('rolesDisponibles', $disponibles);
        }

        return $next($request);
    }
}
