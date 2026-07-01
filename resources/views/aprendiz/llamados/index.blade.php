@extends('layouts.aprendiz')

@section('titulo', 'Mis Llamados de Atención')

@section('contenido')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Historial de Llamados</h2>
            <p class="mt-1 text-sm text-gray-500">Consulta todos los llamados de atención que has recibido.</p>
        </div>
        @include('reportes._botones', ['rutaBase' => 'aprendiz.llamados.export'])
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @if($llamados->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p>No tienes llamados de atención registrados.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="responsive-cards w-full min-w-[640px] text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4">Fecha</th>
                            <th class="px-6 py-4">Asunto</th>
                            <th class="px-6 py-4">Instructor</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4 text-right">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($llamados as $llamado)
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
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4" data-label="Fecha">{{ \Carbon\Carbon::parse($llamado->fecha_llamado)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900" data-label="Asunto">{{ $llamado->asunto }}</td>
                                <td class="px-6 py-4" data-label="Instructor">{{ $llamado->instructor->usuario->nombres ?? 'Desconocido' }} {{ $llamado->instructor->usuario->apellidos ?? '' }}</td>
                                <td class="px-6 py-4" data-label="Estado">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $estadoBadge }}">
                                        {{ str($llamado->estado_llamado)->replace('_',' ')->ucfirst() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right" data-label="Detalle">
                                    <a href="{{ route('aprendiz.llamados.show', $llamado->id_llamado) }}" class="inline-flex items-center text-sm font-semibold text-[#39A900] hover:text-[#247200]">
                                        Ver más →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $llamados->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
