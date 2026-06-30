@extends('layouts.coordinador')

@section('titulo', 'Fichas')

@section('contenido')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Fichas de formación</h2>
        <p class="mt-1 text-sm text-gray-500">Instructor líder, instructores con intervención y aprendices de cada grupo.</p>
    </div>

    @forelse($fichas as $ficha)
        @php
            $lider = optional($ficha->instructorLider)->usuario;
            $instructores = $involucrados[$ficha->id_ficha] ?? collect();
        @endphp
        <div x-data="{ abierto: false }" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <button type="button" @click="abierto = !abierto" class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left transition hover:bg-gray-50">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/></svg>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ optional($ficha->programa)->nombre_programa ?? 'Programa' }}</p>
                        <p class="text-xs text-gray-500">Ficha {{ $ficha->numero_ficha }} · {{ ucfirst($ficha->modalidad) }} · {{ $ficha->matriculas->count() }} aprendices</p>
                        <p class="mt-0.5 text-xs font-semibold text-[#39A900]">
                            Instructor líder: {{ $lider ? trim($lider->nombres.' '.$lider->apellidos) : 'No asignado' }}
                        </p>
                    </div>
                </div>
                <svg class="h-5 w-5 shrink-0 text-gray-400 transition-transform duration-200" :class="abierto && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
            </button>

            <div x-show="abierto" x-cloak class="space-y-5 border-t border-gray-100 px-6 py-5">
                {{-- Instructores --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Instructores</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @if($lider)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#39A900]/10 px-3 py-1 text-xs font-semibold text-[#247200]">
                                ★ {{ trim($lider->nombres.' '.$lider->apellidos) }} (líder)
                            </span>
                        @endif
                        @forelse($instructores as $ins)
                            @php $iu = optional($ins)->usuario; @endphp
                            @if($iu && (!$lider || $ins->id_instructor !== optional($ficha->instructorLider)->id_instructor))
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                    {{ trim($iu->nombres.' '.$iu->apellidos) }}
                                </span>
                            @endif
                        @empty
                        @endforelse
                        @if(!$lider && $instructores->isEmpty())
                            <span class="text-xs text-gray-400">Sin instructores asignados.</span>
                        @endif
                    </div>
                </div>

                {{-- Aprendices --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Aprendices</p>
                    @if($ficha->matriculas->isEmpty())
                        <p class="mt-2 text-sm text-gray-400">Sin aprendices matriculados.</p>
                    @else
                        <ul class="mt-2 divide-y divide-gray-100">
                            @foreach($ficha->matriculas as $m)
                                @php $ap = $m->aprendiz; @endphp
                                @if($ap)
                                    <li class="flex items-center justify-between gap-3 py-2.5">
                                        <span class="text-sm text-gray-700">{{ optional($ap->usuario)->nombres }} {{ optional($ap->usuario)->apellidos }}</span>
                                        <a href="{{ route('coordinacion.aprendices.show', $ap->id_aprendiz) }}" class="text-sm font-semibold text-[#39A900] hover:underline">Ver hoja de vida</a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center text-gray-500 shadow-sm">
            <p class="text-sm">No hay fichas registradas.</p>
        </div>
    @endforelse
</div>
@endsection
