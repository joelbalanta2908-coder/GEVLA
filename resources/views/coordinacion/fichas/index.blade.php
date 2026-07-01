@extends('layouts.coordinador')

@section('titulo', 'Fichas')

@section('contenido')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Fichas de formación</h2>
            <p class="mt-1 text-sm text-gray-500">Crea y administra fichas, sus instructores, el líder y los aprendices matriculados.</p>
        </div>
        <a href="{{ route('coordinacion.fichas.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2D8200]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Nueva ficha
        </a>
    </div>

    {{-- Filtros combinables --}}
    <form method="GET" action="{{ route('coordinacion.fichas.index') }}" data-live-form class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <input type="text" name="buscar" value="{{ $buscar }}" data-live placeholder="N.º de ficha o programa..."
               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30 lg:col-span-2">
        <select name="id_programa" data-live class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            <option value="">Todos los programas</option>
            @foreach($programas as $p)
                <option value="{{ $p->id_programa }}" @selected((string) $programa === (string) $p->id_programa)>{{ $p->nombre_programa }}</option>
            @endforeach
        </select>
        <select name="modalidad" data-live class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            <option value="">Todas las modalidades</option>
            @foreach($modalidades as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected($modalidad === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <select name="estado_ficha" data-live class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                <option value="">Todos los estados</option>
                @foreach($estados as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected($estado === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
            <button class="shrink-0 rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Filtrar</button>
        </div>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="responsive-cards w-full min-w-[720px] text-sm">
            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3">Ficha</th>
                    <th class="px-5 py-3">Programa</th>
                    <th class="px-5 py-3">Modalidad</th>
                    <th class="px-5 py-3">Instructor líder</th>
                    <th class="px-5 py-3 text-center">Aprendices</th>
                    <th class="px-5 py-3 text-center">Instructores</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($fichas as $ficha)
                    @php
                        $lider = optional($ficha->instructorLider)->usuario;
                        $eb = match($ficha->estado_ficha) {
                            'en_ejecucion' => 'bg-[#39A900]/10 text-[#247200]',
                            'terminada' => 'bg-blue-100 text-blue-700',
                            'cancelada' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-semibold text-gray-900" data-label="Ficha">{{ $ficha->numero_ficha }}</td>
                        <td class="px-5 py-3 text-gray-600" data-label="Programa">{{ optional($ficha->programa)->nombre_programa ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600" data-label="Modalidad">{{ $ficha->modalidad_label }}</td>
                        <td class="px-5 py-3 text-gray-600" data-label="Instructor líder">
                            {{ $lider ? trim($lider->nombres.' '.$lider->apellidos) : 'No asignado' }}
                        </td>
                        <td class="px-5 py-3 text-center" data-label="Aprendices">{{ $ficha->matriculas_count }}</td>
                        <td class="px-5 py-3 text-center" data-label="Instructores">{{ $ficha->instructores_count }}</td>
                        <td class="px-5 py-3" data-label="Estado">
                            <span class="estado-badge inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $eb }}">{{ $ficha->estado_label }}</span>
                        </td>
                        <td class="px-5 py-3 text-right" data-label="Acción">
                            <a href="{{ route('coordinacion.fichas.show', $ficha) }}" class="font-medium text-[#39A900] hover:underline">Gestionar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-8 text-center text-gray-400">No se encontraron fichas con los filtros aplicados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($fichas, 'links'))
        {{ $fichas->links() }}
    @endif
</div>
@endsection
