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
                            <th class="px-6 py-4 text-right">Acción</th>
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
                                    {{-- Al abrir esta sección todas quedan marcadas como vistas. --}}
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $nb }}">{{ ['recibida' => 'Vista', 'enviada' => 'Enviada', 'no_entregada' => 'No entregada'][$n->estado_notificacion] ?? ucfirst($n->estado_notificacion) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right" data-label="Acción">
                                    {{-- Eliminar esta notificación --}}
                                    <form method="POST" action="{{ route('instructor.notificaciones.eliminar', $n->id_notificacion) }}" class="inline"
                                          data-confirm="¿Eliminar esta notificación? Esta acción no se puede deshacer."
                                          data-confirm-title="Eliminar notificación" data-confirm-btn="Sí, eliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded bg-red-50 p-1.5 text-red-600 transition hover:bg-red-100" title="Eliminar notificación">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
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
