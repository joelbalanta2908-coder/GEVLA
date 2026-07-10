<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Models\Aprendiz;
use App\Models\Coordinacion;
use App\Models\Instructor;
use App\Models\Rol;
use App\Models\Usuario;
use App\Support\Roles;
use App\Support\Texto;
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
        $this->normalizarNombres($request);

        return $request->validate(array_merge([
            'nombres'          => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'apellidos'        => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'tipo_documento'   => ['required', Rule::in(['CC', 'TI', 'CE', 'PEP', 'PPT', 'PA'])],
            'numero_documento' => ['required', 'regex:/^[A-Za-z0-9]{6,10}$/', 'unique:usuario,numero_documento'],
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
        $this->normalizarNombres($request);

        return $request->validate(array_merge([
            'nombres'          => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'apellidos'        => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'tipo_documento'   => ['required', Rule::in(['CC', 'TI', 'CE', 'PEP', 'PPT', 'PA'])],
            'numero_documento' => ['required', 'regex:/^[A-Za-z0-9]{6,10}$/', Rule::unique('usuario', 'numero_documento')->ignore($usuario->id_usuario, 'id_usuario')],
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
            'numero_documento.regex'          => 'El número de documento debe tener entre 6 y 10 caracteres, solo letras y números (sin espacios ni caracteres especiales).',
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

    /**
     * Recorta y colapsa los espacios de nombres/apellidos ANTES de validar,
     * para que "John   Fredy " llegue como "John Fredy" tanto a las reglas de
     * validación (min:2, regex) como al valor que finalmente se guarda.
     */
    private function normalizarNombres(Request $request): void
    {
        if ($request->has('nombres')) {
            $request->merge(['nombres' => Texto::normalizarEspacios($request->input('nombres'))]);
        }
        if ($request->has('apellidos')) {
            $request->merge(['apellidos' => Texto::normalizarEspacios($request->input('apellidos'))]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Roles adicionales (múltiples roles por usuario)
    |--------------------------------------------------------------------------
    | Cada sección del coordinador (Aprendices/Instructores/Coordinadores) crea
    | y administra su propio perfil "ancla" con su lógica existente. Estos
    | métodos SOLO se encargan de los roles ADICIONALES que se marquen en el
    | formulario, reutilizando las columnas de estado que cada tabla ya tiene
    | (estado_instructor, estado_coordinacion, estado_academico) — no crean
    | columnas ni tocan la estructura de la base de datos.
    */

    /**
     * Valida el arreglo de roles del formulario contra la matriz de
     * compatibilidad (Roles::mensajeIncompatibilidad) y devuelve la lista ya
     * depurada (sin vacíos ni duplicados). Si algo no es válido, redirige de
     * vuelta con el error en la clave "roles", igual que el resto de
     * validaciones de este trait.
     *
     * @param  array<int, mixed>  $rolesFormulario
     * @return array<int, string>
     */
    protected function validarRolesSolicitados(array $rolesFormulario, string $rolAncla): array
    {
        $roles = array_values(array_unique(array_filter(array_map('strval', $rolesFormulario))));

        // El rol ancla de la sección siempre debe quedar incluido, incluso si
        // el checkbox llegó deshabilitado y no se envió en el POST.
        if (! in_array($rolAncla, $roles, true)) {
            $roles[] = $rolAncla;
        }

        $mensaje = Roles::mensajeIncompatibilidad($roles);
        if ($mensaje !== null) {
            throw \Illuminate\Validation\ValidationException::withMessages(['roles' => $mensaje]);
        }

        return $roles;
    }

    /**
     * Crea, reactiva o inactiva los perfiles de los roles ADICIONALES al rol
     * ancla de la sección actual (por ejemplo, si se está en "Editar
     * instructor", el rol Instructor no se toca aquí: lo gestiona el flujo
     * propio de esa sección).
     *
     * @param  array<int, string>  $rolesSeleccionados
     */
    protected function sincronizarRolesAdicionales(Usuario $usuario, array $rolesSeleccionados, string $rolAncla): void
    {
        foreach ([Roles::COORDINADOR, Roles::INSTRUCTOR, Roles::APRENDIZ] as $rol) {
            if ($rol === $rolAncla) {
                continue;
            }

            $debeEstarActivo = in_array($rol, $rolesSeleccionados, true);

            match ($rol) {
                Roles::INSTRUCTOR  => $this->sincronizarPerfilInstructor($usuario, $debeEstarActivo),
                Roles::COORDINADOR => $this->sincronizarPerfilCoordinacion($usuario, $debeEstarActivo),
                Roles::APRENDIZ    => $this->sincronizarPerfilAprendiz($usuario, $debeEstarActivo),
            };

            $this->sincronizarPivoteRol($usuario, $rol, $debeEstarActivo);
        }
    }

    private function sincronizarPerfilInstructor(Usuario $usuario, bool $activo): void
    {
        $instructor = $usuario->instructor;

        if ($activo) {
            if ($instructor) {
                if ($instructor->estado_instructor !== 'activo') {
                    $instructor->update(['estado_instructor' => 'activo']);
                }

                return;
            }

            Instructor::create([
                'id_usuario'        => $usuario->id_usuario,
                'codigo_instructor' => $this->generarCodigoInstructorParaRolAdicional(),
                'estado_instructor' => 'activo',
            ]);

            return;
        }

        if ($instructor && $instructor->estado_instructor !== 'inactivo') {
            $instructor->update(['estado_instructor' => 'inactivo']);
        }
    }

    private function sincronizarPerfilCoordinacion(Usuario $usuario, bool $activo): void
    {
        $coordinacion = $usuario->coordinacion;

        if ($activo) {
            if ($coordinacion) {
                if ($coordinacion->estado_coordinacion !== 'activo') {
                    $coordinacion->update(['estado_coordinacion' => 'activo']);
                }

                return;
            }

            Coordinacion::create([
                'id_usuario'          => $usuario->id_usuario,
                'cargo'               => 'Coordinador',
                'estado_coordinacion' => 'activo',
            ]);

            return;
        }

        if ($coordinacion && $coordinacion->estado_coordinacion !== 'inactivo') {
            $coordinacion->update(['estado_coordinacion' => 'inactivo']);
        }
    }

    private function sincronizarPerfilAprendiz(Usuario $usuario, bool $activo): void
    {
        $aprendiz = $usuario->aprendiz;

        if ($activo) {
            if ($aprendiz) {
                if ($aprendiz->estado_academico !== 'en_formacion') {
                    $aprendiz->update(['estado_academico' => 'en_formacion']);
                }

                return;
            }

            Aprendiz::create([
                'id_usuario'                => $usuario->id_usuario,
                'correo_institucional'      => $usuario->correo,
                'correo_personal'           => $usuario->correo,
                'estado_academico'          => 'en_formacion',
                'tiene_apoyo_sostenimiento' => 0,
            ]);

            return;
        }

        // No hay una columna de "inactivo" genérica para el aprendiz: la más
        // cercana sin inventar estados nuevos es "cancelado" (deja de contar
        // como Aprendiz activo en Roles::disponiblesPara), igual que ya hace
        // el flujo normal de retiro académico.
        if ($aprendiz && $aprendiz->estado_academico !== 'cancelado') {
            $aprendiz->update(['estado_academico' => 'cancelado']);
        }
    }

    /**
     * Mantiene la tabla usuario_rol en sincronía con el estado de cada
     * perfil, reutilizando exactamente el mismo patrón de
     * crearUsuarioConRol() (fecha_asignacion + estado_asignacion).
     */
    private function sincronizarPivoteRol(Usuario $usuario, string $nombreRol, bool $activo): void
    {
        $idRol = Rol::where('nombre_rol', $nombreRol)->value('id_rol');
        if (! $idRol) {
            return;
        }

        $yaAsignado = $usuario->roles()->where('rol.id_rol', $idRol)->exists();

        if ($activo) {
            if ($yaAsignado) {
                $usuario->roles()->updateExistingPivot($idRol, ['estado_asignacion' => 'activa']);
            } else {
                $usuario->roles()->attach($idRol, ['fecha_asignacion' => now(), 'estado_asignacion' => 'activa']);
            }

            return;
        }

        if ($yaAsignado) {
            $usuario->roles()->updateExistingPivot($idRol, ['estado_asignacion' => 'inactiva']);
        }
    }

    /**
     * Código de instructor autogenerado cuando se agrega el rol Instructor
     * como rol ADICIONAL (no desde la sección de Instructores, que ya genera
     * el suyo con su propio formulario).
     */
    private function generarCodigoInstructorParaRolAdicional(): string
    {
        $maximo = Instructor::where('codigo_instructor', 'like', 'INS-%')
            ->get()
            ->map(fn (Instructor $i) => (int) preg_replace('/\D/', '', (string) $i->codigo_instructor))
            ->max();

        return 'INS-' . str_pad((string) (((int) $maximo) + 1), 3, '0', STR_PAD_LEFT);
    }
}
