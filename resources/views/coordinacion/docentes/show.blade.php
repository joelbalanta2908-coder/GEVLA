@extends('layouts.coordinador')

@section('titulo', 'Docente')

@section('contenido')
@php
    $du = $instructor->usuario;
    $nombre = $du ? trim($du->nombres.' '.$du->apellidos) : $instructor->codigo_instructor;
    $esLider = $instructor->fichasLideradas->isNotEmpty();
@endphp
<div class="space-y-6">
    {{-- Encabezado con datos del docente --}}
    <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-4">
            <a href="{{ route('coordinacion.docentes.index') }}" class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900] ring-1 ring-[#39A900]/15 transition hover:bg-[#39A900]/20">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-bold text-gray-900">{{ $nombre }}</h2>
                    @if($esLider)
                        <span class="estado-badge inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">★ Instructor líder</span>
                    @endif
                    <span class="estado-badge inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $instructor->estado_instructor === 'activo' ? 'bg-[#39A900]/10 text-[#247200]' : 'bg-red-100 text-red-700' }}">{{ ucfirst($instructor->estado_instructor) }}</span>
                </div>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $instructor->codigo_instructor }}
                    · {{ $instructor->area_formacion ?? 'Sin área' }}
                    · {{ $instructor->tipo_docente_label }}
                </p>
                <p class="mt-0.5 text-xs text-gray-500">
                    {{ $du?->tipo_documento }} {{ $du?->numero_documento }}
                    @if($du?->correo) · {{ $du->correo }} @endif
                    @if($du?->telefono) · Tel. {{ $du->telefono }} @endif
                </p>
            </div>
        </div>

        {{-- Clasificar tipo de docente --}}
        <form method="POST" action="{{ route('coordinacion.docentes.tipo', $instructor->id_instructor) }}" class="flex items-center gap-2">
            @csrf
            @method('PATCH')
            <select name="tipo_docente" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                <option value="" @selected($instructor->tipo_docente === null)>No definido</option>
                @foreach($tipos as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected($instructor->tipo_docente === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-[#39A900] px-3 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Guardar</button>
        </form>
    </div>

    {{-- Fichas asignadas --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Fichas asignadas</p>
            <span class="text-xs text-gray-400">{{ $instructor->fichas->count() }} en total</span>
        </div>

        @if($instructor->fichas->isEmpty())
            <p class="mt-3 text-sm text-gray-400">Este docente no tiene fichas asignadas.</p>
        @else
            <div class="mt-3 overflow-x-auto rounded-xl border border-gray-100">
                <table class="w-full min-w-[560px] text-left text-sm">
                    <thead class="bg-gray-50 text-xs font-medium uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2.5">Ficha</th>
                            <th class="px-4 py-2.5">Programa</th>
                            <th class="px-4 py-2.5">Rol en la ficha</th>
                            <th class="px-4 py-2.5 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($instructor->fichas->sortByDesc('fecha_inicio') as $ficha)
                            @php $esLiderDeEsta = (int) $ficha->id_instructor_lider === (int) $instructor->id_instructor; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 font-semibold text-gray-900">{{ $ficha->numero_ficha }}</td>
                                <td class="px-4 py-2.5 text-gray-600">{{ optional($ficha->programa)->nombre_programa ?? '—' }}</td>
                                <td class="px-4 py-2.5">
                                    @if($esLiderDeEsta)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">★ Líder</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Instructor</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    <a href="{{ route('coordinacion.fichas.show', $ficha->id_ficha) }}" class="text-xs font-semibold text-[#39A900] hover:underline">Ver ficha</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
