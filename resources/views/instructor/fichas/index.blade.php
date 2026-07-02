@extends('layouts.instructor')

@section('titulo', 'Mis Fichas')

@section('contenido')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Mis Fichas</h2>
        <p class="mt-1 text-sm text-gray-500">Fichas en las que estás asignado. Selecciona una ficha para desplegar todos los aprendices asociados a ella.</p>
    </div>

    @forelse($fichas as $ficha)
        @php
            $lider = optional($ficha->instructorLider)->usuario;
            $fb = match($ficha->estado_ficha) {
                'en_ejecucion' => 'bg-[#39A900]/10 text-[#247200]',
                'terminada' => 'bg-blue-100 text-blue-700',
                'cancelada' => 'bg-red-100 text-red-700',
                default => 'bg-slate-100 text-slate-600',
            };
        @endphp
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:border-[#39A900]/40 hover:shadow-md">
            {{-- Al seleccionar la ficha se abre la vista con sus aprendices --}}
            <a href="{{ route('instructor.fichas.aprendices', $ficha) }}" class="flex w-full items-center justify-between gap-4 px-6 py-4 transition hover:bg-gray-50">
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
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                </div>
            </a>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-3">
                <a href="{{ route('instructor.fichas.aprendices', $ficha) }}"
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-[#39A900] transition hover:text-[#247200]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm14 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Ver aprendices de la ficha
                </a>
                <a href="{{ route('instructor.fichas.show', $ficha) }}"
                   class="inline-flex items-center gap-1.5 rounded-full bg-[#39A900]/10 px-3 py-1.5 text-xs font-bold text-[#247200] transition hover:bg-[#39A900]/20">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h6m-6 4h6M9 8h6M5 3h11l4 4v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/></svg>
                    Historial disciplinario
                </a>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center text-gray-500 shadow-sm">
            <p class="text-sm">Aún no estás asignado a ninguna ficha.</p>
        </div>
    @endforelse
</div>
@endsection
