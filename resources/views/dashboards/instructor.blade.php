@extends('layouts.instructor')

@section('titulo', 'Mi Dashboard')

@section('contenido')
<div class="space-y-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Card: Total Reportados --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-50 text-[#39A900]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Llamados Reportados</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $llamados->count() }}</p>
                </div>
            </div>
        </div>

        {{-- Card: En revisión --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">En Revisión</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $llamados->where('estado_llamado', 'en_revision')->count() }}</p>
                </div>
            </div>
        </div>
        
        {{-- Card: Cerrados --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Llamados Cerrados</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $llamados->where('estado_llamado', 'cerrado')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <a href="{{ route('instructor.llamados.create') }}" class="rounded-lg bg-[#39A900] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#247200] shadow-sm flex items-center gap-2">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo Llamado de Atención
        </a>
    </div>

    {{-- Resumen de mis llamados --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="font-semibold text-gray-900">Historial de reportes emitidos</h3>
        </div>
        
        @if($llamados->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="mt-4 text-sm">No has emitido llamados de atención.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Fecha</th>
                            <th scope="col" class="px-6 py-3">Aprendiz</th>
                            <th scope="col" class="px-6 py-3">Asunto</th>
                            <th scope="col" class="px-6 py-3">Estado</th>
                            <th scope="col" class="px-6 py-3 text-right">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($llamados as $llamado)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4">{{ \Carbon\Carbon::parse($llamado->fecha_llamado)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $llamado->aprendiz->usuario->nombres ?? 'Desconocido' }} {{ $llamado->aprendiz->usuario->apellidos ?? '' }}</td>
                                <td class="px-6 py-4">{{ $llamado->asunto }}</td>
                                <td class="px-6 py-4">
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
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $estadoBadge }}">
                                        {{ str($llamado->estado_llamado)->replace('_',' ')->ucfirst() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('instructor.llamados.show', $llamado->id_llamado) }}" class="inline-flex items-center text-sm font-semibold text-[#39A900] hover:text-[#247200]">
                                        Ver más →
                                    </a>
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
