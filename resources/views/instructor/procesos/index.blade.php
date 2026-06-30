@extends('layouts.instructor')

@section('titulo', 'Seguimiento de Procesos')

@section('contenido')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Procesos disciplinarios</h2>
        <p class="mt-1 text-sm text-gray-500">Procesos originados a partir de los llamados de atención que reportaste (solo lectura).</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @if($procesos->isEmpty())
            <div class="p-8 text-center text-gray-500"><p>No hay procesos derivados de tus reportes.</p></div>
        @else
            <div class="overflow-x-auto">
                <table class="responsive-cards w-full min-w-[640px] text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4">Aprendiz</th>
                            <th class="px-6 py-4">Etapa actual</th>
                            <th class="px-6 py-4">Inicio</th>
                            <th class="px-6 py-4">Llamado origen</th>
                            <th class="px-6 py-4">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($procesos as $proceso)
                            @php
                                $eb = match($proceso->estado_proceso) {
                                    'activo' => 'bg-amber-100 text-amber-700',
                                    'cerrado' => 'bg-green-100 text-green-700',
                                    'anulado' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4 font-medium text-gray-900" data-label="Aprendiz">{{ optional(optional($proceso->aprendiz)->usuario)->nombres }} {{ optional(optional($proceso->aprendiz)->usuario)->apellidos }}</td>
                                <td class="px-6 py-4" data-label="Etapa actual">{{ str($proceso->etapa_actual)->replace('_',' ')->ucfirst() }}</td>
                                <td class="px-6 py-4" data-label="Inicio">{{ \Illuminate\Support\Carbon::parse($proceso->fecha_inicio)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4" data-label="Llamado origen">{{ optional($proceso->llamadoAtencion)->asunto ?? '—' }}</td>
                                <td class="px-6 py-4" data-label="Estado">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $eb }}">{{ ucfirst($proceso->estado_proceso) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-6 py-4">{{ $procesos->links() }}</div>
        @endif
    </div>
</div>
@endsection
