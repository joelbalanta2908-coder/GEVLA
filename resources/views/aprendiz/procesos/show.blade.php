@extends('layouts.aprendiz')

@section('titulo', 'Detalle de Proceso')

@section('contenido')
@php
    $estadoBadge = match($proceso->estado_proceso) {
        'activo'    => 'bg-amber-100 text-amber-700',
        'suspendido'=> 'bg-gray-100 text-gray-700',
        'cerrado'   => 'bg-green-100 text-green-700',
        'apelacion' => 'bg-blue-100 text-blue-700',
        default     => 'bg-gray-100 text-gray-600',
    };
@endphp

<div class="space-y-6">
    <a href="{{ route('aprendiz.procesos.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver a mis procesos
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-[#00324D]">Proceso Disciplinario #{{ $proceso->id_proceso }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Iniciado el {{ \Carbon\Carbon::parse($proceso->fecha_inicio)->translatedFormat('d \d\e F \d\e Y') }}
                </p>
            </div>
            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-medium {{ $estadoBadge }}">
                {{ ucfirst($proceso->estado_proceso) }}
            </span>
        </div>

        <div class="mt-6 space-y-4 border-t border-gray-100 pt-6">
            <div>
                <h3 class="text-xs font-medium uppercase text-gray-400">Decisión Final</h3>
                <p class="mt-1 text-sm text-gray-700 font-semibold">{{ $proceso->decision_final ?? 'En curso, a la espera de resolución.' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
