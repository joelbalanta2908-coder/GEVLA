@extends('layouts.aprendiz')

@section('titulo', 'Mis Notificaciones')

@section('contenido')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Mis Notificaciones</h2>
        <p class="mt-1 text-sm text-gray-500">Comunicaciones oficiales relacionadas con tu proceso formativo y disciplinario.</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @if($notificaciones->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <p class="mt-4 text-sm">No tienes notificaciones registradas.</p>
            </div>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach($notificaciones as $n)
                    @php
                        $nb = match($n->estado_notificacion) {
                            'recibida' => 'bg-green-100 text-green-700',
                            'enviada' => 'bg-blue-100 text-blue-700',
                            'no_entregada' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <li class="px-6 py-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-bold text-gray-900">{{ str($n->tipo_notificacion)->replace('_',' ')->ucfirst() }}</p>
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $nb }}">{{ ucfirst($n->estado_notificacion) }}</span>
                        </div>
                        @if($n->contenido_resumen)
                            <p class="mt-1 text-sm text-gray-600">{{ $n->contenido_resumen }}</p>
                        @endif
                        <p class="mt-1 text-xs text-gray-400">
                            {{ \Illuminate\Support\Carbon::parse($n->fecha_envio)->format('d/m/Y') }} · {{ str($n->medio_envio)->replace('_',' ')->ucfirst() }}
                        </p>
                    </li>
                @endforeach
            </ul>
            <div class="border-t border-gray-200 px-6 py-4">{{ $notificaciones->links() }}</div>
        @endif
    </div>
</div>
@endsection
