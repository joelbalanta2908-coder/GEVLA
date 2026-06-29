@extends('layouts.coordinador')

@section('titulo', 'Historial del proceso')

@section('contenido')
@php
    $etapas = [
        'llamado_escrito'       => 'Llamado escrito',
        'acondicionamiento'     => 'Acondicionamiento',
        'cancelacion_matricula' => 'Cancelación de matrícula',
        'finalizado'            => 'Finalizado',
    ];
    $etapaKeys = array_keys($etapas);
    $indiceActual = array_search($proceso->etapa_actual, $etapaKeys);

    $estadoBadge = match($proceso->estado_proceso) {
        'activo'  => 'bg-green-100 text-green-700',
        'cerrado' => 'bg-gray-100 text-gray-600',
        'anulado' => 'bg-red-100 text-red-700',
        default   => 'bg-gray-100 text-gray-600',
    };
@endphp

<div class="space-y-6">
    <a href="{{ route('coordinacion.procesos.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver a procesos
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-[#00324D]">
                        {{ $proceso->aprendiz->usuario->nombres }} {{ $proceso->aprendiz->usuario->apellidos }}
                    </h2>
                    <div class="flex gap-2">
                        <a href="{{ route('coordinacion.procesos.edit', $proceso->id_proceso) }}" class="rounded bg-amber-50 p-1.5 text-amber-600 hover:bg-amber-100 transition" title="Editar">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </a>
                        <form method="POST" action="{{ route('coordinacion.procesos.destroy', $proceso->id_proceso) }}" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este proceso disciplinario?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded bg-red-50 p-1.5 text-red-600 hover:bg-red-100 transition" title="Eliminar">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                    </div>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Proceso iniciado el {{ \Carbon\Carbon::parse($proceso->fecha_inicio)->format('d/m/Y') }}
                    · Llamado: {{ $proceso->llamadoAtencion->asunto ?? '—' }}
                </p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $estadoBadge }}">{{ ucfirst($proceso->estado_proceso) }}</span>
        </div>

        {{-- Stepper de etapas --}}
        <div class="mt-8">
            <ol class="flex items-center">
                @foreach($etapaKeys as $i => $key)
                    <li class="flex items-center {{ $i < count($etapaKeys) - 1 ? 'flex-1' : '' }}">
                        <div class="flex w-24 flex-col items-center text-center">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold
                                {{ $i < $indiceActual ? 'bg-[#39A900] text-white' : ($i === $indiceActual ? 'bg-[#39A900] text-white ring-4 ring-green-100' : 'bg-gray-100 text-gray-400') }}">
                                {{ $i < $indiceActual ? '✓' : $i + 1 }}
                            </span>
                            <span class="mt-2 text-xs font-medium {{ $i <= $indiceActual ? 'text-gray-900' : 'text-gray-400' }}">
                                {{ $etapas[$key] }}
                            </span>
                        </div>
                        @if($i < count($etapaKeys) - 1)
                            <div class="h-0.5 flex-1 {{ $i < $indiceActual ? 'bg-[#39A900]' : 'bg-gray-200' }}"></div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>

        @if($proceso->observaciones)
            <p class="mt-6 rounded-lg bg-gray-50 p-4 text-sm text-gray-600">{{ $proceso->observaciones }}</p>
        @endif
    </div>

    {{-- Historial de avances --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="font-semibold text-gray-900">Historial de avances</h3>
        </div>

        <ul class="space-y-0 px-6 py-4">
            @forelse($proceso->historial as $registro)
                <li class="relative flex gap-4 pb-6 last:pb-0">
                    <div class="flex flex-col items-center">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#39A900]"></span>
                        @if(!$loop->last)<span class="mt-1 w-px flex-1 bg-gray-200"></span>@endif
                    </div>
                    <div class="flex-1 pb-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-medium text-gray-900">{{ $etapas[$registro->etapa] ?? $registro->etapa }}</span>
                            <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($registro->fecha_registro)->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">{{ $registro->descripcion }}</p>
                        @if($registro->resultado)
                            <p class="mt-1 text-xs font-medium text-gray-500">Resultado: {{ $registro->resultado }}</p>
                        @endif
                    </div>
                </li>
            @empty
                <li class="py-6 text-center text-sm text-gray-400">Aún no hay avances registrados para este proceso.</li>
            @endforelse
        </ul>

        <form method="POST" action="{{ route('coordinacion.procesos.historial.store', $proceso->id_proceso) }}" class="space-y-3 border-t border-gray-100 px-6 py-5">
            @csrf
            <h4 class="text-sm font-semibold text-gray-900">Registrar nuevo avance</h4>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <select name="etapa" required class="rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                    @foreach($etapas as $key => $label)
                        <option value="{{ $key }}" @selected($proceso->etapa_actual == $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="text" name="resultado" placeholder="Resultado (opcional)"
                       class="rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
            </div>
            <textarea name="descripcion" rows="3" required placeholder="Describe lo ocurrido en esta etapa..."
                      class="w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]"></textarea>
            <div class="flex justify-end">
                <button class="rounded-lg bg-[#39A900] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#2D8200]">
                    Guardar avance
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
