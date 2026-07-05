<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Roles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

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
        // Si el módulo aún no está instalado (falta importar
        // database/sql/modulo_notificaciones.sql), la notificación de campanita
        // se omite sin romper la operación principal (llamado, acta, proceso...).
        // La tabla oficial `notificacion` del aprendiz no depende de esto.
        if (! self::moduloInstalado()) {
            return;
        }

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
        if (! self::moduloInstalado()) {
            return;
        }

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

    /**
     * Indica si la tabla del módulo ya existe en la base de datos. Se consulta
     * una sola vez por petición.
     */
    public static function moduloInstalado(): bool
    {
        static $instalado = null;

        return $instalado ??= Schema::hasTable('notificacion_usuario');
    }
}
