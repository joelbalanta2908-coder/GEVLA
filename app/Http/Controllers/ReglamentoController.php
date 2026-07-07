<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ReglamentoAprendiz;
use App\Models\ReglamentoArticulo;
use App\Support\Busqueda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReglamentoController extends Controller
{
    /**
     * Determina el layout base según el ROL ACTIVO de la sesión (el mismo que
     * usa todo el sistema), no según un recalculo propio: así la vista del
     * reglamento conserva el panel desde el que se abrió, incluso para
     * usuarios con varios roles.
     */
    private function getLayoutName($usuario): string
    {
        $rol = session('rol_activo') ?? \App\Support\Roles::porDefecto($usuario);

        return match ($rol) {
            \App\Support\Roles::COORDINADOR => 'layouts.coordinador',
            \App\Support\Roles::INSTRUCTOR  => 'layouts.instructor',
            default                          => 'layouts.aprendiz',
        };
    }

    /**
     * Muestra el reglamento del aprendiz: capítulos, artículos y parágrafos.
     * Permite buscar por texto y filtrar por calificación de falta.
     */
    public function index(Request $request): View
    {
        $usuario = Auth::user();
        $layout = $this->getLayoutName($usuario);

        $buscar = trim((string) $request->input('buscar', ''));
        $calificacion = $request->input('calificacion');

        $reglamento = ReglamentoAprendiz::query()->orderBy('id_reglamento')->first();

        $capitulos = \App\Models\ReglamentoCapitulo::with([
            'articulos' => function ($q) use ($buscar, $calificacion) {
                // Orden del documento: número de artículo y luego sus faltas #n.
                $q->with('paragrafos')
                    ->orderByRaw('CAST(SUBSTRING(numero_articulo, 6) AS UNSIGNED)')
                    ->orderByRaw("CAST(SUBSTRING_INDEX(numero_articulo, '#', -1) AS UNSIGNED)");
                // Búsqueda con inferencia: cada palabra debe coincidir parcialmente
                // con el título, el número o el contenido del artículo, sin exigir
                // el texto exacto (igual que el resto de buscadores del sistema).
                if ($buscar !== '') {
                    foreach (Busqueda::tokens($buscar) as $token) {
                        $q->where(function ($sub) use ($token) {
                            $sub->where('titulo', 'like', "%{$token}%")
                                ->orWhere('numero_articulo', 'like', "%{$token}%")
                                ->orWhere('contenido', 'like', "%{$token}%");
                        });
                    }
                }
                if ($calificacion) {
                    $q->where('calificacion', $calificacion);
                }
            },
        ])->orderBy('id_capitulo')->get();

        // Si hay filtros, ocultamos los capítulos que quedaron sin artículos.
        if ($buscar !== '' || $calificacion) {
            $capitulos = $capitulos->filter(fn ($cap) => $cap->articulos->isNotEmpty())->values();
        }

        $calificaciones = \App\Models\LlamadoAtencion::calificaciones();
        $totalArticulos = ReglamentoArticulo::count();

        return view('reglamento.index', compact(
            'layout',
            'reglamento',
            'capitulos',
            'buscar',
            'calificacion',
            'calificaciones',
            'totalArticulos'
        ));
    }
}
