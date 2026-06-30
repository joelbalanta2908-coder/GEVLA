@extends('layouts.instructor')

@section('titulo', 'Notificaciones')

@section('contenido')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Notificaciones</h2>
        <p class="mt-1 text-sm text-gray-500">Notificaciones generadas a los aprendices a partir de tus llamados de atención.</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @if($notificaciones->isEmpty())
            <div class="p-8 text-center text-gray-500"><p>No hay notificaciones registradas.</p></div>
        @else
            <div class="overflow-x-auto">
                <table class="responsive-cards w-full min-w-[640px] text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4">Fecha</th>
                            <th class="px-6 py-4">Aprendiz</th>
                            <th class="px-6 py-4">Tipo</th>
                            <th class="px-6 py-4">Medio</th>
                            <th class="px-6 py-4">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($notificaciones as $n)
                            @php
                                $nb = match($n->estado_notificacion) {
                                    'recibida' => 'bg-green-100 text-green-700',
                                    'enviada' => 'bg-blue-100 text-blue-700',
                                    'no_entregada' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4" data-label="Fecha">{{ \Illuminate\Support\Carbon::parse($n->fecha_envio)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900" data-label="Aprendiz">{{ optional(optional($n->aprendiz)->usuario)->nombres }} {{ optional(optional($n->aprendiz)->usuario)->apellidos }}</td>
                                <td class="px-6 py-4" data-label="Tipo">{{ str($n->tipo_notificacion)->replace('_',' ')->ucfirst() }}</td>
                                <td class="px-6 py-4" data-label="Medio">{{ str($n->medio_envio)->replace('_',' ')->ucfirst() }}</td>
                                <td class="px-6 py-4" data-label="Estado">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $nb }}">{{ ucfirst($n->estado_notificacion) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-6 py-4">{{ $notificaciones->links() }}</div>
        @endif
    </div>
</div>
@endsection
