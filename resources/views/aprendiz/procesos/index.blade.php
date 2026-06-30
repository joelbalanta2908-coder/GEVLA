@extends('layouts.aprendiz')

@section('titulo', 'Mis Procesos Disciplinarios')

@section('contenido')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Historial de Procesos</h2>
            <p class="mt-1 text-sm text-gray-500">Consulta los procesos disciplinarios en los que estás involucrado.</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @if($procesos->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p>No tienes procesos disciplinarios registrados.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="responsive-cards w-full min-w-[640px] text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Fecha Inicio</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4">Decisión Final</th>
                            <th class="px-6 py-4 text-right">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($procesos as $proceso)
                            @php
                                $estadoBadge = match($proceso->estado_proceso) {
                                    'activo'    => 'bg-amber-100 text-amber-700',
                                    'suspendido'=> 'bg-gray-100 text-gray-700',
                                    'cerrado'   => 'bg-green-100 text-green-700',
                                    'apelacion' => 'bg-blue-100 text-blue-700',
                                    default     => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4 font-medium text-gray-900" data-label="ID">#{{ $proceso->id_proceso }}</td>
                                <td class="px-6 py-4" data-label="Fecha Inicio">{{ \Carbon\Carbon::parse($proceso->fecha_inicio)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4" data-label="Estado">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $estadoBadge }}">
                                        {{ ucfirst($proceso->estado_proceso) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4" data-label="Decisión Final">{{ $proceso->decision_final ?? 'En curso' }}</td>
                                <td class="px-6 py-4 text-right" data-label="Detalle">
                                    <a href="{{ route('aprendiz.procesos.show', $proceso->id_proceso) }}" class="inline-flex items-center text-sm font-semibold text-[#39A900] hover:text-[#247200]">
                                        Ver más →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $procesos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
