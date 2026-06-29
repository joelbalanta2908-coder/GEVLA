<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LlamadoAtencion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InstructorController extends Controller
{
    /**
     * Muestra el panel de control del Instructor.
     */
    public function dashboard(): View
    {
        $user = Auth::user();
        
        $instructor = $user->instructor;
        
        if (!$instructor) {
            abort(403, 'Acceso denegado: El usuario no es un instructor.');
        }

        // Obtener llamados reportados por este instructor
        $llamados = LlamadoAtencion::with('aprendiz.usuario')
                        ->where('id_instructor', $instructor->id_instructor)
                        ->orderByDesc('fecha_llamado')
                        ->get();

        return view('dashboards.instructor', compact('instructor', 'llamados'));
    }
}
