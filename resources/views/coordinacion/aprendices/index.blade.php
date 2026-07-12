@extends('layouts.coordinador')

@section('titulo', 'Aprendices')

@section('contenido')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Aprendices</h2>
        <p class="mt-1 text-sm text-gray-500">Consulta el historial disciplinario y formativo de cada aprendiz.</p>
    </div>

    {{-- Barra de búsqueda y botón «Nuevo aprendiz» a la misma altura --}}
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('coordinacion.aprendices.index') }}" data-live-form class="flex flex-col gap-3 sm:flex-row sm:items-center lg:flex-1">
            <input type="text" name="buscar" value="{{ $buscar }}" data-live placeholder="Buscar por nombre, apellido o correo..."
                   class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30 sm:max-w-md">
            <select name="estado_academico" data-live class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                <option value="">Todos los estados</option>
                @foreach($estados as $e)
                    <option value="{{ $e }}" @selected($estado == $e)>{{ ['en_formacion' => 'En formación', 'aplazado' => 'Aplazado', 'cancelado' => 'Cancelado', 'certificado' => 'Certificado'][$e] ?? ucfirst(str_replace('_', ' ', $e)) }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Filtrar</button>
        </form>
        <a href="{{ route('coordinacion.aprendices.crear') }}"
           class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2D8200]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Nuevo aprendiz
        </a>
    </div>

    {{-- Carga masiva por Excel --}}
    @include('importacion._panel', [
        'tituloPanel'  => 'aprendices',
        'urlPlantilla' => route('coordinacion.importacion.plantilla', 'aprendices'),
        'urlImportar'  => route('coordinacion.importacion.importar', 'aprendices'),
    ])

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="responsive-cards w-full min-w-[1000px] text-sm">
            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                <tr>
                    <th class="whitespace-nowrap px-5 py-3">Aprendiz</th>
                    <th class="whitespace-nowrap px-5 py-3">Correo</th>
                    <th class="whitespace-nowrap px-5 py-3">Estado</th>
                    <th class="whitespace-nowrap px-5 py-3">Cuenta</th>
                    <th class="whitespace-nowrap px-5 py-3 text-center">Llamados</th>
                    <th class="whitespace-nowrap px-5 py-3 text-center">Procesos</th>
                    <th class="whitespace-nowrap px-5 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($aprendices as $ap)
                    @php
                        $u = $ap->usuario;
                        $eb = match($ap->estado_academico) {
                            'en_formacion' => 'bg-[#39A900]/10 text-[#247200]',
                            'aplazado' => 'bg-amber-100 text-amber-700',
                            'cancelado' => 'bg-red-100 text-red-700',
                            'certificado' => 'bg-blue-100 text-blue-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                        $etiquetaEstado = ['en_formacion' => 'En formación', 'aplazado' => 'Aplazado', 'cancelado' => 'Cancelado', 'certificado' => 'Certificado'][$ap->estado_academico] ?? ucfirst(str_replace('_', ' ', $ap->estado_academico));
                    @endphp
                    <tr class="hover:bg-gray-50">
                        {{-- Aprendiz con su foto de perfil (o iniciales) --}}
                        <td class="px-5 py-3" data-label="Aprendiz">
                            <div class="flex items-center gap-3">
                                @if($u?->fotoUrl())
                                    <img src="{{ $u->fotoUrl() }}" alt="Foto de {{ $u->nombres }}" class="h-9 w-9 shrink-0 rounded-full object-cover ring-1 ring-gray-200">
                                @else
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#39A900]/10 text-xs font-black text-[#39A900]">
                                        {{ $u?->iniciales() ?? 'A' }}
                                    </span>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-gray-900">{{ $u?->nombres }} {{ $u?->apellidos }}</p>
                                    <p class="truncate text-xs text-gray-400">{{ $u?->tipo_documento }} {{ $u?->numero_documento }}</p>
                                </div>
                            </div>
                        </td>
                        {{-- Se muestra el correo principal de la cuenta; si no existe, el institucional. --}}
                        <td class="px-5 py-3 text-gray-600" data-label="Correo">{{ $u?->correo ?? $ap->correo_institucional }}</td>
                        <td class="px-5 py-3" data-label="Estado">
                            <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $eb }}">{{ $etiquetaEstado }}</span>
                        </td>
                        @php
                            $estadoCuenta = $u?->estado_usuario;
                            $cuentaBadge = match($estadoCuenta) {
                                'activo'    => 'bg-[#39A900]/10 text-[#247200]',
                                'inactivo'  => 'bg-red-100 text-red-700',
                                'bloqueado' => 'bg-slate-200 text-slate-700',
                                default     => 'bg-gray-100 text-gray-500',
                            };
                            $cuentaActiva = $estadoCuenta === 'activo';
                            $nombreAprendiz = trim(($u->nombres ?? '') . ' ' . ($u->apellidos ?? ''));
                            $sinHistorial = ($ap->llamados_atencion_count + $ap->procesos_disciplinarios_count + ($ap->actas_coordinacion_count ?? 0)) === 0;
                        @endphp
                        <td class="px-5 py-3" data-label="Cuenta">
                            <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $cuentaBadge }}">
                                {{ $estadoCuenta ? ucfirst($estadoCuenta) : 'Sin cuenta' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center" data-label="Llamados">{{ $ap->llamados_atencion_count }}</td>
                        <td class="px-5 py-3 text-center" data-label="Procesos">{{ $ap->procesos_disciplinarios_count }}</td>
                        <td class="px-5 py-3 text-right" data-label="Acción">
                            {{-- Acciones compactas: no se parten en dos líneas --}}
                            <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                <a href="{{ route('coordinacion.aprendices.show', $ap->id_aprendiz) }}" class="font-medium text-[#39A900] hover:underline">Ver</a>
                                <a href="{{ route('coordinacion.aprendices.editar', $ap->id_aprendiz) }}" class="font-medium text-amber-600 hover:underline">Editar</a>
                                @if($u && $estadoCuenta !== 'bloqueado')
                                    <form method="POST" action="{{ route('coordinacion.aprendices.estado', $ap->id_aprendiz) }}"
                                          data-confirm="{{ $cuentaActiva
                                              ? "¿Inactivar al aprendiz {$nombreAprendiz}? No podrá iniciar sesión mientras esté inactivo."
                                              : "¿Activar al aprendiz {$nombreAprendiz}? Podrá volver a iniciar sesión en el sistema." }}"
                                          data-confirm-title="{{ $cuentaActiva ? 'Inactivar aprendiz' : 'Activar aprendiz' }}"
                                          data-confirm-btn="{{ $cuentaActiva ? 'Sí, inactivar' : 'Sí, activar' }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="rounded-full px-3 py-1.5 text-xs font-bold transition {{ $cuentaActiva
                                                    ? 'bg-red-50 text-red-600 hover:bg-red-100'
                                                    : 'bg-[#39A900]/10 text-[#247200] hover:bg-[#39A900]/20' }}">
                                            {{ $cuentaActiva ? 'Inactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">No se encontraron aprendices.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($aprendices, 'links'))
        {{ $aprendices->links() }}
    @endif
</div>
@endsection
