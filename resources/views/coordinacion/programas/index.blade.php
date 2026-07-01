@extends('layouts.coordinador')

@section('titulo', 'Programas de formación')

@section('contenido')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Programas de formación</h2>
            <p class="mt-1 text-sm text-gray-500">Catálogo de programas usado como base para crear fichas.</p>
        </div>
        <a href="{{ route('coordinacion.programas.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2D8200]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Nuevo programa
        </a>
    </div>

    <form method="GET" action="{{ route('coordinacion.programas.index') }}" data-live-form class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input type="text" name="buscar" value="{{ $buscar }}" data-live placeholder="Buscar por nombre o código..."
               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30 sm:max-w-md">
        <button class="rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Filtrar</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="responsive-cards w-full min-w-[640px] text-sm">
            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3">Código</th>
                    <th class="px-5 py-3">Programa</th>
                    <th class="px-5 py-3">Nivel</th>
                    <th class="px-5 py-3 text-center">Duración</th>
                    <th class="px-5 py-3 text-center">Fichas</th>
                    <th class="px-5 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($programas as $programa)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-semibold text-gray-900" data-label="Código">{{ $programa->codigo_programa }}</td>
                        <td class="px-5 py-3 text-gray-700" data-label="Programa">{{ $programa->nombre_programa }}</td>
                        <td class="px-5 py-3 text-gray-600" data-label="Nivel">{{ $programa->nivel_label }}</td>
                        <td class="px-5 py-3 text-center text-gray-600" data-label="Duración">{{ $programa->duracion_meses }} meses</td>
                        <td class="px-5 py-3 text-center" data-label="Fichas">{{ $programa->fichas_count }}</td>
                        <td class="px-5 py-3 text-right" data-label="Acciones">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('coordinacion.programas.edit', $programa) }}" class="font-medium text-[#39A900] hover:underline">Editar</a>
                                @if($programa->fichas_count === 0)
                                    <form method="POST" action="{{ route('coordinacion.programas.destroy', $programa) }}"
                                          onsubmit="return confirm('¿Eliminar este programa? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-medium text-red-500 hover:underline">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No hay programas registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($programas, 'links'))
        {{ $programas->links() }}
    @endif
</div>
@endsection
