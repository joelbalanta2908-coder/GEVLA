@extends('layouts.coordinador')

@section('titulo', 'Coordinadores')

@section('contenido')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Coordinadores</h2>
            <p class="mt-1 text-sm text-gray-500">Cuentas de coordinación: cargo, dependencia y estado de acceso al sistema.</p>
        </div>
        <a href="{{ route('coordinacion.coordinadores.crear') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2D8200]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Nuevo coordinador
        </a>
    </div>

    {{-- Carga masiva por Excel --}}
    @include('importacion._panel', [
        'tituloPanel'  => 'coordinadores',
        'urlPlantilla' => route('coordinacion.importacion.plantilla', 'coordinadores'),
        'urlImportar'  => route('coordinacion.importacion.importar', 'coordinadores'),
    ])

    <form method="GET" action="{{ route('coordinacion.coordinadores.index') }}" data-live-form class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <input type="text" name="buscar" value="{{ $buscar }}" data-live placeholder="Nombre, documento, cargo o dependencia..."
               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30 sm:col-span-2">
        <select name="estado_coordinacion" data-live class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            <option value="">Todos los estados</option>
            <option value="activo" @selected($estado === 'activo')>Activo</option>
            <option value="inactivo" @selected($estado === 'inactivo')>Inactivo</option>
        </select>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="responsive-cards w-full min-w-[820px] text-sm">
            <thead class="whitespace-nowrap bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3">Coordinador</th>
                    <th class="px-5 py-3">Documento</th>
                    <th class="px-5 py-3">Cargo</th>
                    <th class="px-5 py-3">Dependencia</th>
                    <th class="px-5 py-3 text-center">Llamados</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($coordinadores as $coordinador)
                    @php
                        $cu = $coordinador->usuario;
                        $activo = $coordinador->estado_coordinacion === 'activo';
                        $nombreCoordinador = $cu ? trim($cu->nombres.' '.$cu->apellidos) : ('#'.$coordinador->id_coordinacion);
                        $esPropio = $cu && (int) $cu->id_usuario === (int) auth()->id();
                    @endphp
                    <tr>
                        <td class="px-5 py-3" data-label="Coordinador">
                            <div class="flex items-center gap-3">
                                @if($cu?->fotoUrl())
                                    <img src="{{ $cu->fotoUrl() }}" alt="Foto de {{ $cu->nombres }}" class="h-9 w-9 shrink-0 rounded-full object-cover ring-1 ring-gray-200">
                                @else
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#39A900]/10 text-xs font-black text-[#39A900]">
                                        {{ $cu?->iniciales() ?? 'C' }}
                                    </span>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-gray-900">{{ $nombreCoordinador }} @if($esPropio)<span class="ml-1 rounded-full bg-[#39A900]/10 px-2 py-0.5 text-[10px] font-bold uppercase text-[#247200]">Tú</span>@endif</p>
                                    <p class="truncate text-xs text-gray-400">{{ $cu?->correo }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-5 py-3" data-label="Documento">{{ $cu?->tipo_documento }} {{ $cu?->numero_documento }}</td>
                        <td class="px-5 py-3" data-label="Cargo">{{ $coordinador->cargo ?? '—' }}</td>
                        <td class="px-5 py-3" data-label="Dependencia">{{ $coordinador->dependencia ?? '—' }}</td>
                        <td class="px-5 py-3 text-center" data-label="Llamados">{{ $coordinador->llamados_atencion_count }}</td>
                        <td class="px-5 py-3" data-label="Estado">
                            <span class="estado-badge inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $activo ? 'bg-[#39A900]/10 text-[#247200]' : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($coordinador->estado_coordinacion) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right" data-label="Acción">
                            <div class="flex items-center justify-end gap-3 whitespace-nowrap">
                                <a href="{{ route('coordinacion.coordinadores.editar', $coordinador->id_coordinacion) }}" class="font-medium text-amber-600 hover:underline">Editar</a>
                                @unless($esPropio)
                                    <form method="POST" action="{{ route('coordinacion.coordinadores.estado', $coordinador->id_coordinacion) }}"
                                          data-confirm="{{ $activo
                                              ? "¿Inactivar al coordinador {$nombreCoordinador}? No podrá iniciar sesión mientras esté inactivo."
                                              : "¿Activar al coordinador {$nombreCoordinador}? Podrá volver a iniciar sesión en el sistema." }}"
                                          data-confirm-title="{{ $activo ? 'Inactivar coordinador' : 'Activar coordinador' }}"
                                          data-confirm-btn="{{ $activo ? 'Sí, inactivar' : 'Sí, activar' }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="rounded-full px-3 py-1.5 text-xs font-bold transition {{ $activo
                                                    ? 'bg-red-50 text-red-600 hover:bg-red-100'
                                                    : 'bg-[#39A900]/10 text-[#247200] hover:bg-[#39A900]/20' }}">
                                            {{ $activo ? 'Inactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">No se encontraron coordinadores.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($coordinadores, 'links'))
        <div>{{ $coordinadores->links() }}</div>
    @endif
</div>
@endsection
