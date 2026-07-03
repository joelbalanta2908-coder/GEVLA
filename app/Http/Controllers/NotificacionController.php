<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificacionController extends Controller
{
    /**
     * Marca como 'recibida' las notificaciones pendientes para el usuario autenticado.
     */
    public function marcarRecibidas(Request $request): JsonResponse
    {
        $user = auth()->user();
        $updated = 0;

        if ($user?->aprendiz) {
            $updated = \App\Models\Notificacion::where('id_aprendiz', $user->aprendiz->id_aprendiz)
                ->where('estado_notificacion', 'enviada')
                ->update(['estado_notificacion' => 'recibida']);
        } elseif ($user?->instructor) {
            $updated = \App\Models\Notificacion::whereHas('llamado', fn($q) => $q->where('id_instructor', $user->instructor->id_instructor))
                ->where('estado_notificacion', 'enviada')
                ->update(['estado_notificacion' => 'recibida']);
        }

        return response()->json(['ok' => true, 'updated' => $updated]);
    }
}
