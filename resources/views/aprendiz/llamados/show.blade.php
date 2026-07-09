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
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('aprendiz.llamados.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
            ← Volver a mis llamados
        </a>
        <div class="flex flex-wrap items-center gap-3">
            {{-- Exportar este llamado en específico (PDF / Excel / Word). --}}
            @include('reportes._botones', ['rutaBase' => 'aprendiz.llamados.detalle.export', 'rutaParams' => ['id' => $llamado->id_llamado]])
            <a href="{{ asset('formatos/F002-008-25-formato-llamado-de-atencion-V01.pdf') }}" download="F002-008-25-Formato-Llamado-de-Atencion-V01.pdf"
               class="inline-flex items-center gap-2 rounded-full border border-[#39A900] px-4 py-2 text-sm font-bold text-[#39A900] transition hover:bg-[#39A900]/10"
               title="Descargar el formato oficial de llamado de atención del SENA (F002-008-25 V01) en PDF">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                Formato oficial (PDF)
            </a>
        </div>
    </div>

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
            @include('llamados._pruebas_muestra')
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
