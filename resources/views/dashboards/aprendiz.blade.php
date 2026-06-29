@extends('layouts.aprendiz')

@section('titulo', 'Mi Dashboard')

@section('contenido')
<div class="space-y-4">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Card: Estado Académico --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-50 text-[#39A900]">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Estado académico</p>
                    <p class="text-xl font-extrabold text-gray-900">{{ ucfirst($aprendiz->estado_academico ?? 'Activo') }}</p>
                </div>
            </div>
        </div>

        {{-- Card: Llamados de Atención --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Llamados totales</p>
                    <p class="text-xl font-extrabold text-gray-900">{{ $llamados->count() }}</p>
                </div>
            </div>
        </div>

        {{-- Card: Procesos Activos --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-50 text-red-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Procesos disciplinarios</p>
                    <p class="text-xl font-extrabold text-gray-900">{{ $procesos->where('estado_proceso', 'activo')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen de mis llamados --}}
    <div class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-base font-semibold text-gray-900">Mis últimos llamados de atención</h3>
        </div>
        
        @if($llamados->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-4 text-sm">No tienes llamados de atención registrados.</p>
                <p class="mt-1 text-[11px] text-gray-400">¡Sigue con tu buen desempeño académico y disciplinario!</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">Fecha</th>
                            <th scope="col" class="px-5 py-3">Asunto</th>
                            <th scope="col" class="px-5 py-3">Instructor</th>
                            <th scope="col" class="px-5 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($llamados->take(5) as $llamado)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-5 py-3.5">{{ \Carbon\Carbon::parse($llamado->fecha_llamado)->format('d/m/Y') }}</td>
                                <td class="px-5 py-3.5 font-medium text-gray-900">{{ $llamado->asunto }}</td>
                                <td class="px-5 py-3.5">{{ $llamado->instructor->usuario->nombres ?? 'No asignado' }}</td>
                                <td class="px-5 py-3.5">
                                    @php
                                        $estadoBadge = match($llamado->estado_llamado) {
                                            'registrado'  => 'bg-gray-100 text-gray-600',
                                            'en_revision' => 'bg-amber-100 text-amber-700',
                                            'notificado'  => 'bg-blue-100 text-blue-700',
                                            'cerrado'     => 'bg-green-100 text-green-700',
                                            'cancelado'   => 'bg-red-100 text-red-700',
                                            default       => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-medium {{ $estadoBadge }}">
                                        {{ str($llamado->estado_llamado)->replace('_',' ')->ucfirst() }}
                                    </span>
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
