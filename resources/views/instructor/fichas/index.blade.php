@extends('layouts.instructor')

@section('titulo', 'Mis Fichas')

@section('contenido')
<div class="space-y-6" x-data="{ fichaSeleccionada: null }">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Mis Fichas</h2>
        <p class="mt-1 text-sm text-gray-500">Fichas en las que estás asignado. Selecciona una ficha para desplegar en el panel lateral todos los aprendices asociados a ella.</p>
    </div>

    @if($fichas->isEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center text-gray-500 shadow-sm">
            <p class="text-sm">Aún no estás asignado a ninguna ficha.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 lg:items-start">
            {{-- Listado de fichas (selección) --}}
            <div class="space-y-4">
                @foreach($fichas as $ficha)
                    @php
                        $lider = optional($ficha->instructorLider)->usuario;
                        $fb = match($ficha->estado_ficha) {
                            'en_ejecucion' => 'bg-[#39A900]/10 text-[#247200]',
                            'terminada' => 'bg-blue-100 text-blue-700',
                            'cancelada' => 'bg-red-100 text-red-700',
                            default => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm transition"
                         :class="fichaSeleccionada === {{ $ficha->id_ficha }} ? 'border-[#39A900] ring-2 ring-[#39A900]/20' : 'border-gray-200 hover:border-[#39A900]/40'">
                        <button type="button"
                                @click="fichaSeleccionada = (fichaSeleccionada === {{ $ficha->id_ficha }} ? null : {{ $ficha->id_ficha }})"
                                class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left transition hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900]">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
                                </span>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ optional($ficha->programa)->nombre_programa ?? 'Programa' }}</p>
                                    <p class="text-xs text-gray-500">Ficha {{ $ficha->numero_ficha }} · {{ $ficha->modalidad_label }} · {{ $ficha->matriculas->count() }} aprendices</p>
                                    <p class="mt-0.5 text-xs font-semibold text-[#39A900]">
                                        Instructor líder: {{ $lider ? trim($lider->nombres.' '.$lider->apellidos) : 'No asignado' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="hidden rounded-full px-3 py-1 text-xs font-semibold sm:inline {{ $fb }}">{{ str($ficha->estado_ficha)->replace('_',' ')->ucfirst() }}</span>
                                <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" :class="fichaSeleccionada === {{ $ficha->id_ficha }} && 'rotate-90 text-[#39A900]'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                            </div>
                        </button>
                        <div class="flex items-center justify-between gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-3">
                            <span class="text-xs font-semibold text-gray-400" x-text="fichaSeleccionada === {{ $ficha->id_ficha }} ? 'Aprendices desplegados en el panel' : 'Selecciona la ficha para ver sus aprendices'"></span>
                            <a href="{{ route('instructor.fichas.show', $ficha) }}" @click.stop
                               class="inline-flex items-center gap-1.5 rounded-full bg-[#39A900]/10 px-3 py-1.5 text-xs font-bold text-[#247200] transition hover:bg-[#39A900]/20">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h6m-6 4h6M9 8h6M5 3h11l4 4v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/></svg>
                                Historial disciplinario
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Panel adicional: aprendices de la ficha seleccionada --}}
            <div class="lg:sticky lg:top-4">
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/60 px-6 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Panel de aprendices</p>
                        <h3 class="mt-1 text-base font-bold text-gray-900">Aprendices de la ficha</h3>
                    </div>

                    {{-- Sin ficha seleccionada --}}
                    <div x-show="fichaSeleccionada === null" class="px-6 py-12 text-center">
                        <span class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#39A900]/10 text-[#39A900]">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm14 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        <p class="text-sm font-semibold text-gray-600">Selecciona una ficha</p>
                        <p class="mt-1 text-xs text-gray-400">Aquí se desplegarán todos los aprendices asociados a la ficha que elijas.</p>
                    </div>

                    @foreach($fichas as $ficha)
                        <div x-show="fichaSeleccionada === {{ $ficha->id_ficha }}" x-cloak>
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-6 py-3">
                                <p class="text-sm font-bold text-gray-900">Ficha {{ $ficha->numero_ficha }}</p>
                                <span class="rounded-full bg-[#39A900]/10 px-3 py-1 text-xs font-semibold text-[#247200]">{{ $ficha->matriculas->count() }} aprendices</span>
                            </div>
                            @if($ficha->matriculas->isEmpty())
                                <p class="px-6 py-8 text-center text-sm text-gray-400">Esta ficha no tiene aprendices matriculados.</p>
                            @else
                                <ul class="max-h-[28rem] divide-y divide-gray-100 overflow-y-auto">
                                    @foreach($ficha->matriculas as $m)
                                        @php $ap = $m->aprendiz; @endphp
                                        @if($ap)
                                            <li class="flex flex-wrap items-center justify-between gap-3 px-6 py-3.5">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-600">
                                                        {{ $ap->usuario?->iniciales() ?? 'A' }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-gray-900">{{ optional($ap->usuario)->nombres }} {{ optional($ap->usuario)->apellidos }}</p>
                                                        <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_',' ', $ap->estado_academico)) }}</p>
                                                    </div>
                                                </div>
                                                <a href="{{ route('instructor.aprendices.show', $ap->id_aprendiz) }}"
                                                   class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-[#39A900] px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#247200]">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    Ver información completa
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
