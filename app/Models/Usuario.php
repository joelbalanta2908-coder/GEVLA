<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    /**
     * La tabla asociada al modelo.
     */
    protected $table = 'usuario';

    /**
     * La clave primaria de la tabla.
     */
    protected $primaryKey = 'id_usuario';

    /**
     * Indica si el modelo debe manejar timestamps automáticos.
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombres',
        'apellidos',
        'tipo_documento',
        'numero_documento',
        'telefono',
        'username',
        'correo',
        'foto_perfil',
        'password_hash',
        'estado_usuario',
        'ultimo_acceso',
    ];

    /**
     * Los atributos que deben estar ocultos en la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ultimo_acceso' => 'datetime',
            'fecha_creacion' => 'datetime',
        ];
    }

    /**
     * Obtiene el nombre del campo de contraseña para autenticación.
     * Laravel usa este método internamente para saber dónde está la contraseña.
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /**
     * URL pública de la foto de perfil (o null si no tiene).
     */
    public function fotoUrl(): ?string
    {
        return $this->foto_perfil ? asset('storage/' . $this->foto_perfil) : null;
    }

    /**
     * Iniciales del usuario para el avatar por defecto.
     */
    public function iniciales(): string
    {
        return strtoupper(
            mb_substr($this->nombres ?? 'U', 0, 1) . mb_substr($this->apellidos ?? '', 0, 1)
        );
    }

    /**
     * Relación muchos a muchos con los roles del usuario.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Rol::class,
            'usuario_rol',
            'id_usuario',
            'id_rol'
        )->withPivot('estado_asignacion');
    }

    /**
     * Verifica si el usuario tiene un rol específico activo.
     */
    public function tieneRol(string $nombreRol): bool
    {
        return $this->roles()
            ->where('nombre_rol', $nombreRol)
            ->wherePivot('estado_asignacion', 'activa')
            ->exists();
    }

    /**
     * Obtiene el rol principal activo del usuario.
     */
    public function rolPrincipal(): ?string
    {
        $rol = $this->roles()
            ->wherePivot('estado_asignacion', 'activa')
            ->first();

        return $rol?->nombre_rol;
    }

    /**
     * Relación con la coordinación del usuario (si tiene rol Coordinador).
     */
    public function coordinacion(): HasOne
    {
        return $this->hasOne(Coordinacion::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Indica si el usuario es Coordinador Misional (única figura autorizada para
     * designar el instructor líder de una ficha). Se identifica por el `cargo`
     * de su coordinación activa, ya que la tabla `rol` no distingue subtipos de
     * coordinador.
     */
    public function esCoordinadorMisional(): bool
    {
        $coordinacion = $this->coordinacion;

        return $coordinacion !== null
            && $coordinacion->estado_coordinacion === 'activo'
            && str_contains(mb_strtolower((string) $coordinacion->cargo), 'misional');
    }

    /**
     * Relación con el aprendiz (si el usuario es un aprendiz).
     */
    public function aprendiz(): HasOne
    {
        return $this->hasOne(Aprendiz::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Relación con el instructor (si el usuario es un instructor).
     */
    public function instructor(): HasOne
    {
        return $this->hasOne(Instructor::class, 'id_usuario', 'id_usuario');
    }
}
