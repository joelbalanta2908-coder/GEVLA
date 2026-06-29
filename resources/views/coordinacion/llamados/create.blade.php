@extends('layouts.coordinador')

@section('titulo', 'Crear llamado de atención')

@section('contenido')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('coordinacion.llamados.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver a llamados
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-[#00324D]">Registrar nuevo llamado de atención</h2>
        <p class="mt-1 text-sm text-gray-500">Diligencia la información para registrar un nuevo llamado.</p>

        <form method="POST" action="{{ route('coordinacion.llamados.store') }}" class="mt-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-bold text-gray-700">Aprendiz</label>
                    <select name="id_aprendiz" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="">Selecciona un aprendiz</option>
                        @foreach($aprendices as $aprendiz)
                            <option value="{{ $aprendiz->id_aprendiz }}" @selected(old('id_aprendiz') == $aprendiz->id_aprendiz)>
                                {{ $aprendiz->usuario->nombres }} {{ $aprendiz->usuario->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Instructor reporta</label>
                    <select name="id_instructor" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="">Selecciona un instructor</option>
                        @foreach($instructores as $instructor)
                            <option value="{{ $instructor->id_instructor }}" @selected(old('id_instructor') == $instructor->id_instructor)>
                                {{ $instructor->usuario->nombres }} {{ $instructor->usuario->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Fecha del llamado</label>
                    <input type="date" name="fecha_llamado" required value="{{ old('fecha_llamado', now()->toDateString()) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Tipo de llamado</label>
                    <select name="tipo_llamado" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="llamado_escrito" @selected(old('tipo_llamado') == 'llamado_escrito')>Llamado escrito</option>
                        <option value="acondicionamiento" @selected(old('tipo_llamado') == 'acondicionamiento')>Acondicionamiento</option>
                        <option value="cancelacion_matricula" @selected(old('tipo_llamado') == 'cancelacion_matricula')>Cancelación de matrícula</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Categoría</label>
                    <select name="categoria" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="academico" @selected(old('categoria') == 'academico')>Académico</option>
                        <option value="disciplinario" @selected(old('categoria') == 'disciplinario')>Disciplinario</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700">Estado inicial</label>
                    <select name="estado_llamado" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="registrado" @selected(old('estado_llamado') == 'registrado')>Registrado</option>
                        <option value="en_revision" @selected(old('estado_llamado') == 'en_revision')>En revisión</option>
                        <option value="notificado" @selected(old('estado_llamado') == 'notificado')>Notificado</option>
                        <option value="cerrado" @selected(old('estado_llamado') == 'cerrado')>Cerrado</option>
                        <option value="cancelado" @selected(old('estado_llamado') == 'cancelado')>Cancelado</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700">Asunto</label>
                <input type="text" name="asunto" required value="{{ old('asunto') }}" placeholder="Resumen del llamado..."
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700">Descripción de los hechos</label>
                <textarea name="descripcion_hechos" rows="4" required
                          class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]"
                          placeholder="Describe de manera detallada y objetiva lo ocurrido...">{{ old('descripcion_hechos') }}</textarea>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700">Pruebas aportadas (Opcional)</label>
                <textarea name="pruebas_aportadas" rows="2"
                          class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]"
                          placeholder="Enlaces a documentos, actas firmadas, fotos...">{{ old('pruebas_aportadas') }}</textarea>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700">Observaciones (Opcional)</label>
                <textarea name="observaciones" rows="2"
                          class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]"
                          placeholder="Anotaciones internas del coordinador...">{{ old('observaciones') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('coordinacion.llamados.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-[#39A900] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#247200]">
                    Crear llamado
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
