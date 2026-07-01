<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Usuario;
use App\Support\Roles;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe un grupo de rutas al rol ACTIVO de la sesión.
 *
 * El backend es la única fuente de verdad: la ruta solo se sirve si el rol
 * activo del usuario está entre los requeridos y realmente le pertenece. Si el
 * rol activo no corresponde (aunque el usuario tenga ese otro rol disponible),
 * se le devuelve a su dashboard activo para que use el selector de roles.
 *
 * Uso en rutas:  ->middleware('rol:Coordinador')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::user();

        if (! $usuario) {
            abort(401);
        }

        $activo = $request->session()->get('rol_activo') ?? Roles::porDefecto($usuario);

        // El rol activo debe ser uno de los requeridos y estar disponible.
        if (in_array($activo, $roles, true) && Roles::puede($usuario, $activo)) {
            return $next($request);
        }

        // Rol activo válido pero distinto: lo enviamos a su dashboard activo.
        if (Roles::puede($usuario, $activo)) {
            return redirect()
                ->route(Roles::dashboardRoute($activo))
                ->with('info', 'Cambia al rol correspondiente para acceder a esa sección.');
        }

        abort(403, 'No tienes permisos para acceder a esta sección.');
    }
}
