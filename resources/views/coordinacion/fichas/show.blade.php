@extends('layouts.coordinador')

@section('titulo', 'Ficha ' . $ficha->numero_ficha)

@section('contenido')
@php
    $lider = optional($ficha->instructorLider)->usuario;
    $liderId = $ficha->id_instructor_lider;
    $eb = match($ficha->estado_ficha) {
        'en_ejecucion' => 'bg-[#39A900]/10 text-[#247200]',
        'terminada' => 'bg-blue-100 text-blue-700',
        'cancelada' => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-600',
    };
    $matriculasActivas = $ficha->matriculas->where('estado_matricula', 'activa');
@endphp

<div class="space-y-6">
    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-4">
            <a href="{{ route('coordinacion.fichas.index') }}" class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900] ring-1 ring-[#39A900]/15 transition hover:bg-[#39A900]/20">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-gray-900">Ficha {{ $ficha->numero_ficha }}</h2>
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $eb }}">{{ $ficha->estado_label }}</span>
                </div>
                <p class="mt-1 text-sm text-gray-600">{{ optional($ficha->programa)->nombre_programa ?? 'Programa' }}</p>
                <p class="mt-0.5 text-xs text-gray-500">
                    {{ $ficha->modalidad_label }}
                    · Inicio {{ optional($ficha->fecha_inicio)->format('d/m/Y') ?? '—' }}
                    · Fin {{ optional($ficha->fecha_fin_programada)->format('d/m/Y') ?? '—' }}
                </p>
                <p class="mt-1 text-xs font-semibold text-[#39A900]">
                    ★ Instructor líder: {{ $lider ? trim($lider->nombres.' '.$lider->apellidos) : 'No asignado' }}
                    @if($ficha->fecha_asignacion_lider)
                        <span class="font-normal text-gray-400">(desde {{ $ficha->fecha_asignacion_lider->format('d/m/Y') }})</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <a href="{{ route('coordinacion.fichas.edit', $ficha) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">Editar</a>
            <form method="POST" action="{{ route('coordinacion.fichas.destroy', $ficha) }}"
                  onsubmit="return confirm('¿Eliminar esta ficha? Esta acción no se puede deshacer.');">
                @csrf
                @method('DELETE')
                <button class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Eliminar</button>
            </form>
        </div>
    </div>

    {{-- Cambiar estado --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Estado de la ficha</p>
        <form method="POST" action="{{ route('coordinacion.fichas.actualizarEstado', $ficha) }}" class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center">
            @csrf
            @method('PATCH')
            <select name="estado_ficha" class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30 sm:max-w-xs">
                @foreach(\App\Models\Ficha::estados() as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected($ficha->estado_ficha === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Actualizar estado</button>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Instructores --}}
        <div class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Instructores asociados</p>
                <span class="text-xs text-gray-400">{{ $ficha->instructores->count() }} en total</span>
            </div>

            <ul class="divide-y divide-gray-100">
                @forelse($ficha->instructores as $ins)
                    @php $iu = $ins->usuario; $esLider = (int) $ins->id_instructor === (int) $liderId; @endphp
                    <li class="flex items-center justify-between gap-3 py-2.5">
                        <span class="text-sm text-gray-700">
                            @if($esLider)<span class="text-[#39A900]">★</span> @endif
                            {{ $iu ? trim($iu->nombres.' '.$iu->apellidos) : $ins->codigo_instructor }}
                            <span class="text-xs text-gray-400">({{ $ins->codigo_instructor }})</span>
                            @if($esLider)<span class="ml-1 rounded-full bg-[#39A900]/10 px-2 py-0.5 text-[10px] font-semibold text-[#247200]">Líder</span>@endif
                        </span>
                        @unless($esLider)
                            <form method="POST" action="{{ route('coordinacion.fichas.instructores.destroy', [$ficha, $ins->id_instructor]) }}"
                                  onsubmit="return confirm('¿Desasociar a este instructor de la ficha?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-xs font-semibold text-red-500 hover:underline">Quitar</button>
                            </form>
                        @endunless
                    </li>
                @empty
                    <li class="py-3 text-sm text-gray-400">Sin instructores asociados.</li>
                @endforelse
            </ul>

            {{-- Asociar instructores --}}
            @if($instructoresDisponibles->isNotEmpty())
                <form method="POST" action="{{ route('coordinacion.fichas.instructores.store', $ficha) }}" class="space-y-3 border-t border-gray-100 pt-4">
                    @csrf
                    <p class="text-sm font-semibold text-gray-700">Asociar instructores</p>
                    <div class="max-h-40 space-y-1 overflow-y-auto rounded-lg border border-gray-200 p-2">
                        @foreach($instructoresDisponibles as $ins)
                            @php $iu = $ins->usuario; @endphp
                            <label class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-gray-700 hover:bg-gray-50">
                                <input type="checkbox" name="instructores[]" value="{{ $ins->id_instructor }}" class="rounded border-gray-300 text-[#39A900] focus:ring-[#39A900]/30">
                                {{ $iu ? trim($iu->nombres.' '.$iu->apellidos) : $ins->codigo_instructor }}
                                <span class="text-xs text-gray-400">({{ $ins->codigo_instructor }})</span>
                            </label>
                        @endforeach
                    </div>
                    <button class="rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Asociar seleccionados</button>
                </form>
            @endif

            {{-- Designar instructor líder (solo Coordinador Misional) --}}
            <div class="border-t border-gray-100 pt-4">
                <p class="text-sm font-semibold text-gray-700">Instructor líder</p>
                @if($puedeDesignarLider)
                    @if($ficha->instructores->isNotEmpty())
                        <form method="POST" action="{{ route('coordinacion.fichas.lider', $ficha) }}" class="mt-2 flex flex-col gap-2 sm:flex-row">
                            @csrf
                            @method('PUT')
                            <select name="id_instructor_lider" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                                @foreach($ficha->instructores as $ins)
                                    @php $iu = $ins->usuario; @endphp
                                    <option value="{{ $ins->id_instructor }}" @selected((int) $ins->id_instructor === (int) $liderId)>
                                        {{ $iu ? trim($iu->nombres.' '.$iu->apellidos) : $ins->codigo_instructor }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="shrink-0 rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Designar líder</button>
                        </form>
                        <p class="mt-1 text-xs text-gray-400">Solo puedes elegir un instructor ya asociado a la ficha.</p>
                    @else
                        <p class="mt-2 text-xs text-gray-400">Asocia al menos un instructor para poder designar un líder.</p>
                    @endif
                @else
                    <p class="mt-2 text-xs text-gray-400">La designación de instructor líder está reservada al Coordinador Misional.</p>
                @endif
            </div>
        </div>

        {{-- Aprendices --}}
        <div class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Aprendices matriculados</p>
                <span class="text-xs text-gray-400">{{ $matriculasActivas->count() }} activos</span>
            </div>

            <ul class="divide-y divide-gray-100">
                @forelse($ficha->matriculas as $m)
                    @php $ap = $m->aprendiz; $au = optional($ap)->usuario; @endphp
                    @if($ap)
                        <li class="flex items-center justify-between gap-3 py-2.5">
                            <span class="text-sm text-gray-700">
                                {{ $au ? trim($au->nombres.' '.$au->apellidos) : 'Aprendiz #'.$ap->id_aprendiz }}
                                @if($m->estado_matricula !== 'activa')
                                    <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-500">{{ ucfirst($m->estado_matricula) }}</span>
                                @endif
                            </span>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('coordinacion.aprendices.show', $ap->id_aprendiz) }}" class="text-xs font-semibold text-[#39A900] hover:underline">Ver</a>
                                @if($m->estado_matricula === 'activa')
                                    <form method="POST" action="{{ route('coordinacion.fichas.aprendices.destroy', [$ficha, $m->id_matricula]) }}"
                                          onsubmit="return confirm('¿Retirar a este aprendiz de la ficha?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs font-semibold text-red-500 hover:underline">Retirar</button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @endif
                @empty
                    <li class="py-3 text-sm text-gray-400">Sin aprendices matriculados.</li>
                @endforelse
            </ul>

            {{-- Matricular aprendices --}}
            @if($aprendicesDisponibles->isNotEmpty())
                <form method="POST" action="{{ route('coordinacion.fichas.aprendices.store', $ficha) }}" class="space-y-3 border-t border-gray-100 pt-4"
                      x-data="{ q: '' }">
                    @csrf
                    <p class="text-sm font-semibold text-gray-700">Matricular aprendices</p>
                    <input type="text" x-model="q" placeholder="Filtrar por nombre..."
                           class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                    <div class="max-h-40 space-y-1 overflow-y-auto rounded-lg border border-gray-200 p-2">
                        @foreach($aprendicesDisponibles as $ap)
                            @php $au = $ap->usuario; $nombre = $au ? trim($au->nombres.' '.$au->apellidos) : ('Aprendiz #'.$ap->id_aprendiz); @endphp
                            <label class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                                   x-show="q === '' || '{{ \Illuminate\Support\Str::lower($nombre) }}'.includes(q.toLowerCase())">
                                <input type="checkbox" name="aprendices[]" value="{{ $ap->id_aprendiz }}" class="rounded border-gray-300 text-[#39A900] focus:ring-[#39A900]/30">
                                {{ $nombre }}
                            </label>
                        @endforeach
                    </div>
                    <button class="rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Matricular seleccionados</button>
                    <p class="text-xs text-gray-400">Un aprendiz no puede tener matrícula activa en otra ficha del mismo programa.</p>
                </form>
            @endif
        </div>
    </div>

    {{-- Historial de instructor líder --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Historial de instructor líder</p>
        @php $historial = $ficha->historialLider->sortByDesc('fecha_cambio'); @endphp
        @if($historial->isEmpty())
            <p class="mt-3 text-sm text-gray-400">Sin cambios registrados.</p>
        @else
            <ul class="mt-3 space-y-3">
                @foreach($historial as $h)
                    @php
                        $ant = optional(optional($h->instructorAnterior)->usuario);
                        $nue = optional(optional($h->instructorNuevo)->usuario);
                        $reg = $h->usuarioRegistra;
                    @endphp
                    <li class="flex items-start gap-3 text-sm">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-[#39A900]"></span>
                        <div>
                            <p class="text-gray-700">
                                @if($h->instructorAnterior)
                                    <span class="text-gray-500">{{ trim(($ant->nombres ?? '').' '.($ant->apellidos ?? '')) }}</span> →
                                @endif
                                <span class="font-semibold text-gray-900">{{ trim(($nue->nombres ?? '').' '.($nue->apellidos ?? '')) ?: 'Instructor #'.$h->id_instructor_nuevo }}</span>
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ optional($h->fecha_cambio)->format('d/m/Y H:i') }}
                                @if($reg) · por {{ trim(($reg->nombres ?? '').' '.($reg->apellidos ?? '')) }} @endif
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
