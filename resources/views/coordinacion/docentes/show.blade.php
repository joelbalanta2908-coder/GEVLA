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
                    <a href="{{ route('coordinacion.docentes.editar', $instructor->id_instructor) }}" title="Editar datos del instructor"
                       class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200 transition hover:bg-amber-100">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Editar
                    </a>
                    @if($esLider)
                        <span class="estado-badge inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">★ Instructor líder</span>
                    @endif
                    <span class="estado-badge inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $instructor->estado_instructor === 'activo' ? 'bg-[#39A900]/10 text-[#247200]' : 'bg-red-100 text-red-700' }}">{{ ucfirst($instructor->estado_instructor) }}</span>
                </div>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $instructor->codigo_instructor }}
                    · {{ $instructor->area_formacion ?? 'Sin área' }}
                    {{-- El tipo de instructor solo se muestra si está activo. --}}
                    @if($instructor->estado_instructor === 'activo')
                        · {{ $instructor->tipo_docente_label }}
                    @endif
                </p>
                <p class="mt-0.5 text-xs text-gray-500">
                    {{ $du?->tipo_documento }} {{ $du?->numero_documento }}
                    @if($du?->correo) · {{ $du->correo }} @endif
                    @if($du?->telefono) · Tel. {{ $du->telefono }} @endif
                </p>
            </div>
        </div>

        {{-- Clasificar tipo de docente (solo cuando el instructor está activo) --}}
        @if($instructor->estado_instructor === 'activo')
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
        @endif
    </div>

    {{-- Indicadores --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 text-center shadow-sm">
            <p class="text-2xl font-extrabold text-slate-900">{{ $instructor->fichas->count() }}</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Fichas asignadas</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 text-center shadow-sm">
            <p class="text-2xl font-extrabold text-slate-900">{{ $instructor->fichasLideradas->count() }}</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Fichas lideradas</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 text-center shadow-sm">
            <p class="text-2xl font-extrabold text-slate-900">{{ $instructor->llamados_atencion_count }}</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Llamados reportados</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 text-center shadow-sm">
            <p class="text-2xl font-extrabold text-slate-900">{{ $du?->ultimo_acceso ? $du->ultimo_acceso->locale('es')->diffForHumans() : 'Nunca' }}</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Último acceso</p>
        </div>
    </div>

    {{-- Datos personales y de la cuenta --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Datos personales</p>
            <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Nombres</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $du?->nombres ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Apellidos</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $du?->apellidos ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Documento</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $du ? $du->tipo_documento . ' ' . $du->numero_documento : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Teléfono</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $du?->telefono ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase text-gray-400">Correo</dt>
                    <dd class="mt-1 break-all text-sm text-gray-900">{{ $du?->correo ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Usuario de acceso</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $du?->username ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Estado de la cuenta</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $du ? ucfirst($du->estado_usuario) : 'Sin cuenta' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Información académica</p>
            <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Código de instructor</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $instructor->codigo_instructor }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Área de formación</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $instructor->area_formacion ?? 'Sin área' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Tipo de instructor</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $instructor->estado_instructor === 'activo' ? $instructor->tipo_docente_label : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Estado</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($instructor->estado_instructor) }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase text-gray-400">Roles en el sistema</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $du ? (implode(' · ', \App\Support\Roles::disponiblesPara($du)) ?: 'Instructor') : '—' }}</dd>
                </div>
            </dl>
        </div>
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
