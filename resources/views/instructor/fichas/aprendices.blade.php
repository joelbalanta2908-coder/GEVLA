@extends('layouts.instructor')

@section('titulo', 'Aprendices · Ficha ' . $ficha->numero_ficha)

@section('contenido')
@php
    $lider = optional($ficha->instructorLider)->usuario;
    $fb = match($ficha->estado_ficha) {
        'en_ejecucion' => 'bg-[#39A900]/10 text-[#247200]',
        'terminada' => 'bg-blue-100 text-blue-700',
        'cancelada' => 'bg-red-100 text-red-700',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="space-y-6">
    {{-- Encabezado de la ficha --}}
    <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-4">
            <a href="{{ route('instructor.fichas.index') }}" title="Volver a Mis Fichas"
               class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900] ring-1 ring-[#39A900]/15 transition hover:bg-[#39A900]/20">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
            </a>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-[#39A900]">Aprendices de la ficha</p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">Ficha {{ $ficha->numero_ficha }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ optional($ficha->programa)->nombre_programa ?? 'Programa' }} · {{ $ficha->modalidad_label }}</p>
                <p class="mt-0.5 text-xs font-semibold text-[#39A900]">
                    Instructor líder: {{ $lider ? trim($lider->nombres.' '.$lider->apellidos) : 'No asignado' }}
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $fb }}">{{ str($ficha->estado_ficha)->replace('_',' ')->ucfirst() }}</span>
            <a href="{{ route('instructor.fichas.show', $ficha) }}"
               class="inline-flex items-center gap-1.5 rounded-full bg-[#39A900]/10 px-3 py-1.5 text-xs font-bold text-[#247200] transition hover:bg-[#39A900]/20">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h6m-6 4h6M9 8h6M5 3h11l4 4v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/></svg>
                Historial disciplinario
            </a>
        </div>
    </div>

    {{-- Listado de aprendices asociados a la ficha --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 bg-gray-50/60 px-6 py-4">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Aprendices asociados</p>
            <span class="rounded-full bg-[#39A900]/10 px-3 py-1 text-xs font-semibold text-[#247200]">{{ $ficha->matriculas->count() }} aprendices</span>
        </div>

        @if($ficha->matriculas->isEmpty())
            <p class="px-6 py-10 text-center text-sm text-gray-400">Esta ficha no tiene aprendices matriculados.</p>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach($ficha->matriculas->sortBy(fn ($m) => optional(optional($m->aprendiz)->usuario)->apellidos) as $m)
                    @php $ap = $m->aprendiz; @endphp
                    @if($ap)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-600">
                                    {{ $ap->usuario?->iniciales() ?? 'A' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-900">{{ optional($ap->usuario)->nombres }} {{ optional($ap->usuario)->apellidos }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ ucfirst(str_replace('_',' ', $ap->estado_academico)) }}
                                        @if($m->estado_matricula !== 'activa')
                                            · <span class="font-semibold">Matrícula {{ $m->estado_matricula }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('instructor.aprendices.show', $ap->id_aprendiz) }}"
                               class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-[#39A900] px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#247200]">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Ver información completa
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
