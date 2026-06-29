@extends('layouts.coordinador')

@section('titulo', 'Llamados de atención')

@section('contenido')
<div class="space-y-6">

    <div>
        <h2 class="text-2xl font-bold text-gray-900">Llamados de atención</h2>
        <p class="text-gray-500">Revisa y da seguimiento a los llamados reportados por los instructores.</p>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por aprendiz o asunto"
               class="min-w-[220px] flex-1 rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">

        <select name="categoria" class="rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
            <option value="">Categoría: todas</option>
            <option value="academico" @selected(request('categoria') == 'academico')>Académico</option>
            <option value="disciplinario" @selected(request('categoria') == 'disciplinario')>Disciplinario</option>
        </select>

        <select name="estado" class="rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
            <option value="">Estado: todos</option>
            @foreach(['registrado','en_revision','notificado','cerrado','cancelado'] as $estado)
                <option value="{{ $estado }}" @selected(request('estado') == $estado)>
                    {{ str($estado)->replace('_',' ')->ucfirst() }}
                </option>
            @endforeach
        </select>

        <button class="rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Filtrar</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3">Aprendiz</th>
                    <th class="px-5 py-3">Instructor</th>
                    <th class="px-5 py-3">Fecha</th>
                    <th class="px-5 py-3">Categoría</th>
                    <th class="px-5 py-3">Asunto</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($llamados as $llamado)
                    @php
                        $estadoBadge = match($llamado->estado_llamado) {
                            'registrado'  => 'bg-gray-100 text-gray-600',
                            'en_revision' => 'bg-amber-100 text-amber-700',
                            'notificado'  => 'bg-blue-100 text-blue-700',
                            'cerrado'     => 'bg-green-100 text-green-700',
                            'cancelado'   => 'bg-red-100 text-red-700',
                            default       => 'bg-gray-100 text-gray-600',
                        };
                        $catBadge = $llamado->categoria === 'disciplinario' ? 'bg-rose-50 text-rose-600' : 'bg-sky-50 text-sky-600';
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $llamado->aprendiz->usuario->nombres }} {{ $llamado->aprendiz->usuario->apellidos }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $llamado->instructor->usuario->nombres }} {{ $llamado->instructor->usuario->apellidos }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ \Carbon\Carbon::parse($llamado->fecha_llamado)->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $catBadge }}">{{ ucfirst($llamado->categoria) }}</span>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $llamado->asunto }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $estadoBadge }}">
                                {{ str($llamado->estado_llamado)->replace('_',' ')->ucfirst() }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('coordinacion.llamados.show', $llamado->id_llamado) }}" class="font-medium text-[#39A900] hover:underline">Ver detalle</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">No se encontraron llamados con los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($llamados ?? null, 'links'))
        {{ $llamados->links() }}
    @endif
</div>
@endsection
