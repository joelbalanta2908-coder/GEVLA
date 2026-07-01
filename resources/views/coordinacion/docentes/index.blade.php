@extends('layouts.coordinador')

@section('titulo', 'Docentes')

@section('contenido')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Docentes</h2>
        <p class="mt-1 text-sm text-gray-500">Instructores a cargo: fichas asignadas, liderazgo y tipo de docente.</p>
    </div>

    <form method="GET" action="{{ route('coordinacion.docentes.index') }}" data-live-form class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <input type="text" name="buscar" value="{{ $buscar }}" data-live placeholder="Nombre, documento, código o área..."
               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30 lg:col-span-2">
        <select name="tipo_docente" data-live class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            <option value="">Todos los tipos</option>
            @foreach($tipos as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected($tipo === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        <select name="estado_instructor" data-live class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            <option value="">Todos los estados</option>
            <option value="activo" @selected($estado === 'activo')>Activo</option>
            <option value="inactivo" @selected($estado === 'inactivo')>Inactivo</option>
        </select>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="responsive-cards w-full min-w-[820px] text-sm">
            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3">Docente</th>
                    <th class="px-5 py-3">Documento</th>
                    <th class="px-5 py-3">Área</th>
                    <th class="px-5 py-3">Tipo</th>
                    <th class="px-5 py-3 text-center">Fichas</th>
                    <th class="px-5 py-3">Líder</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($docentes as $docente)
                    @php $du = $docente->usuario; $esLider = $docente->fichas_lideradas_count > 0; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3" data-label="Docente">
                            <p class="font-semibold text-gray-900">{{ $du ? trim($du->nombres.' '.$du->apellidos) : $docente->codigo_instructor }}</p>
                            <p class="text-xs text-gray-500">{{ $docente->codigo_instructor }}{{ $du?->correo ? ' · '.$du->correo : '' }}</p>
                        </td>
                        <td class="px-5 py-3 text-gray-600" data-label="Documento">{{ $du?->tipo_documento }} {{ $du?->numero_documento }}</td>
                        <td class="px-5 py-3 text-gray-600" data-label="Área">{{ $docente->area_formacion ?? '—' }}</td>
                        <td class="px-5 py-3" data-label="Tipo">
                            <form method="POST" action="{{ route('coordinacion.docentes.tipo', $docente->id_instructor) }}" data-live-form>
                                @csrf
                                @method('PATCH')
                                <select name="tipo_docente" data-live class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                                    <option value="" @selected($docente->tipo_docente === null)>No definido</option>
                                    @foreach($tipos as $valor => $etiqueta)
                                        <option value="{{ $valor }}" @selected($docente->tipo_docente === $valor)>{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="px-5 py-3 text-center" data-label="Fichas">
                            @if($docente->fichas_count > 0)
                                <span class="estado-badge inline-flex rounded-full bg-[#39A900]/10 px-2.5 py-1 text-xs font-medium text-[#247200]">{{ $docente->fichas_count }}</span>
                            @else
                                <span class="text-xs text-gray-400">Sin ficha</span>
                            @endif
                        </td>
                        <td class="px-5 py-3" data-label="Líder">
                            @if($esLider)
                                <span class="estado-badge inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">★ Líder</span>
                            @else
                                <span class="text-xs text-gray-400">No</span>
                            @endif
                        </td>
                        <td class="px-5 py-3" data-label="Estado">
                            <span class="estado-badge inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $docente->estado_instructor === 'activo' ? 'bg-[#39A900]/10 text-[#247200]' : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($docente->estado_instructor) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right" data-label="Acción">
                            <a href="{{ route('coordinacion.docentes.show', $docente->id_instructor) }}" class="font-medium text-[#39A900] hover:underline">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-8 text-center text-gray-400">No se encontraron docentes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($docentes, 'links'))
        {{ $docentes->links() }}
    @endif
</div>
@endsection
