@extends('layouts.coordinador')

@section('titulo', 'Editar proceso disciplinario')

@section('contenido')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('coordinacion.procesos.show', $proceso->id_proceso) }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver al detalle
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-[#00324D]">Editar proceso disciplinario</h2>
        <p class="mt-1 text-sm text-gray-500">Modifica los detalles generales del proceso en curso.</p>

        <form method="POST" action="{{ route('coordinacion.procesos.update', $proceso->id_proceso) }}" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-bold text-gray-700">Aprendiz</label>
                    <select name="id_aprendiz" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        @foreach($aprendices as $aprendiz)
                            <option value="{{ $aprendiz->id_aprendiz }}" @selected(old('id_aprendiz', $proceso->id_aprendiz) == $aprendiz->id_aprendiz)>
                                {{ $aprendiz->usuario->nombres }} {{ $aprendiz->usuario->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Llamado asociado (Opcional)</label>
                    <select name="id_llamado" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="">Sin llamado previo</option>
                        @foreach($llamados as $llamado)
                            <option value="{{ $llamado->id_llamado }}" @selected(old('id_llamado', $proceso->id_llamado) == $llamado->id_llamado)>
                                {{ $llamado->aprendiz->usuario->nombres }} - {{ $llamado->asunto }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Fecha de inicio</label>
                    <input type="date" name="fecha_inicio" required value="{{ old('fecha_inicio', $proceso->fecha_inicio) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Etapa actual</label>
                    <select name="etapa_actual" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="llamado_escrito" @selected(old('etapa_actual', $proceso->etapa_actual) == 'llamado_escrito')>Llamado escrito</option>
                        <option value="acondicionamiento" @selected(old('etapa_actual', $proceso->etapa_actual) == 'acondicionamiento')>Acondicionamiento</option>
                        <option value="cancelacion_matricula" @selected(old('etapa_actual', $proceso->etapa_actual) == 'cancelacion_matricula')>Cancelación de matrícula</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Estado del proceso</label>
                    <select name="estado_proceso" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="activo" @selected(old('estado_proceso', $proceso->estado_proceso) == 'activo')>Activo</option>
                        <option value="suspendido" @selected(old('estado_proceso', $proceso->estado_proceso) == 'suspendido')>Suspendido</option>
                        <option value="finalizado" @selected(old('estado_proceso', $proceso->estado_proceso) == 'finalizado')>Finalizado</option>
                        <option value="apelacion" @selected(old('estado_proceso', $proceso->estado_proceso) == 'apelacion')>En apelación</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700">Observaciones</label>
                <textarea name="observaciones" rows="3"
                          class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]"
                          placeholder="Anotaciones...">{{ old('observaciones', $proceso->observaciones) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('coordinacion.procesos.show', $proceso->id_proceso) }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-[#39A900] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#247200]">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
