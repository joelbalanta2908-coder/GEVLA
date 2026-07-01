<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Usuario;

/**
 * Fuente única de verdad de los roles operativos del sistema.
 *
 * Determina, a partir del backend (tabla `usuario_rol` y perfiles operativos
 * `coordinacion` / `instructor` / `aprendiz`), qué roles puede usar un usuario,
 * cuál es su rol por defecto, a qué dashboard corresponde cada rol y su
 * etiqueta legible. No existe lógica de roles en el cliente: el frontend solo
 * consume lo que aquí se calcula.
 */
final class Roles
{
    public const ADMINISTRADOR = 'Administrador';
    public const COORDINADOR   = 'Coordinador';
    public const INSTRUCTOR     = 'Instructor';
    public const APRENDIZ       = 'Aprendiz';

    /**
     * Orden de prioridad. El primer rol disponible es el rol por defecto
     * (rol principal) tras iniciar sesión.
     *
     * @var array<int, string>
     */
    public const PRIORIDAD = [self::ADMINISTRADOR, self::COORDINADOR, self::INSTRUCTOR, self::APRENDIZ];

    /**
     * Roles que el usuario tiene realmente asignados, en orden de prioridad.
     *
     * @return array<int, string>
     */
    public static function disponiblesPara(Usuario $usuario): array
    {
        $roles = [];

        if ($usuario->tieneRol(self::ADMINISTRADOR)) {
            $roles[] = self::ADMINISTRADOR;
        }
        if (self::tieneCoordinador($usuario)) {
            $roles[] = self::COORDINADOR;
        }
        if (self::tieneInstructor($usuario)) {
            $roles[] = self::INSTRUCTOR;
        }
        if (self::tieneAprendiz($usuario)) {
            $roles[] = self::APRENDIZ;
        }

        return $roles;
    }

    /**
     * Rol por defecto (principal) del usuario, o null si no tiene ninguno.
     */
    public static function porDefecto(Usuario $usuario): ?string
    {
        return self::disponiblesPara($usuario)[0] ?? null;
    }

    /**
     * Indica si el usuario puede activar el rol indicado.
     */
    public static function puede(Usuario $usuario, ?string $rol): bool
    {
        return $rol !== null && in_array($rol, self::disponiblesPara($usuario), true);
    }

    /**
     * Nombre de la ruta del dashboard correspondiente a un rol.
     */
    public static function dashboardRoute(?string $rol): string
    {
        return match ($rol) {
            self::ADMINISTRADOR => 'admin.usuarios.index',
            self::COORDINADOR   => 'coordinacion.dashboard',
            self::INSTRUCTOR    => 'instructor.dashboard',
            self::APRENDIZ      => 'aprendiz.dashboard',
            default             => 'login',
        };
    }

    /**
     * Etiqueta legible del rol. Para el coordinador usa el cargo real de su
     * coordinación (por ejemplo, «Coordinador Misional») cuando existe.
     */
    public static function etiqueta(string $rol, ?Usuario $usuario = null): string
    {
        if ($rol === self::COORDINADOR && $usuario) {
            $cargo = optional($usuario->coordinacion)->cargo;
            if (! empty($cargo)) {
                return $cargo;
            }
        }

        return $rol;
    }

    private static function tieneCoordinador(Usuario $usuario): bool
    {
        return $usuario->tieneRol(self::COORDINADOR)
            || optional($usuario->coordinacion)->estado_coordinacion === 'activo';
    }

    private static function tieneInstructor(Usuario $usuario): bool
    {
        return $usuario->tieneRol(self::INSTRUCTOR)
            || optional($usuario->instructor)->estado_instructor === 'activo';
    }

    private static function tieneAprendiz(Usuario $usuario): bool
    {
        return $usuario->tieneRol(self::APRENDIZ)
            || optional($usuario->aprendiz)->estado_academico === 'en_formacion';
    }
}
