@extends('layouts.coordinador')

@section('titulo', 'Instructores')

@section('contenido')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Instructores</h2>
            <p class="mt-1 text-sm text-gray-500">Instructores a cargo: fichas asignadas, liderazgo y tipo de instructor.</p>
        </div>
        <a href="{{ route('coordinacion.docentes.crear') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2D8200]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Nuevo instructor
        </a>
    </div>

    {{-- Carga masiva por Excel --}}
    @include('importacion._panel', [
        'tituloPanel'  => 'instructores',
        'urlPlantilla' => route('coordinacion.importacion.plantilla', 'instructores'),
        'urlImportar'  => route('coordinacion.importacion.importar', 'instructores'),
    ])

    <form method="GET" action="{{ route('coordinacion.docentes.index') }}" data-live-form class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <input type="text" name="buscar" value="{{ $buscar }}" data-live placeholder="Nombre, documento, código o área..."
               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30 lg:col-span-2">
        <select name="tipo_docente" data-live class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            <option value="">Todos los tipos</option>
            @foreach($tipos as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected($tipo === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        <select name="estado_instructor" data-live class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            <option value="">Todos los estados</option>
            <option value="activo" @selected($estado === 'activo')>Activo</option>
            <option value="inactivo" @selected($estado === 'inactivo')>Inactivo</option>
        </select>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="responsive-cards w-full min-w-[840px] text-sm">
            <thead class="whitespace-nowrap bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                <tr>
                    <th class="whitespace-nowrap px-4 py-3">Docente</th>
                    <th class="whitespace-nowrap px-4 py-3">Área</th>
                    <th class="whitespace-nowrap px-4 py-3">Tipo</th>
                    <th class="whitespace-nowrap px-4 py-3 text-center">Fichas</th>
                    <th class="whitespace-nowrap px-4 py-3">Estado</th>
                    <th class="whitespace-nowrap px-4 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($docentes as $docente)
                    @php $du = $docente->usuario; $esLider = $docente->fichas_lideradas_count > 0; @endphp
                    <tr class="hover:bg-gray-50">
                        {{-- Docente: foto, nombre y debajo código · documento · correo --}}
                        <td class="px-4 py-3" data-label="Docente">
                            <div class="flex items-center gap-3">
                                @if($du?->fotoUrl())
                                    <img src="{{ $du->fotoUrl() }}" alt="Foto de {{ $du->nombres }}" class="h-9 w-9 shrink-0 rounded-full object-cover ring-1 ring-gray-200">
                                @else
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#39A900]/10 text-xs font-black text-[#39A900]">
                                        {{ $du?->iniciales() ?? 'I' }}
                                    </span>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-gray-900">{{ $du ? trim($du->nombres.' '.$du->apellidos) : $docente->codigo_instructor }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ $docente->codigo_instructor }}{{ $du ? ' · '.$du->tipo_documento.' '.$du->numero_documento : '' }}</p>
                                    @if($du?->correo)
                                        <p class="truncate text-xs text-gray-400">{{ $du->correo }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600" data-label="Área">{{ $docente->area_formacion ?? '—' }}</td>
                        <td class="px-4 py-3" data-label="Tipo">
                            {{-- El tipo de instructor solo se muestra/gestiona si el instructor está activo. --}}
                            @if($docente->estado_instructor === 'activo')
                                <form method="POST" action="{{ route('coordinacion.docentes.tipo', $docente->id_instructor) }}" data-live-form>
                                    @csrf
                                    @method('PATCH')
                                    <select name="tipo_docente" data-live class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                                        <option value="" @selected($docente->tipo_docente === null)>No definido</option>
                                        @foreach($tipos as $valor => $etiqueta)
                                            <option value="{{ $valor }}" @selected($docente->tipo_docente === $valor)>{{ $etiqueta }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        {{-- Fichas + liderazgo en una sola columna --}}
                        <td class="px-4 py-3 text-center" data-label="Fichas">
                            <div class="flex items-center justify-center gap-1.5 whitespace-nowrap">
                                @if($docente->fichas_count > 0)
                                    <span class="estado-badge inline-flex rounded-full bg-[#39A900]/10 px-2.5 py-1 text-xs font-medium text-[#247200]">{{ $docente->fichas_count }}</span>
                                @else
                                    <span class="whitespace-nowrap text-xs text-gray-400">Sin ficha</span>
                                @endif
                                @if($esLider)
                                    <span class="estado-badge inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700" title="Instructor líder">★</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3" data-label="Estado">
                            <span class="estado-badge inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $docente->estado_instructor === 'activo' ? 'bg-[#39A900]/10 text-[#247200]' : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($docente->estado_instructor) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right" data-label="Acción">
                            {{-- whitespace-nowrap: las acciones no se parten en dos líneas --}}
                            <div class="flex items-center justify-end gap-3 whitespace-nowrap">
                                <a href="{{ route('coordinacion.docentes.show', $docente->id_instructor) }}" class="font-medium text-[#39A900] hover:underline">Ver</a>
                                <a href="{{ route('coordinacion.docentes.editar', $docente->id_instructor) }}" class="font-medium text-amber-600 hover:underline">Editar</a>
                                @php
                                    $docenteActivo = $docente->estado_instructor === 'activo';
                                    $nombreDocente = $du ? trim($du->nombres.' '.$du->apellidos) : $docente->codigo_instructor;
                                @endphp
                                <form method="POST" action="{{ route('coordinacion.docentes.estado', $docente->id_instructor) }}"
                                      data-confirm="{{ $docenteActivo
                                          ? "¿Inactivar al instructor {$nombreDocente}? No podrá iniciar sesión ni aparecerá en los selectores de fichas."
                                          : "¿Activar al instructor {$nombreDocente}? Podrá volver a iniciar sesión y ser asignado a fichas." }}"
                                      data-confirm-title="{{ $docenteActivo ? 'Inactivar instructor' : 'Activar instructor' }}"
                                      data-confirm-btn="{{ $docenteActivo ? 'Sí, inactivar' : 'Sí, activar' }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="rounded-full px-3 py-1.5 text-xs font-bold transition {{ $docenteActivo
                                                ? 'bg-red-50 text-red-600 hover:bg-red-100'
                                                : 'bg-[#39A900]/10 text-[#247200] hover:bg-[#39A900]/20' }}">
                                        {{ $docenteActivo ? 'Inactivar' : 'Activar' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No se encontraron instructores.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($docentes, 'links'))
        {{ $docentes->links() }}
    @endif
</div>
@endsection
