@extends('layouts.instructor')

@section('titulo', 'Editar Llamado')

@section('contenido')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('instructor.llamados.show', $llamadoModel->id_llamado) }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver al detalle
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-[#00324D]">Editar Llamado de Atención #{{ $llamadoModel->id_llamado }}</h2>
        <p class="mt-1 text-sm text-gray-500">Solo puedes modificar los llamados que se encuentran en estado "Registrado".</p>

        <form method="POST" action="{{ route('instructor.llamados.update', $llamadoModel->id_llamado) }}" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-bold text-gray-700">Aprendiz involucrado</label>
                    <select name="id_aprendiz" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        @foreach($aprendices as $aprendiz)
                            <option value="{{ $aprendiz->id_aprendiz }}" @selected(old('id_aprendiz', $llamadoModel->id_aprendiz) == $aprendiz->id_aprendiz)>
                                {{ $aprendiz->usuario->nombres }} {{ $aprendiz->usuario->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Fecha del llamado</label>
                    <input type="date" name="fecha_llamado" required value="{{ old('fecha_llamado', $llamadoModel->fecha_llamado) }}" max="{{ now()->toDateString() }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Tipo de llamado</label>
                    <select name="tipo_llamado" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="verbal" @selected(old('tipo_llamado', $llamadoModel->tipo_llamado) == 'verbal')>Verbal</option>
                        <option value="escrito" @selected(old('tipo_llamado', $llamadoModel->tipo_llamado) == 'escrito')>Escrito</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Categoría</label>
                    <select name="categoria" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="academico" @selected(old('categoria', $llamadoModel->categoria) == 'academico')>Académico</option>
                        <option value="disciplinario" @selected(old('categoria', $llamadoModel->categoria) == 'disciplinario')>Disciplinario</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700">Asunto</label>
                <input type="text" name="asunto" required value="{{ old('asunto', $llamadoModel->asunto) }}"
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700">Descripción de los hechos</label>
                <textarea name="descripcion_hechos" rows="4" required
                          class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">{{ old('descripcion_hechos', $llamadoModel->descripcion_hechos) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700">Pruebas aportadas (Opcional)</label>
                <textarea name="pruebas_aportadas" rows="3"
                          class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">{{ old('pruebas_aportadas', $llamadoModel->pruebas_aportadas) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('instructor.llamados.show', $llamadoModel->id_llamado) }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
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
