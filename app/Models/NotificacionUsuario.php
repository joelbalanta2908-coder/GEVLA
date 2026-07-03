<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Roles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notificación interna dirigida a un usuario del sistema (cualquier rol).
 *
 * A diferencia de la tabla `notificacion` (comunicaciones oficiales del
 * proceso disciplinario del aprendiz), esta tabla alimenta la campanita del
 * panel: cada usuario tiene sus propias notificaciones y su estado de lectura
 * (leída / no leída) se guarda en base de datos, por lo que persiste entre
 * sesiones.
 */
class NotificacionUsuario extends Model
{
    protected $table = 'notificacion_usuario';

    protected $primaryKey = 'id_notificacion_usuario';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'titulo',
        'mensaje',
        'url',
        'leida',
        'fecha_creacion',
    ];

    protected function casts(): array
    {
        return [
            'leida'          => 'boolean',
            'fecha_creacion' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Emite una notificación a uno o varios usuarios (ids de usuario).
     *
     * @param  int|array<int, int|null>  $usuarios
     */
    public static function emitir(int|array $usuarios, string $titulo, ?string $mensaje = null, ?string $url = null): void
    {
        $ids = collect(is_array($usuarios) ? $usuarios : [$usuarios])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique();

        foreach ($ids as $id) {
            self::create([
                'id_usuario'     => $id,
                'titulo'         => $titulo,
                'mensaje'        => $mensaje,
                'url'            => $url,
                'leida'          => false,
                'fecha_creacion' => now(),
            ]);
        }
    }

    /**
     * Emite una notificación a todos los usuarios activos que tengan el rol
     * indicado (por ejemplo, todos los coordinadores).
     */
    public static function emitirARol(string $rol, string $titulo, ?string $mensaje = null, ?string $url = null): void
    {
        $ids = Usuario::where('estado_usuario', 'activo')
            ->whereHas('roles', fn ($q) => $q->where('nombre_rol', $rol))
            ->pluck('id_usuario')
            ->all();

        // Los coordinadores también pueden existir solo como perfil operativo.
        if ($rol === Roles::COORDINADOR) {
            $extra = Usuario::where('estado_usuario', 'activo')
                ->whereHas('coordinacion', fn ($q) => $q->where('estado_coordinacion', 'activo'))
                ->pluck('id_usuario')
                ->all();
            $ids = array_merge($ids, $extra);
        }

        self::emitir($ids, $titulo, $mensaje, $url);
    }
}
