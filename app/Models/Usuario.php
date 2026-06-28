<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'username',
        'correo',
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
}
