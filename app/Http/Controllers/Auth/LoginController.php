<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoginController extends Controller
{

/**
     * Muestra el formulario de inicio de sesion.
     */
    public function showLoginForm(): View
    {
        // Solo debe retornar la vista del login
        return view('auth.login');

        /* * Si quieres dejar los otros como recordatorio, 
         * debes comentarlos para que PHP no intente leerlos:
         *
         * return view('dashboards.coordinador.index');
         * return view('dashboards.coordinador.llamados.index');
         * return view('dashboards.coordinador.llamados.show');
         * return view('dashboards.coordinador.actas.index');
         * return view('dashboards.coordinador.actas.create');
         * return view('dashboards.coordinador.procesos.index');
         * return view('dashboards.coordinador.procesos.show');
         */
    }

    /**
     * Procesa el intento de inicio de sesion.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'string', Rule::in(['Aprendiz', 'Instructor', 'Coordinador'])],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identifier = trim((string) $request->input('username'));
        $password = (string) $request->input('password');
        $role = (string) $request->input('role');

        $usuario = $this->buscarUsuarioPorIdentificador($identifier);

        if (!$usuario) {
            return $this->rechazarLogin($request, 'Las credenciales son incorrectas o tu cuenta no esta activa.');
        }

        if ($usuario->estado_usuario === 'bloqueado') {
            return $this->rechazarLogin($request, 'Tu cuenta ha sido bloqueada. Contacta al administrador.');
        }

        if ($usuario->estado_usuario === 'inactivo') {
            return $this->rechazarLogin($request, 'Tu cuenta esta inactiva.');
        }

        if (!$this->usuarioTieneRolOperativo($usuario, $role)) {
            return $this->rechazarLogin($request, "El usuario no tiene el rol {$role} activo.");
        }

        if (!$this->passwordValida($usuario, $password)) {
            return $this->rechazarLogin($request, 'Las credenciales son incorrectas o tu cuenta no esta activa.');
        }

        Auth::login($usuario, $request->boolean('remember'));
        $usuario->update(['ultimo_acceso' => now()]);

        $request->session()->regenerate();

        return redirect()->intended($this->obtenerRutaPorRol($role));
    }

    /**
     * Cierra la sesion del usuario.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'Has cerrado sesion correctamente.');
    }

    private function buscarUsuarioPorIdentificador(string $identifier): ?Usuario
    {
        return Usuario::query()
            ->where('correo', $identifier)
            ->orWhere('username', $identifier)
            ->orWhere('numero_documento', $identifier)
            ->orWhereIn('id_usuario', function ($query) use ($identifier) {
                $query->select('id_usuario')
                    ->from('aprendiz')
                    ->where('correo_personal', $identifier)
                    ->orWhere('correo_institucional', $identifier);
            })
            ->first();
    }

    private function usuarioTieneRolOperativo(Usuario $usuario, string $role): bool
    {
        if ($usuario->tieneRol($role)) {
            return true;
        }

        return match ($role) {
            'Aprendiz' => DB::table('aprendiz')
                ->where('id_usuario', $usuario->id_usuario)
                ->where('estado_academico', 'en_formacion')
                ->exists(),
            'Instructor' => DB::table('instructor')
                ->where('id_usuario', $usuario->id_usuario)
                ->where('estado_instructor', 'activo')
                ->exists(),
            'Coordinador' => DB::table('coordinacion')
                ->where('id_usuario', $usuario->id_usuario)
                ->where('estado_coordinacion', 'activo')
                ->exists(),
            default => false,
        };
    }

    private function passwordValida(Usuario $usuario, string $password): bool
    {
        $hash = (string) $usuario->password_hash;

        // El dump trae hashes de ejemplo como "$2b$10$abc..."; para no tocar la BD,
        // se permite el numero de documento como clave temporal solo en esos registros.
        if ($this->hashEsPlaceholder($hash)) {
            return hash_equals((string) $usuario->numero_documento, $password);
        }

        return Hash::check($password, $hash);
    }

    private function hashEsPlaceholder(string $hash): bool
    {
        return str_contains($hash, '...')
            || preg_match('/^\$2[aby]\$\d{2}\$.{53}$/', $hash) !== 1;
    }

    private function rechazarLogin(Request $request, string $message): RedirectResponse
    {
        return back()
            ->withInput($request->only('username', 'role'))
            ->withErrors(['login' => $message]);
    }

    /**
     * Determina la ruta de redireccion segun el rol seleccionado.
     */
    private function obtenerRutaPorRol(string $rol): string
    {
        return match ($rol) {
            'Aprendiz' => '/aprendiz/dashboard',
            'Coordinador' => '/coordinacion/dashboard',
            'Instructor' => '/instructor/dashboard',
            default => '/login',
        };
    }
}
