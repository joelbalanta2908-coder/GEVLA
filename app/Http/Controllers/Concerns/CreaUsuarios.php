<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Lógica compartida para dar de alta personas (usuario + rol) desde los
 * controladores del coordinador. El username y la contraseña inicial se basan
 * en el número de documento.
 */
trait CreaUsuarios
{
    /**
     * Crea el usuario base y le asigna el rol indicado (usuario_rol).
     *
     * @param  array<string, mixed>  $datos
     */
    protected function crearUsuarioConRol(array $datos, string $rolNombre): Usuario
    {
        // Contraseña indicada por el coordinador o, si se deja vacía, el documento.
        $password = ! empty($datos['password']) ? $datos['password'] : $datos['numero_documento'];

        $usuario = Usuario::create([
            'nombres'          => $datos['nombres'],
            'apellidos'        => $datos['apellidos'],
            'tipo_documento'   => $datos['tipo_documento'],
            'numero_documento' => $datos['numero_documento'],
            'correo'           => $datos['correo'],
            'telefono'         => $datos['telefono'] ?? null,
            'username'         => $datos['numero_documento'],
            'password_hash'    => Hash::make($password),
            'estado_usuario'   => 'activo',
        ]);

        $rolId = Rol::where('nombre_rol', $rolNombre)->value('id_rol');
        if ($rolId) {
            $usuario->roles()->attach($rolId, [
                'fecha_asignacion'  => now(),
                'estado_asignacion' => 'activa',
            ]);
        }

        return $usuario;
    }

    /**
     * Reglas de validación comunes para dar de alta a una persona (usuario).
     * Documento y correo deben ser únicos.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function validarPersona(Request $request, array $extra = []): array
    {
        return $request->validate(array_merge([
            'nombres'          => ['required', 'string', 'max:100'],
            'apellidos'        => ['required', 'string', 'max:100'],
            'tipo_documento'   => ['required', Rule::in(['CC', 'TI', 'CE', 'PEP'])],
            'numero_documento' => ['required', 'string', 'max:20', 'unique:usuario,numero_documento'],
            'correo'           => ['required', 'email', 'max:120', 'unique:usuario,correo'],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'password'         => ['nullable', 'string', 'min:6', 'max:255', 'confirmed'],
        ], $extra), [
            'password.confirmed'      => 'La confirmación de la contraseña no coincide.',
            'numero_documento.unique' => 'Ya existe un usuario con ese número de documento.',
            'correo.unique'           => 'Ya existe un usuario con ese correo.',
        ]);
    }
}
