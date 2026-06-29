@extends('layouts.coordinador')

@section('titulo', 'Detalle del acta')

@section('contenido')
@php
    $estadoBadge = match($acta->estado_acta) {
        'expedido'   => 'bg-blue-100 text-blue-700',
        'notificado' => 'bg-amber-100 text-amber-700',
        'firme'      => 'bg-green-100 text-green-700',
        default      => 'bg-gray-100 text-gray-600',
    };
@endphp

<div class="space-y-6">
    <a href="{{ route('coordinacion.actas.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver a actas
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-[#00324D]">Acta {{ $acta->numero_acta }}</h2>
                    <div class="flex gap-2">
                        <a href="{{ route('coordinacion.actas.edit', $acta->id_acta) }}" class="rounded bg-amber-50 p-1.5 text-amber-600 hover:bg-amber-100 transition" title="Editar">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </a>
                        <form method="POST" action="{{ route('coordinacion.actas.destroy', $acta->id_acta) }}" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta acta?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded bg-red-50 p-1.5 text-red-600 hover:bg-red-100 transition" title="Eliminar">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                    </div>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Expedida el {{ \Carbon\Carbon::parse($acta->fecha_expedicion)->translatedFormat('d \d\e F \d\e Y') }}
                </p>
            </div>
            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-medium {{ $estadoBadge }}">
                {{ ucfirst($acta->estado_acta) }}
            </span>
        </div>

        <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Aprendiz</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $acta->aprendiz->usuario->nombres }} {{ $acta->aprendiz->usuario->apellidos }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Tipo de acta</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ str($acta->tipo_acta)->replace('_',' ')->ucfirst() }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Falta relacionada</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $acta->falta->principio_valor_infringido ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Calificación de la falta</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ str($acta->falta->calificacion_falta ?? '—')->replace('_',' ')->ucfirst() }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Notificación personal</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $acta->fecha_notificacion_personal ? \Carbon\Carbon::parse($acta->fecha_notificacion_personal)->format('d/m/Y') : '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Fecha de firmeza</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $acta->fecha_firmeza ? \Carbon\Carbon::parse($acta->fecha_firmeza)->format('d/m/Y') : '—' }}
                </dd>
            </div>
            @if($acta->meses_inhabilitacion)
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Meses de inhabilitación</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $acta->meses_inhabilitacion }}</dd>
                </div>
            @endif
        </dl>

        @if($acta->sancion_descripcion)
            <div class="mt-6">
                <h3 class="text-xs font-medium uppercase text-gray-400">Descripción de la sanción</h3>
                <p class="mt-1 text-sm text-gray-700">{{ $acta->sancion_descripcion }}</p>
            </div>
        @endif
    </div>

    @if($acta->procesoDisciplinario)
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900">Proceso disciplinario asociado</h3>
            <p class="mt-2 text-sm text-gray-600">
                Etapa actual: {{ str($acta->procesoDisciplinario->etapa_actual)->replace('_',' ')->ucfirst() }}
                · Estado: {{ ucfirst($acta->procesoDisciplinario->estado_proceso) }}
            </p>
            <a href="{{ route('coordinacion.procesos.show', $acta->procesoDisciplinario->id_proceso) }}"
               class="mt-3 inline-block text-sm font-medium text-[#39A900] hover:underline">
                Ver proceso →
            </a>
        </div>
    @endif
</div>
@endsection
