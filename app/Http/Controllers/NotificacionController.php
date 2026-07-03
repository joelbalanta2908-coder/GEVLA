<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\NotificacionUsuario;
use Illuminate\Http\JsonResponse;

/**
 * Notificaciones de la campanita del panel. El estado de lectura se guarda en
 * la tabla `notificacion_usuario`, por lo que persiste al cerrar sesión y
 * volver a entrar con las mismas credenciales.
 */
class NotificacionController extends Controller
{
    /**
     * Marca una notificación puntual del usuario autenticado como vista.
     */
    public function marcarLeida(NotificacionUsuario $notificacion): JsonResponse
    {
        abort_unless((int) $notificacion->id_usuario === (int) auth()->id(), 403);

        $notificacion->update(['leida' => true]);

        return response()->json(['ok' => true]);
    }

    /**
     * Marca todas las notificaciones pendientes del usuario autenticado como vistas.
     */
    public function marcarTodas(): JsonResponse
    {
        $actualizadas = NotificacionUsuario::where('id_usuario', auth()->id())
            ->where('leida', false)
            ->update(['leida' => true]);

        // Compatibilidad con las comunicaciones oficiales del aprendiz: al
        // marcar todo como visto también quedan como recibidas en su portal.
        $user = auth()->user();
        if ($user?->aprendiz) {
            Notificacion::where('id_aprendiz', $user->aprendiz->id_aprendiz)
                ->where('estado_notificacion', 'enviada')
                ->update(['estado_notificacion' => 'recibida']);
        }

        return response()->json(['ok' => true, 'actualizadas' => $actualizadas]);
    }
}
