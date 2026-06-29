@extends('layouts.aprendiz')

@section('titulo', 'Detalle de Acta')

@section('contenido')
@php
    $estadoBadge = match($acta->estado_acta) {
        'borrador'  => 'bg-gray-100 text-gray-600',
        'firmada'   => 'bg-green-100 text-green-700',
        'anulada'   => 'bg-red-100 text-red-700',
        default     => 'bg-gray-100 text-gray-600',
    };
@endphp

<div class="space-y-6">
    <a href="{{ route('aprendiz.actas.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver a mis actas
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-[#00324D]">Acta {{ $acta->codigo_acta ?? '#' . $acta->id_acta }}</h2>
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
                <dt class="text-xs font-medium uppercase text-gray-400">Tipo de Acta</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ ucfirst($acta->tipo_acta) }}</dd>
            </div>
        </dl>

        <div class="mt-6 space-y-4">
            <div>
                <h3 class="text-xs font-medium uppercase text-gray-400">Contenido del Acta</h3>
                <div class="mt-2 rounded-lg bg-gray-50 p-4 text-sm text-gray-700 whitespace-pre-wrap font-mono">{{ $acta->contenido }}</div>
            </div>
            @if($acta->compromisos_aprendiz)
                <div>
                    <h3 class="text-xs font-medium uppercase text-gray-400">Compromisos del Aprendiz</h3>
                    <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ $acta->compromisos_aprendiz }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
