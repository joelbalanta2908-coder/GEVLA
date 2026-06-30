<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ReglamentoAprendiz;
use App\Models\ReglamentoArticulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReglamentoController extends Controller
{
    /**
     * Determina el layout base según el rol del usuario autenticado.
     */
    private function getLayoutName($usuario): string
    {
        return match (true) {
            $usuario->tieneRol('Coordinador') => 'layouts.coordinador',
            $usuario->tieneRol('Instructor')  => 'layouts.instructor',
            default => 'layouts.aprendiz',
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
                $q->with('paragrafos')->orderBy('id_articulo');
                if ($buscar !== '') {
                    $q->where(function ($sub) use ($buscar) {
                        $sub->where('titulo', 'like', "%{$buscar}%")
                            ->orWhere('numero_articulo', 'like', "%{$buscar}%")
                            ->orWhere('contenido', 'like', "%{$buscar}%");
                    });
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
