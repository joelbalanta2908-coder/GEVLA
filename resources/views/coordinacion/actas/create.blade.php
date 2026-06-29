@extends('layouts.coordinador')

@section('titulo', 'Expedir acta de coordinación')

@section('contenido')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('coordinacion.actas.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver a actas
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-gray-900">Expedir acta de coordinación</h2>
        <p class="mt-1 text-sm text-gray-500">Diligencia los datos del acta según la falta y el proceso disciplinario relacionado.</p>

        <form method="POST" action="{{ route('coordinacion.actas.store') }}" class="mt-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Aprendiz</label>
                    <select name="id_aprendiz" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="">Selecciona un aprendiz</option>
                        @foreach($aprendices as $aprendiz)
                            <option value="{{ $aprendiz->id_aprendiz }}"
                                @selected(old('id_aprendiz', $llamadoSeleccionado->id_aprendiz ?? null) == $aprendiz->id_aprendiz)>
                                {{ $aprendiz->usuario->nombres }} {{ $aprendiz->usuario->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Falta relacionada</label>
                    <select name="id_falta" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="">Selecciona una falta</option>
                        @foreach($faltas as $falta)
                            <option value="{{ $falta->id_falta }}">
                                {{ $falta->principio_valor_infringido }} — {{ str($falta->calificacion_falta)->ucfirst() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo de acta</label>
                    <select name="tipo_acta" required class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                        <option value="acondicionamiento_academico">Acondicionamiento académico</option>
                        <option value="cancelacion_academica">Cancelación académica</option>
                        <option value="acondicionamiento_disciplinario">Acondicionamiento disciplinario</option>
                        <option value="cancelacion_disciplinaria">Cancelación disciplinaria</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Número de acta</label>
                    <input type="text" name="numero_acta" required placeholder="AC-2026-004"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha de expedición</label>
                    <input type="date" name="fecha_expedicion" required value="{{ old('fecha_expedicion', now()->toDateString()) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Meses de inhabilitación</label>
                    <input type="number" name="meses_inhabilitacion" min="0" placeholder="Solo aplica a cancelaciones"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Descripción de la sanción</label>
                <textarea name="sancion_descripcion" rows="4" required
                          class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]"
                          placeholder="Describe la medida adoptada y su justificación..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('coordinacion.actas.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-[#39A900] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#2D8200]">
                    Expedir acta
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
