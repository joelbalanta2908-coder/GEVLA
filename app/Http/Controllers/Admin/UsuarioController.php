<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Administración de cuentas de usuario (rol Administrador).
 *
 * Permite listar los usuarios y activar / desactivar / bloquear sus cuentas.
 * El estado se valida en backend y controla el acceso al iniciar sesión
 * (LoginController rechaza cuentas inactivas o bloqueadas).
 */
class UsuarioController extends Controller
{
    /**
     * Estados posibles de una cuenta (enum de `usuario.estado_usuario`).
     *
     * @var array<string, string>
     */
    private const ESTADOS = [
        'activo'    => 'Activa',
        'inactivo'  => 'Inactiva',
        'bloqueado' => 'Bloqueada',
    ];

    public function index(Request $request): View
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $estado = $request->input('estado');

        $usuarios = Usuario::query()
            ->with('roles')
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('correo', 'like', "%{$buscar}%")
                    ->orWhere('numero_documento', 'like', "%{$buscar}%")
                    ->orWhere('username', 'like', "%{$buscar}%");
            })
            ->when($estado, fn ($q) => $q->where('estado_usuario', $estado))
            ->orderBy('nombres')
            ->paginate(15)
            ->withQueryString();

        $estados = self::ESTADOS;

        return view('admin.usuarios.index', compact('usuarios', 'estados', 'buscar', 'estado'));
    }

    /**
     * Cambia el estado de la cuenta (activa / inactiva / bloqueada). No permite
     * que el administrador modifique su propia cuenta para no auto-bloquearse.
     */
    public function actualizarEstado(Request $request, Usuario $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'estado_usuario' => ['required', Rule::in(array_keys(self::ESTADOS))],
        ]);

        if ((int) $usuario->id_usuario === (int) Auth::id()) {
            return back()->withErrors(['error' => 'No puedes cambiar el estado de tu propia cuenta.']);
        }

        $usuario->update(['estado_usuario' => $validated['estado_usuario']]);

        return back()->with('success', 'Estado de la cuenta actualizado a «' . self::ESTADOS[$validated['estado_usuario']] . '».');
    }
}
