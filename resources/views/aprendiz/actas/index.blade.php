@extends('layouts.aprendiz')

@section('titulo', 'Mis Actas')

@section('contenido')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Historial de Actas</h2>
            <p class="mt-1 text-sm text-gray-500">Consulta todas tus actas de coordinación y descargos.</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @if($actas->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p>No tienes actas registradas.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4">Código</th>
                            <th class="px-6 py-4">Fecha</th>
                            <th class="px-6 py-4">Tipo</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4 text-right">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($actas as $acta)
                            @php
                                $estadoBadge = match($acta->estado_acta) {
                                    'borrador'  => 'bg-gray-100 text-gray-600',
                                    'firmada'   => 'bg-green-100 text-green-700',
                                    'anulada'   => 'bg-red-100 text-red-700',
                                    default     => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $acta->codigo_acta ?? '#' . $acta->id_acta }}</td>
                                <td class="px-6 py-4">{{ \Carbon\Carbon::parse($acta->fecha_expedicion)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">{{ ucfirst($acta->tipo_acta) }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $estadoBadge }}">
                                        {{ ucfirst($acta->estado_acta) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('aprendiz.actas.show', $acta->id_acta) }}" class="inline-flex items-center text-sm font-semibold text-[#39A900] hover:text-[#247200]">
                                        Ver más →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $actas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
