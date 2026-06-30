@extends('layouts.instructor')

@section('titulo', 'Registrar Llamado')

@section('contenido')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('instructor.llamados.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver a mis reportes
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-[#00324D]">Reportar Llamado de Atención</h2>
        <p class="mt-1 text-sm text-gray-500">Ingresa los datos correspondientes al llamado de atención. Una vez en revisión por coordinación, no podrás editarlo.</p>

        <form method="POST" action="{{ route('instructor.llamados.store') }}" class="mt-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                @php
                    $aprendicesBuscador = $aprendices->map(fn($a) => [
                        'id' => $a->id_aprendiz,
                        'nombre' => trim(($a->usuario->nombres ?? '') . ' ' . ($a->usuario->apellidos ?? '')),
                    ])->values();
                @endphp
                <div>
                    <label class="block text-sm font-bold text-gray-700">Aprendiz involucrado</label>
                    <div class="relative"
                         x-data="{
                            aprendices: @js($aprendicesBuscador),
                            texto: '',
                            seleccionId: '{{ old('id_aprendiz') }}',
                            abierto: false,
                            get sugerencias() {
                                const q = this.texto.toLowerCase().trim();
                                const base = q ? this.aprendices.filter(a => a.nombre.toLowerCase().includes(q)) : this.aprendices;
                                return base.slice(0, 8);
                            },
                            limpiar() { this.texto = this.texto.replace(/[0-9]/g, ''); this.seleccionId = ''; this.abierto = true; },
                            elegir(a) { this.texto = a.nombre; this.seleccionId = a.id; this.abierto = false; }
                         }"
                         x-init="if (seleccionId) { const a = aprendices.find(x => String(x.id) === String(seleccionId)); if (a) texto = a.nombre; }"
                         @click.away="abierto = false">

                        <input type="hidden" name="id_aprendiz" :value="seleccionId">
                        <input type="text" x-model="texto" @input="limpiar()"
                               @keydown="if (/[0-9]/.test($event.key)) $event.preventDefault()"
                               @focus="abierto = true" autocomplete="off" inputmode="text"
                               placeholder="Escribe el nombre del aprendiz..."
                               class="mt-1 w-full rounded-lg border border-gray-300 text-sm caret-[#39A900] focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/30">

                        <ul x-show="abierto && sugerencias.length" x-cloak
                            class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg">
                            <template x-for="a in sugerencias" :key="a.id">
                                <li @click="elegir(a)" x-text="a.nombre"
                                    class="cursor-pointer px-3 py-2 text-sm text-gray-700 hover:bg-[#39A900]/10"></li>
                            </template>
                        </ul>
                        <p x-show="abierto && texto.length && !sugerencias.length" x-cloak
                           class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-400 shadow-lg">
                            Sin coincidencias
                        </p>
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Escribe para buscar; solo se permiten letras.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Fecha del llamado</label>
                    <input type="date" name="fecha_llamado" required value="{{ old('fecha_llamado', now()->toDateString()) }}" max="{{ now()->toDateString() }}"
                           class="mt-1 w-full rounded-lg border border-gray-300 text-sm caret-[#39A900] focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/30">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Tipo de llamado</label>
                    <select name="tipo_llamado" required class="mt-1 w-full rounded-lg border border-gray-300 text-sm caret-[#39A900] focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/30">
                        @foreach(\App\Models\LlamadoAtencion::tipos() as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected(old('tipo_llamado', \App\Models\LlamadoAtencion::TIPO_LLAMADO_ESCRITO) == $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Categoría</label>
                    <select name="categoria" required class="mt-1 w-full rounded-lg border border-gray-300 text-sm caret-[#39A900] focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/30">
                        <option value="academico" @selected(old('categoria') == 'academico')>Académico</option>
                        <option value="disciplinario" @selected(old('categoria') == 'disciplinario')>Disciplinario</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2"
                 x-data="{ calificacion: '{{ old('calificacion_falta') }}', articulos: @js($articulos) }">
                <div>
                    <label class="block text-sm font-bold text-gray-700">Calificación de la falta</label>
                    <select name="calificacion_falta" x-model="calificacion" required
                            class="mt-1 w-full rounded-lg border border-gray-300 text-sm caret-[#39A900] focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/30">
                        <option value="">Seleccione la calificación...</option>
                        @foreach($calificaciones as $valor => $etiqueta)
                            <option value="{{ $valor }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Leve, Grave o Gravísima (Art. 42 del reglamento).</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Artículo / falta del reglamento</label>
                    <select name="id_articulo" required :disabled="!calificacion"
                            class="mt-1 w-full rounded-lg border border-gray-300 text-sm caret-[#39A900] focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/30 disabled:bg-gray-100 disabled:text-gray-400">
                        <option value="">Seleccione primero la calificación</option>
                        <template x-for="art in (articulos[calificacion] || [])" :key="art.id">
                            <option :value="art.id" x-text="art.texto" :selected="String(art.id) === '{{ old('id_articulo') }}'"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Las opciones cambian según la calificación elegida.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700">Asunto</label>
                <input type="text" name="asunto" required value="{{ old('asunto') }}" placeholder="Ej: Inasistencia injustificada reiterativa"
                       class="mt-1 w-full rounded-lg border border-gray-300 text-sm caret-[#39A900] focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/30">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700">Descripción de los hechos</label>
                <textarea name="descripcion_hechos" rows="4" required
                          class="mt-1 w-full rounded-lg border border-gray-300 text-sm caret-[#39A900] focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/30">{{ old('descripcion_hechos') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700">Pruebas aportadas (Opcional)</label>
                <textarea name="pruebas_aportadas" rows="3" placeholder="Ej: Control de asistencia firmado, pantallazos, etc."
                          class="mt-1 w-full rounded-lg border border-gray-300 text-sm caret-[#39A900] focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/30">{{ old('pruebas_aportadas') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('instructor.llamados.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-[#39A900] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#247200]">
                    Registrar reporte
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
