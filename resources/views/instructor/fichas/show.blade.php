@extends('layouts.instructor')

@section('titulo', 'Historial disciplinario · Ficha ' . $ficha->numero_ficha)

@section('contenido')
@php
    $lider = optional($ficha->instructorLider)->usuario;
    $estadoLlamadoBadge = fn ($estado) => match($estado) {
        'registrado'  => 'bg-slate-100 text-slate-600',
        'en_revision' => 'bg-amber-100 text-amber-700',
        'notificado'  => 'bg-blue-100 text-blue-700',
        'cerrado'     => 'bg-[#39A900]/10 text-[#247200]',
        'cancelado'   => 'bg-red-100 text-red-700',
        default       => 'bg-slate-100 text-slate-600',
    };
    $estadoProcesoBadge = fn ($estado) => match($estado) {
        'activo'  => 'bg-amber-100 text-amber-700',
        'cerrado' => 'bg-[#39A900]/10 text-[#247200]',
        'anulado' => 'bg-red-100 text-red-700',
        default   => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="space-y-6">
    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-4">
            <a href="{{ route('instructor.fichas.index') }}" class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900] ring-1 ring-[#39A900]/15 transition hover:bg-[#39A900]/20">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
            </a>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-[#39A900]">Consulta disciplinaria</p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">Ficha {{ $ficha->numero_ficha }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ optional($ficha->programa)->nombre_programa ?? 'Programa' }}</p>
                <p class="mt-0.5 text-xs font-semibold text-[#39A900]">
                    Instructor líder: {{ $lider ? trim($lider->nombres.' '.$lider->apellidos) : 'No asignado' }}
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-blue-100 bg-blue-50/60 px-4 py-3 text-xs text-blue-700">
        Puedes consultar el historial disciplinario de todos los aprendices de esta ficha. Solo puedes editar los llamados de atención que tú registraste y que aún estén en estado «Registrado».
    </div>

    @php $matriculas = $ficha->matriculas->sortBy(fn ($m) => optional(optional($m->aprendiz)->usuario)->apellidos); @endphp

    @forelse($matriculas as $m)
        @php $ap = $m->aprendiz; $au = optional($ap)->usuario; @endphp
        @continue(! $ap)
        <div x-data="{ abierto: false }" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <button type="button" @click="abierto = !abierto" class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left transition hover:bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-600">
                        {{ $au?->iniciales() ?? 'A' }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ $au ? trim($au->nombres.' '.$au->apellidos) : 'Aprendiz #'.$ap->id_aprendiz }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $ap->llamadosAtencion->count() }} llamado(s) · {{ $ap->procesosDisciplinarios->count() }} proceso(s)
                            @if($m->estado_matricula !== 'activa')
                                · <span class="font-semibold">{{ ucfirst($m->estado_matricula) }}</span>
                            @endif
                        </p>
                    </div>
                </div>
                <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" :class="abierto && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
            </button>

            <div x-show="abierto" x-cloak class="space-y-6 border-t border-gray-100 px-6 py-5">
                {{-- Llamados de atención --}}
                <div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Llamados de atención</p>
                    @if($ap->llamadosAtencion->isEmpty())
                        <p class="text-sm text-gray-400">Sin llamados de atención registrados.</p>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-gray-100">
                            <table class="w-full min-w-[640px] text-left text-sm">
                                <thead class="bg-gray-50 text-xs font-medium uppercase text-gray-500">
                                    <tr>
                                        <th class="px-4 py-2.5">Fecha</th>
                                        <th class="px-4 py-2.5">Asunto</th>
                                        <th class="px-4 py-2.5">Registró</th>
                                        <th class="px-4 py-2.5">Estado</th>
                                        <th class="px-4 py-2.5 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($ap->llamadosAtencion as $ll)
                                        @php
                                            $reg = optional(optional($ll->instructor)->usuario);
                                            $esPropio = (int) $ll->id_instructor === (int) $instructor->id_instructor;
                                            $puedeEditar = $esPropio && $ll->estado_llamado === 'registrado';
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2.5 text-gray-600">{{ \Carbon\Carbon::parse($ll->fecha_llamado)->format('d/m/Y') }}</td>
                                            <td class="px-4 py-2.5 text-gray-800">
                                                {{ $ll->asunto }}
                                                @if($ll->observaciones)
                                                    <span class="block text-xs text-gray-400">Obs.: {{ \Illuminate\Support\Str::limit($ll->observaciones, 80) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2.5 text-gray-600">
                                                {{ trim(($reg->nombres ?? '').' '.($reg->apellidos ?? '')) ?: '—' }}
                                                @if($esPropio)<span class="ml-1 rounded bg-[#39A900]/10 px-1.5 py-0.5 text-[10px] font-semibold text-[#247200]">Tú</span>@endif
                                            </td>
                                            <td class="px-4 py-2.5">
                                                <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $estadoLlamadoBadge($ll->estado_llamado) }}">
                                                    {{ str($ll->estado_llamado)->replace('_',' ')->ucfirst() }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2.5 text-right">
                                                <a href="{{ route('instructor.llamados.show', $ll->id_llamado) }}" class="text-xs font-semibold text-blue-600 hover:underline">Ver</a>
                                                @if($puedeEditar)
                                                    <a href="{{ route('instructor.llamados.edit', $ll->id_llamado) }}" class="ml-2 text-xs font-semibold text-amber-600 hover:underline">Editar</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Procesos disciplinarios --}}
                <div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Procesos disciplinarios</p>
                    @if($ap->procesosDisciplinarios->isEmpty())
                        <p class="text-sm text-gray-400">Sin procesos disciplinarios.</p>
                    @else
                        <ul class="space-y-2">
                            @foreach($ap->procesosDisciplinarios as $proc)
                                @php $regProc = optional(optional(optional($proc->llamadoAtencion)->instructor)->usuario); @endphp
                                <li class="rounded-xl border border-gray-100 px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-gray-800">
                                            Etapa: {{ str($proc->etapa_actual)->replace('_',' ')->ucfirst() }}
                                        </p>
                                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $estadoProcesoBadge($proc->estado_proceso) }}">
                                            {{ ucfirst($proc->estado_proceso) }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Inicio {{ optional($proc->fecha_inicio)->format('d/m/Y') ?? '—' }}
                                        @if($proc->fecha_cierre) · Cierre {{ $proc->fecha_cierre->format('d/m/Y') }} @endif
                                        @if($regProc->nombres ?? null) · Origen: {{ trim($regProc->nombres.' '.$regProc->apellidos) }} @endif
                                    </p>
                                    @if($proc->observaciones)
                                        <p class="mt-1 text-xs text-gray-500">Obs.: {{ $proc->observaciones }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="text-right">
                    <a href="{{ route('instructor.aprendices.show', $ap->id_aprendiz) }}" class="text-sm font-semibold text-[#39A900] hover:underline">Ver hoja de vida completa →</a>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center text-gray-500 shadow-sm">
            <p class="text-sm">Esta ficha no tiene aprendices matriculados.</p>
        </div>
    @endforelse
</div>
@endsection
