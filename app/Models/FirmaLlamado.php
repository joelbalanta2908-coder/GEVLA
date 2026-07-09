<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

/**
 * Firma de un llamado de atención: registra quién firmó (usuario), con qué
 * rol (Instructor / Coordinador / Aprendiz) y cuándo (fecha y hora), para la
 * trazabilidad del proceso disciplinario.
 *
 * Requiere importar database/sql/modulo_firmas.sql. Si la tabla no existe,
 * moduloInstalado() devuelve false y las acciones de firma se deshabilitan
 * sin romper el resto del sistema (mismo patrón que NotificacionUsuario).
 */
class FirmaLlamado extends Model
{
    protected $table = 'firma_llamado';

    protected $primaryKey = 'id_firma';

    public $timestamps = false;

    public const ROL_INSTRUCTOR  = 'Instructor';
    public const ROL_COORDINADOR = 'Coordinador';
    public const ROL_APRENDIZ    = 'Aprendiz';

    protected $fillable = [
        'id_llamado',
        'id_usuario',
        'rol_firma',
        'fecha_firma',
    ];

    protected function casts(): array
    {
        return [
            'fecha_firma' => 'datetime',
        ];
    }

    public function llamado(): BelongsTo
    {
        return $this->belongsTo(LlamadoAtencion::class, 'id_llamado', 'id_llamado');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Registra la firma de un rol sobre un llamado (una sola vez por rol:
     * si ya estaba firmado por ese rol, conserva la firma original).
     */
    public static function firmar(int $idLlamado, int $idUsuario, string $rol): self
    {
        return static::firstOrCreate(
            ['id_llamado' => $idLlamado, 'rol_firma' => $rol],
            ['id_usuario' => $idUsuario, 'fecha_firma' => now()]
        );
    }

    /**
     * Firma de un rol concreto sobre un llamado, o null si aún no firma.
     */
    public static function de(int $idLlamado, string $rol): ?self
    {
        if (! self::moduloInstalado()) {
            return null;
        }

        return static::where('id_llamado', $idLlamado)->where('rol_firma', $rol)->first();
    }

    /**
     * Indica si la tabla del módulo ya existe en la base de datos.
     */
    public static function moduloInstalado(): bool
    {
        static $instalado = null;

        return $instalado ??= Schema::hasTable('firma_llamado');
    }
}
