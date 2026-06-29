@extends('layouts.aprendiz')

@section('titulo', 'Detalle del Llamado')

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
    <a href="{{ route('aprendiz.llamados.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver a mis llamados
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-[#00324D]">{{ $llamado->asunto }}</h2>
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
                <dt class="text-xs font-medium uppercase text-gray-400">Instructor que reporta</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $llamado->instructor->usuario->nombres }} {{ $llamado->instructor->usuario->apellidos }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Tipo de llamado</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ str($llamado->tipo_llamado)->replace('_',' ')->ucfirst() }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Categoría</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($llamado->categoria) }}</dd>
            </div>
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

    @if($llamado->observaciones)
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-[#00324D] mb-2">Observaciones de Coordinación</h3>
            <p class="text-sm text-gray-700">{{ $llamado->observaciones }}</p>
        </div>
    @endif
</div>
@endsection
