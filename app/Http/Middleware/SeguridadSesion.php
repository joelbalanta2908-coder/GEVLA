<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Seguridad de la sesión en el navegador:
 *
 * 1) SIN CACHÉ tras cerrar sesión: a las páginas de usuarios autenticados se
 *    les añade "Cache-Control: no-store". Así, al pulsar "atrás" después de
 *    cerrar sesión, el navegador NO muestra el dashboard guardado en caché:
 *    vuelve a pedir la página y el sistema lo redirige al login.
 *
 * 2) CIERRE DE SESIÓN AL CERRAR LA PESTAÑA: cada página envía una señal
 *    (sendBeacon, ver layouts/toast.blade.php) cuando la pestaña se oculta o
 *    cierra, que marca la sesión con la hora del posible cierre. Si el usuario
 *    solo estaba navegando dentro del sistema, la siguiente petición llega en
 *    segundos y la marca se descarta. Si de verdad cerró la pestaña, no llega
 *    ninguna petición: cuando vuelva a abrir el sistema (pasado el periodo de
 *    gracia) la sesión se invalida y debe iniciar sesión de nuevo.
 */
class SeguridadSesion
{
    /**
     * Segundos de gracia entre la señal de cierre y la siguiente petición.
     * Cubre la navegación normal entre páginas (llega en menos de un segundo)
     * y las recargas, sin mantener viva una pestaña realmente cerrada.
     */
    private const GRACIA_SEGUNDOS = 8;

    public function handle(Request $request, Closure $next): Response
    {
        // La señal de cierre solo registra la marca de tiempo y termina.
        if ($request->routeIs('sesion.cerrando')) {
            return $next($request);
        }

        if (Auth::check()) {
            $marca = $request->session()->get('cierre_pendiente');

            if ($marca !== null && (time() - (int) $marca) > self::GRACIA_SEGUNDOS) {
                // La pestaña se cerró de verdad: se invalida la sesión.
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['login' => 'Tu sesión se cerró al cerrar la pestaña. Inicia sesión nuevamente.']);
            }

            // Navegación normal: se descarta la marca de cierre.
            if ($marca !== null) {
                $request->session()->forget('cierre_pendiente');
            }
        }

        $response = $next($request);

        // Sin caché para las páginas de usuarios autenticados (punto 1).
        if (Auth::check() && ! $request->expectsJson()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
