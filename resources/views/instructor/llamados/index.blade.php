@extends('layouts.instructor')

@section('titulo', 'Mis Llamados de Atención')

@section('contenido')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Mis Reportes</h2>
            <p class="mt-1 text-sm text-gray-500">Listado de los llamados de atención que has emitido a los aprendices.</p>
        </div>
        <a href="{{ route('instructor.llamados.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#2D8200] shadow-sm">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Nuevo Llamado
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @if($llamados->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p>Aún no has reportado ningún llamado de atención.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Fecha</th>
                            <th class="px-6 py-4">Aprendiz</th>
                            <th class="px-6 py-4">Asunto</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
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
                                <td class="px-6 py-4 font-medium text-gray-900">#{{ $llamado->id_llamado }}</td>
                                <td class="px-6 py-4">{{ \Carbon\Carbon::parse($llamado->fecha_llamado)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">{{ $llamado->aprendiz->usuario->nombres }} {{ $llamado->aprendiz->usuario->apellidos }}</td>
                                <td class="px-6 py-4">{{ $llamado->asunto }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $estadoBadge }}">
                                        {{ str($llamado->estado_llamado)->replace('_',' ')->ucfirst() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('instructor.llamados.show', $llamado->id_llamado) }}" class="rounded bg-blue-50 p-1.5 text-blue-600 hover:bg-blue-100 transition" title="Ver detalle">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>
                                        @if($llamado->estado_llamado === 'registrado')
                                            <a href="{{ route('instructor.llamados.edit', $llamado->id_llamado) }}" class="rounded bg-amber-50 p-1.5 text-amber-600 hover:bg-amber-100 transition" title="Editar">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </a>
                                            <form method="POST" action="{{ route('instructor.llamados.destroy', $llamado->id_llamado) }}" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este reporte?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded bg-red-50 p-1.5 text-red-600 hover:bg-red-100 transition" title="Eliminar">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
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
