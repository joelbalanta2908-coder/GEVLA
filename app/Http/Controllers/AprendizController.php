<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LlamadoAtencion;
use App\Models\ActaCoordinacion;
use App\Models\FirmaLlamado;
use App\Models\Notificacion;
use App\Models\ProcesoDisciplinario;
use App\Support\DocumentoLlamado;
use App\Support\Firmas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AprendizController extends Controller
{
    /**
     * Muestra el panel de control del Aprendiz.
     */
    public function dashboard(): View
    {
        $user = Auth::user();
        
        // Asumiendo que el usuario tiene relación con aprendiz
        $aprendiz = $user->aprendiz;
        
        if (!$aprendiz) {
            abort(403, 'Acceso denegado: El usuario no es un aprendiz.');
        }

        // Obtener resúmenes
        $llamados = LlamadoAtencion::where('id_aprendiz', $aprendiz->id_aprendiz)
                        ->orderByDesc('fecha_llamado')
                        ->get();
                        
        $actas = ActaCoordinacion::where('id_aprendiz', $aprendiz->id_aprendiz)
                        ->orderByDesc('fecha_expedicion')
                        ->get();
                        
        $procesos = ProcesoDisciplinario::where('id_aprendiz', $aprendiz->id_aprendiz)
                        ->orderByDesc('fecha_inicio')
                        ->get();

        return view('dashboards.aprendiz', compact('aprendiz', 'llamados', 'actas', 'procesos'));
    }

    /**
     * Helper para obtener el aprendiz actual o abortar si no lo es.
     */
    private function getAprendiz()
    {
        $aprendiz = Auth::user()->aprendiz;
        if (!$aprendiz) abort(403, 'Acceso denegado: El usuario no es un aprendiz.');
        return $aprendiz;
    }

    // --- LLAMADOS DE ATENCIÓN ---
    public function llamados(): View
    {
        $aprendiz = $this->getAprendiz();
        $llamados = LlamadoAtencion::with('instructor.usuario')
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->orderByDesc('fecha_llamado')
            ->paginate(15);
        return view('aprendiz.llamados.index', compact('llamados'));
    }

    public function showLlamado(string $id): View
    {
        $aprendiz = $this->getAprendiz();
        $llamado = LlamadoAtencion::with(['instructor.usuario', 'faltas', 'coordinacion'])
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->findOrFail($id);

        // Marcar como recibidas las notificaciones relacionadas con este llamado
        \App\Models\Notificacion::where('id_llamado', $llamado->id_llamado)
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->where('estado_notificacion', 'enviada')
            ->update(['estado_notificacion' => 'recibida']);

        // Estado de la firma del aprendiz sobre este llamado (módulo de firmas).
        $firmaAprendiz  = FirmaLlamado::de((int) $llamado->id_llamado, FirmaLlamado::ROL_APRENDIZ);
        $puedeFirmar    = FirmaLlamado::moduloInstalado() && $firmaAprendiz === null;
        $tieneFirmaImg  = Firmas::tiene(Auth::user());

        return view('aprendiz.llamados.show', compact('llamado', 'firmaAprendiz', 'puedeFirmar', 'tieneFirmaImg'));
    }

    /**
     * El aprendiz firma (acepta) el llamado de atención: su firma registrada
     * en Mi Perfil queda incrustada en el documento y el acto se traza con
     * fecha y hora en firma_llamado.
     */
    public function firmarLlamado(string $id): RedirectResponse
    {
        $aprendiz = $this->getAprendiz();
        $llamado = LlamadoAtencion::where('id_aprendiz', $aprendiz->id_aprendiz)->findOrFail($id);

        if (! FirmaLlamado::moduloInstalado()) {
            return back()->withErrors([
                'error' => 'El módulo de firmas no está instalado: importa database/sql/modulo_firmas.sql en la base de datos.',
            ]);
        }

        if (! Firmas::tiene(Auth::user())) {
            return back()->withErrors([
                'error' => 'No puedes firmar este llamado: primero debes registrar tu firma desde Mi Perfil (sección Firma).',
            ]);
        }

        FirmaLlamado::firmar((int) $llamado->id_llamado, (int) Auth::id(), FirmaLlamado::ROL_APRENDIZ);

        return back()->with('success', 'Has firmado el llamado de atención. Tu firma quedó registrada con fecha y hora.');
    }

    /**
     * Genera el documento del llamado (formato F002-008-25, imprimible como
     * PDF) con las firmas registradas hasta el momento.
     */
    public function documentoLlamado(string $id)
    {
        $aprendiz = $this->getAprendiz();
        $llamado = LlamadoAtencion::where('id_aprendiz', $aprendiz->id_aprendiz)->findOrFail($id);

        return DocumentoLlamado::render($llamado);
    }

    // --- ACTAS DE COORDINACIÓN ---
    public function actas(): View
    {
        $aprendiz = $this->getAprendiz();
        $actas = ActaCoordinacion::with('falta')
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->orderByDesc('fecha_expedicion')
            ->paginate(15);
        return view('aprendiz.actas.index', compact('actas'));
    }

    public function showActa(string $id): View
    {
        $aprendiz = $this->getAprendiz();
        $acta = ActaCoordinacion::with(['falta', 'procesoDisciplinario'])
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->findOrFail($id);
        return view('aprendiz.actas.show', compact('acta'));
    }

    // --- PROCESOS DISCIPLINARIOS ---
    public function procesos(): View
    {
        $aprendiz = $this->getAprendiz();
        $procesos = ProcesoDisciplinario::where('id_aprendiz', $aprendiz->id_aprendiz)
            ->orderByDesc('fecha_inicio')
            ->paginate(15);
        return view('aprendiz.procesos.index', compact('procesos'));
    }

    public function showProceso(string $id): View
    {
        $aprendiz = $this->getAprendiz();
        $proceso = ProcesoDisciplinario::with(['historial', 'llamadoAtencion'])
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->findOrFail($id);
        return view('aprendiz.procesos.show', compact('proceso'));
    }

    // --- NOTIFICACIONES ---
    public function notificaciones(): View
    {
        $aprendiz = $this->getAprendiz();
        // Marcar como recibidas las notificaciones enviadas al revisar la lista
        \App\Models\Notificacion::where('id_aprendiz', $aprendiz->id_aprendiz)
            ->where('estado_notificacion', 'enviada')
            ->update(['estado_notificacion' => 'recibida']);
        $notificaciones = Notificacion::with(['llamado', 'acta'])
            ->where('id_aprendiz', $aprendiz->id_aprendiz)
            ->orderByDesc('fecha_envio')
            ->paginate(15);
        return view('aprendiz.notificaciones.index', compact('notificaciones'));
    }
}
