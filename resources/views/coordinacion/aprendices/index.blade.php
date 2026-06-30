@extends('layouts.coordinador')

@section('titulo', 'Aprendices')

@section('contenido')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Aprendices</h2>
        <p class="mt-1 text-sm text-gray-500">Consulta el historial disciplinario y formativo de cada aprendiz.</p>
    </div>

    <form method="GET" action="{{ route('coordinacion.aprendices.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input type="text" name="buscar" value="{{ $buscar }}" placeholder="Buscar por nombre, apellido o correo..."
               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30 sm:max-w-md">
        <select name="estado_academico" class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            <option value="">Todos los estados</option>
            @foreach($estados as $e)
                <option value="{{ $e }}" @selected($estado == $e)>{{ ucfirst(str_replace('_',' ', $e)) }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Filtrar</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="responsive-cards w-full min-w-[640px] text-sm">
            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3">Aprendiz</th>
                    <th class="px-5 py-3">Correo</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3 text-center">Llamados</th>
                    <th class="px-5 py-3 text-center">Procesos</th>
                    <th class="px-5 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($aprendices as $ap)
                    @php
                        $eb = match($ap->estado_academico) {
                            'en_formacion' => 'bg-[#39A900]/10 text-[#247200]',
                            'aplazado' => 'bg-amber-100 text-amber-700',
                            'cancelado' => 'bg-red-100 text-red-700',
                            'certificado' => 'bg-blue-100 text-blue-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-900" data-label="Aprendiz">{{ optional($ap->usuario)->nombres }} {{ optional($ap->usuario)->apellidos }}</td>
                        <td class="px-5 py-3 text-gray-600" data-label="Correo">{{ $ap->correo_institucional ?? optional($ap->usuario)->correo }}</td>
                        <td class="px-5 py-3" data-label="Estado">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $eb }}">{{ ucfirst(str_replace('_',' ', $ap->estado_academico)) }}</span>
                        </td>
                        <td class="px-5 py-3 text-center" data-label="Llamados">{{ $ap->llamados_atencion_count }}</td>
                        <td class="px-5 py-3 text-center" data-label="Procesos">{{ $ap->procesos_disciplinarios_count }}</td>
                        <td class="px-5 py-3 text-right" data-label="Acción">
                            <a href="{{ route('coordinacion.aprendices.show', $ap->id_aprendiz) }}" class="font-medium text-[#39A900] hover:underline">Ver hoja de vida</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No se encontraron aprendices.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($aprendices, 'links'))
        {{ $aprendices->links() }}
    @endif
</div>
@endsection
