@extends('layouts.coordinador')

@section('titulo', 'Editar acta de coordinación')

@section('contenido')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('coordinacion.actas.show', $acta->id_acta) }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver al detalle
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-[#00324D]">Editar acta {{ $acta->numero_acta }}</h2>
        <p class="mt-1 text-sm text-gray-500">Modifica los detalles del acta registrada.</p>

        <form method="POST" action="{{ route('coordinacion.actas.update', $acta->id_acta) }}" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-bold text-gray-700">Aprendiz infractor</label>
                    <select name="id_aprendiz" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        @foreach($aprendices as $aprendiz)
                            <option value="{{ $aprendiz->id_aprendiz }}" @selected(old('id_aprendiz', $acta->id_aprendiz) == $aprendiz->id_aprendiz)>
                                {{ $aprendiz->usuario->nombres }} {{ $aprendiz->usuario->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Falta asociada</label>
                    <select name="id_falta" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        @foreach($faltas as $falta)
                            <option value="{{ $falta->id_falta }}" @selected(old('id_falta', $acta->id_falta) == $falta->id_falta)>
                                Falta #{{ $falta->id_falta }} - {{ str($falta->calificacion_falta)->replace('_', ' ')->ucfirst() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Tipo de acta</label>
                    <select name="tipo_acta" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="acondicionamiento_academico" @selected(old('tipo_acta', $acta->tipo_acta) == 'acondicionamiento_academico')>Acondicionamiento académico</option>
                        <option value="cancelacion_academica" @selected(old('tipo_acta', $acta->tipo_acta) == 'cancelacion_academica')>Cancelación académica</option>
                        <option value="acondicionamiento_disciplinario" @selected(old('tipo_acta', $acta->tipo_acta) == 'acondicionamiento_disciplinario')>Acondicionamiento disciplinario</option>
                        <option value="cancelacion_disciplinaria" @selected(old('tipo_acta', $acta->tipo_acta) == 'cancelacion_disciplinaria')>Cancelación disciplinaria</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Número de acta</label>
                    <input type="text" name="numero_acta" required value="{{ old('numero_acta', $acta->numero_acta) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Fecha de expedición</label>
                    <input type="date" name="fecha_expedicion" required value="{{ old('fecha_expedicion', $acta->fecha_expedicion) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Estado del acta</label>
                    <select name="estado_acta" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="expedido" @selected(old('estado_acta', $acta->estado_acta) == 'expedido')>Expedida</option>
                        <option value="notificado" @selected(old('estado_acta', $acta->estado_acta) == 'notificado')>Notificada</option>
                        <option value="firme" @selected(old('estado_acta', $acta->estado_acta) == 'firme')>Firme</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700">Fecha de notificación personal</label>
                    <input type="date" name="fecha_notificacion_personal" value="{{ old('fecha_notificacion_personal', $acta->fecha_notificacion_personal) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700">Fecha de firmeza</label>
                    <input type="date" name="fecha_firmeza" value="{{ old('fecha_firmeza', $acta->fecha_firmeza) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700">Meses de inhabilitación</label>
                    <input type="number" name="meses_inhabilitacion" value="{{ old('meses_inhabilitacion', $acta->meses_inhabilitacion) }}" min="0" placeholder="Ej: 6"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700">Descripción de la sanción</label>
                <textarea name="sancion_descripcion" rows="4" required
                          class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">{{ old('sancion_descripcion', $acta->sancion_descripcion) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('coordinacion.actas.show', $acta->id_acta) }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
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
