@extends('layouts.instructor')

@section('titulo', 'Detalle del Reporte')

@section('contenido')
@php
    $estadoBadge = match($llamado->estado_llamado) {
        'registrado'  => 'bg-gray-100 text-gray-600',
        'en_revision' => 'bg-amber-100 text-amber-700',
        'notificado'  => 'bg-blue-100 text-blue-700',
        'cerrado'     => 'bg-green-100 text-green-700',
        'cancelado'   => 'bg-red-100 text-red-700',
        default       => 'bg-gray-100 text-gray-600',
    };
@endphp

<div class="space-y-6">
    <a href="{{ route('instructor.llamados.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver a mis reportes
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-[#00324D]">{{ $llamado->asunto }}</h2>
                    @if($llamado->estado_llamado === 'registrado')
                        <div class="flex gap-2">
                            <a href="{{ route('instructor.llamados.edit', $llamado->id_llamado) }}" class="rounded bg-amber-50 p-1.5 text-amber-600 hover:bg-amber-100 transition" title="Editar">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>
                            <form method="POST" action="{{ route('instructor.llamados.destroy', $llamado->id_llamado) }}" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este reporte?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded bg-red-50 p-1.5 text-red-600 hover:bg-red-100 transition" title="Eliminar">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Reportado el {{ \Carbon\Carbon::parse($llamado->fecha_llamado)->translatedFormat('d \d\e F \d\e Y') }}
                </p>
            </div>
            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-medium {{ $estadoBadge }}">
                {{ str($llamado->estado_llamado)->replace('_',' ')->ucfirst() }}
            </span>
        </div>

        <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Aprendiz reportado</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $llamado->aprendiz->usuario->nombres }} {{ $llamado->aprendiz->usuario->apellidos }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Tipo de llamado</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ str($llamado->tipo_llamado)->replace('_',' ')->ucfirst() }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Categoría</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($llamado->categoria) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Calificación de la falta</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $llamado->calificacion_label }}</dd>
            </div>
            @if($llamado->articulo)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase text-gray-400">Artículo del reglamento infringido</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $llamado->articulo->numero_articulo }} — {{ $llamado->articulo->titulo }}</dd>
                </div>
            @endif
        </dl>

        <div class="mt-6 space-y-4">
            <div>
                <h3 class="text-xs font-medium uppercase text-gray-400">Descripción de los hechos</h3>
                <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ $llamado->descripcion_hechos }}</p>
            </div>
            @if($llamado->pruebas_aportadas)
                <div>
                    <h3 class="text-xs font-medium uppercase text-gray-400">Pruebas aportadas</h3>
                    <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ $llamado->pruebas_aportadas }}</p>
                </div>
            @endif
        </div>
    </div>

    @if($llamado->observaciones || $llamado->estado_llamado !== 'registrado')
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-[#00324D] mb-2">Retroalimentación de Coordinación</h3>
            @if($llamado->observaciones)
                <p class="text-sm text-gray-700">{{ $llamado->observaciones }}</p>
            @else
                <p class="text-sm text-gray-500 italic">El caso se encuentra bajo análisis de coordinación, aún no se han registrado observaciones formales.</p>
            @endif
        </div>
    @endif
</div>
@endsection
