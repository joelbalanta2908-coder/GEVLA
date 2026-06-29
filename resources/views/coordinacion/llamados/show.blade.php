@extends('layouts.coordinador')

@section('titulo', 'Detalle del llamado')

@section('contenido')
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

<div class="space-y-6">
    <a href="{{ route('coordinacion.llamados.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver a llamados
    </a>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $llamado->asunto }}</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Reportado el {{ \Carbon\Carbon::parse($llamado->fecha_llamado)->translatedFormat('d \d\e F \d\e Y') }}
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-medium {{ $estadoBadge }}">
                        {{ str($llamado->estado_llamado)->replace('_',' ')->ucfirst() }}
                    </span>
                </div>

                <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase text-gray-400">Aprendiz</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $llamado->aprendiz->usuario->nombres }} {{ $llamado->aprendiz->usuario->apellidos }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-gray-400">Instructor reporta</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $llamado->instructor->usuario->nombres }} {{ $llamado->instructor->usuario->apellidos }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-gray-400">Tipo de llamado</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ str($llamado->tipo_llamado)->replace('_',' ')->ucfirst() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-gray-400">Categoría</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($llamado->categoria) }}</dd>
                    </div>
                </dl>

                <div class="mt-6 space-y-4">
                    <div>
                        <h3 class="text-xs font-medium uppercase text-gray-400">Descripción de los hechos</h3>
                        <p class="mt-1 text-sm text-gray-700">{{ $llamado->descripcion_hechos }}</p>
                    </div>
                    @if($llamado->pruebas_aportadas)
                        <div>
                            <h3 class="text-xs font-medium uppercase text-gray-400">Pruebas aportadas</h3>
                            <p class="mt-1 text-sm text-gray-700">{{ $llamado->pruebas_aportadas }}</p>
                        </div>
                    @endif
                    @if($llamado->observaciones)
                        <div>
                            <h3 class="text-xs font-medium uppercase text-gray-400">Observaciones de coordinación</h3>
                            <p class="mt-1 text-sm text-gray-700">{{ $llamado->observaciones }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($llamado->faltas && $llamado->faltas->count())
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="font-semibold text-gray-900">Faltas asociadas</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach($llamado->faltas as $falta)
                            @php
                                $califBadge = match($falta->calificacion_falta) {
                                    'leve'      => 'bg-green-100 text-green-700',
                                    'grave'     => 'bg-amber-100 text-amber-700',
                                    'muy_grave' => 'bg-red-100 text-red-700',
                                    default     => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <div class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">{{ $falta->principio_valor_infringido }}</p>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $califBadge }}">
                                        {{ str($falta->calificacion_falta)->replace('_',' ')->ucfirst() }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-600">{{ $falta->descripcion_hechos }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="space-y-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-[#00324D]">Acciones de coordinación</h3>
                    <div class="flex gap-2">
                        <a href="{{ route('coordinacion.llamados.edit', $llamado->id_llamado) }}" class="rounded bg-amber-50 p-1.5 text-amber-600 hover:bg-amber-100 transition" title="Editar">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </a>
                        <form method="POST" action="{{ route('coordinacion.llamados.destroy', $llamado->id_llamado) }}" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este llamado de atención?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded bg-red-50 p-1.5 text-red-600 hover:bg-red-100 transition" title="Eliminar">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                    </div>
                </div>

                <a href="{{ route('coordinacion.actas.create', ['llamado' => $llamado->id_llamado]) }}"
                   class="block w-full rounded-lg bg-[#39A900] px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-[#2D8200]">
                    Expedir acta de coordinación
                </a>

                <form method="POST" action="{{ route('coordinacion.llamados.actualizarEstado', $llamado->id_llamado) }}" class="space-y-2">
                    @csrf
                    @method('PATCH')
                    <label class="text-xs font-medium uppercase text-gray-400">Actualizar estado</label>
                    <select name="estado_llamado" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        @foreach(['registrado','en_revision','notificado','cerrado','cancelado'] as $estado)
                            <option value="{{ $estado }}" @selected($llamado->estado_llamado == $estado)>
                                {{ str($estado)->replace('_',' ')->ucfirst() }}
                            </option>
                        @endforeach
                    </select>
                    <button class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Guardar estado
                    </button>
                </form>
            </div>

            @if($llamado->coordinacion)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold text-gray-900">Asignado a</h3>
                    <p class="mt-2 text-sm text-gray-700">{{ $llamado->coordinacion->cargo }}</p>
                    <p class="text-xs text-gray-400">{{ $llamado->coordinacion->dependencia }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
