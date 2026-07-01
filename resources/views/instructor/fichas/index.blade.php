@extends('layouts.instructor')

@section('titulo', 'Mis Fichas')

@section('contenido')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Fichas a cargo</h2>
        <p class="mt-1 text-sm text-gray-500">Grupos de formación que lideras y sus aprendices.</p>
    </div>

    @forelse($fichas as $ficha)
        <div x-data="{ abierto: false }" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <button type="button" @click="abierto = !abierto" class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left transition hover:bg-gray-50">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ optional($ficha->programa)->nombre_programa ?? 'Programa' }}</p>
                        <p class="text-xs text-gray-500">Ficha {{ $ficha->numero_ficha }} · {{ ucfirst($ficha->modalidad) }} · {{ $ficha->matriculas->count() }} aprendices</p>
                        @php $lider = optional($ficha->instructorLider)->usuario; @endphp
                        <p class="mt-0.5 text-xs font-semibold text-[#39A900]">
                            Instructor líder: {{ $lider ? trim($lider->nombres.' '.$lider->apellidos) : 'No asignado' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @php
                        $fb = match($ficha->estado_ficha) {
                            'en_ejecucion' => 'bg-[#39A900]/10 text-[#247200]',
                            'terminada' => 'bg-blue-100 text-blue-700',
                            'cancelada' => 'bg-red-100 text-red-700',
                            default => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <span class="hidden rounded-full px-3 py-1 text-xs font-semibold sm:inline {{ $fb }}">{{ str($ficha->estado_ficha)->replace('_',' ')->ucfirst() }}</span>
                    <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" :class="abierto && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </div>
            </button>

            <div x-show="abierto" x-cloak class="border-t border-gray-100">
                @if($ficha->matriculas->isEmpty())
                    <p class="px-6 py-6 text-center text-sm text-gray-400">Esta ficha no tiene aprendices matriculados.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($ficha->matriculas as $m)
                            @php $ap = $m->aprendiz; @endphp
                            @if($ap)
                                <li class="flex items-center justify-between gap-3 px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-600">
                                            {{ $ap->usuario?->iniciales() ?? 'A' }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ optional($ap->usuario)->nombres }} {{ optional($ap->usuario)->apellidos }}</p>
                                            <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_',' ', $ap->estado_academico)) }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('instructor.aprendices.show', $ap->id_aprendiz) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-[#39A900] hover:text-[#247200]">
                                        Ver información →
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center text-gray-500 shadow-sm">
            <p class="text-sm">Aún no tienes fichas asignadas como instructor líder.</p>
        </div>
    @endforelse
</div>
@endsection
