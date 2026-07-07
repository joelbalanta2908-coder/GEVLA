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
            'nombres'          => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'apellidos'        => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'tipo_documento'   => ['required', Rule::in(['CC', 'TI', 'CE', 'PEP'])],
            'numero_documento' => ['required', 'digits_between:8,10', 'unique:usuario,numero_documento'],
            'correo'           => ['required', 'email', 'max:120', 'unique:usuario,correo'],
            'telefono'         => ['nullable', 'digits:10'],
            'password'         => ['nullable', 'string', 'min:6', 'max:255', 'confirmed'],
        ], $extra), $this->mensajesValidacionPersona());
    }

    /**
     * Reglas de validación para EDITAR una persona ya creada: documento y
     * correo siguen siendo únicos, pero ignorando al propio usuario.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function validarPersonaEdicion(Request $request, Usuario $usuario, array $extra = []): array
    {
        return $request->validate(array_merge([
            'nombres'          => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'apellidos'        => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'tipo_documento'   => ['required', Rule::in(['CC', 'TI', 'CE', 'PEP'])],
            'numero_documento' => ['required', 'digits_between:8,10', Rule::unique('usuario', 'numero_documento')->ignore($usuario->id_usuario, 'id_usuario')],
            'correo'           => ['required', 'email', 'max:120', Rule::unique('usuario', 'correo')->ignore($usuario->id_usuario, 'id_usuario')],
            'telefono'         => ['nullable', 'digits:10'],
            'password'         => ['nullable', 'string', 'min:6', 'max:255', 'confirmed'],
        ], $extra), $this->mensajesValidacionPersona(edicion: true));
    }

    /**
     * Mensajes de error compartidos por validarPersona() y
     * validarPersonaEdicion(); solo cambia el texto de "unicidad" según si es
     * alta (choca con cualquiera) o edición (choca con "otro" usuario).
     *
     * @return array<string, string>
     */
    private function mensajesValidacionPersona(bool $edicion = false): array
    {
        return [
            'nombres.regex'                   => 'Los nombres solo pueden contener letras y espacios, sin números ni caracteres especiales.',
            'nombres.min'                     => 'Los nombres deben tener al menos 2 caracteres.',
            'apellidos.regex'                 => 'Los apellidos solo pueden contener letras y espacios, sin números ni caracteres especiales.',
            'apellidos.min'                   => 'Los apellidos deben tener al menos 2 caracteres.',
            'numero_documento.digits_between' => 'El número de documento debe tener entre 8 y 10 dígitos, solo números.',
            'telefono.digits'                 => 'El teléfono debe tener exactamente 10 dígitos, solo números.',
            'correo.email'                    => 'El correo debe ser una dirección válida (debe contener @).',
            'password.confirmed'              => 'La confirmación de la contraseña no coincide.',
            'numero_documento.unique'         => $edicion ? 'Ya existe otro usuario con ese número de documento.' : 'Ya existe un usuario con ese número de documento.',
            'correo.unique'                   => $edicion ? 'Ya existe otro usuario con ese correo.' : 'Ya existe un usuario con ese correo.',
        ];
    }

    /**
     * Aplica a un usuario existente los datos personales editados. La
     * contraseña solo cambia si se indicó una nueva, y el username se mantiene
     * sincronizado con el documento cuando seguía la convención de alta.
     *
     * @param  array<string, mixed>  $datos
     */
    protected function actualizarDatosUsuario(Usuario $usuario, array $datos): void
    {
        $cambios = [
            'nombres'          => $datos['nombres'],
            'apellidos'        => $datos['apellidos'],
            'tipo_documento'   => $datos['tipo_documento'],
            'numero_documento' => $datos['numero_documento'],
            'correo'           => $datos['correo'],
            'telefono'         => $datos['telefono'] ?? null,
        ];

        // El alta usa el documento como username: si aún coinciden, se sincroniza.
        if ((string) $usuario->username === (string) $usuario->numero_documento) {
            $cambios['username'] = $datos['numero_documento'];
        }

        if (! empty($datos['password'])) {
            $cambios['password_hash'] = Hash::make($datos['password']);
        }

        $usuario->update($cambios);
    }
}
