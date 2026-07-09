@extends('layouts.coordinador')

@section('titulo', 'Expedir acta de coordinación')

@section('contenido')
@php
    // Datos del selector de fichas: etiqueta con número y programa para que la
    // búsqueda parcial aplique a ambos campos.
    $fichasCombo = $fichas->map(fn ($f) => [
        'id'    => $f->id_ficha,
        'label' => 'Ficha ' . $f->numero_ficha . ($f->programa?->nombre_programa ? ' — ' . $f->programa->nombre_programa : ''),
    ])->values();

    // Aprendiz preseleccionado (old() o llamado de origen), solo si pertenece a
    // la lista precargada de la ficha seleccionada.
    $aprendizInicial = (int) old('id_aprendiz', $llamadoSeleccionado->id_aprendiz ?? 0);
    if (! collect($aprendicesFicha)->firstWhere('id', $aprendizInicial)) {
        $aprendizInicial = 0;
    }
@endphp
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('coordinacion.actas.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver a actas
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-gray-900">Expedir acta de coordinación</h2>
        <p class="mt-1 text-sm text-gray-500">Diligencia los datos del acta según la falta y el proceso disciplinario relacionado.</p>

        <form method="POST" action="{{ route('coordinacion.actas.store') }}" class="mt-6 space-y-5"
              x-data="formularioActa()" @submit="validarSeleccion($event)">
            @csrf

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                {{-- Selector de ficha con buscador (se elige primero) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Ficha</label>
                    <div class="relative mt-1" @keydown.escape.prevent.stop="fichaAbierta = false">
                        <button type="button" x-ref="botonFicha" @click="alternarFicha()"
                                class="flex w-full items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm transition focus:border-[#39A900] focus:outline-none focus:ring-1 focus:ring-[#39A900]">
                            <span class="truncate" :class="fichaId ? 'text-gray-900' : 'text-gray-500'"
                                  x-text="fichaLabel || 'Selecciona una ficha'"></span>
                            <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </button>

                        <div x-show="fichaAbierta" x-cloak @click.outside="fichaAbierta = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute z-30 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                            <div class="border-b border-gray-100 p-2">
                                {{-- Filtrado con debounce de 500 ms: espera a que se deje de escribir --}}
                                <input type="text" x-ref="buscadorFicha" x-model.debounce.500ms="fichaFiltro"
                                       placeholder="Buscar por número o programa..."
                                       class="w-full rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">
                            </div>
                            <ul class="max-h-52 overflow-y-auto py-1">
                                <template x-for="f in fichasFiltradas()" :key="f.id">
                                    <li>
                                        <button type="button" @click="seleccionarFicha(f)"
                                                class="w-full px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-[#39A900]/10"
                                                :class="fichaId === f.id && 'bg-[#39A900]/10 font-semibold text-[#247200]'"
                                                x-text="f.label"></button>
                                    </li>
                                </template>
                                <li x-show="fichasFiltradas().length === 0" x-cloak
                                    class="px-3 py-4 text-center text-sm text-gray-400">Sin coincidencias.</li>
                            </ul>
                        </div>

                        <input type="hidden" name="id_ficha" :value="fichaId || ''">
                    </div>
                    <p x-show="errorFicha" x-cloak class="mt-1 text-xs font-medium text-red-600">Selecciona una ficha.</p>
                </div>

                {{-- Selector de aprendiz dependiente de la ficha, con buscador --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Aprendiz</label>
                    <div class="relative mt-1" @keydown.escape.prevent.stop="aprendizAbierto = false">
                        <button type="button" @click="alternarAprendiz()" :disabled="!fichaId || cargando"
                                class="flex w-full items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm transition focus:border-[#39A900] focus:outline-none focus:ring-1 focus:ring-[#39A900] disabled:cursor-not-allowed disabled:bg-gray-50">
                            <span class="truncate" :class="aprendizId ? 'text-gray-900' : (fichaId ? 'text-gray-500' : 'text-gray-400')"
                                  x-text="aprendizTexto"></span>
                            <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </button>

                        <div x-show="aprendizAbierto" x-cloak @click.outside="aprendizAbierto = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute z-30 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                            <div class="border-b border-gray-100 p-2">
                                {{-- Filtrado con debounce de 500 ms sobre nombre, apellido y documento --}}
                                <input type="text" x-ref="buscadorAprendiz" x-model.debounce.500ms="aprendizFiltro"
                                       placeholder="Buscar por nombre, apellido o documento..."
                                       class="w-full rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">
                            </div>
                            <ul class="max-h-52 overflow-y-auto py-1">
                                <template x-for="a in aprendicesFiltrados()" :key="a.id">
                                    <li>
                                        <button type="button" @click="seleccionarAprendiz(a)"
                                                class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-[#39A900]/10"
                                                :class="aprendizId === a.id && 'bg-[#39A900]/10 font-semibold text-[#247200]'">
                                            <span class="truncate" x-text="a.nombre"></span>
                                            <span class="shrink-0 text-xs text-gray-400" x-text="a.documento"></span>
                                        </button>
                                    </li>
                                </template>
                                <li x-show="aprendices.length === 0" x-cloak
                                    class="px-3 py-4 text-center text-sm text-gray-400">Esta ficha no tiene aprendices matriculados.</li>
                                <li x-show="aprendices.length > 0 && aprendicesFiltrados().length === 0" x-cloak
                                    class="px-3 py-4 text-center text-sm text-gray-400">Sin coincidencias.</li>
                            </ul>
                        </div>

                        <input type="hidden" name="id_aprendiz" :value="aprendizId || ''">
                    </div>
                    <p x-show="errorAprendiz" x-cloak class="mt-1 text-xs font-medium text-red-600">Selecciona un aprendiz.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Falta relacionada</label>
                    <select name="id_falta" required class="mt-1 w-full rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">
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
                    <select name="tipo_acta" required class="mt-1 w-full rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">
                        <option value="acondicionamiento_academico">Acondicionamiento académico</option>
                        <option value="cancelacion_academica">Cancelación académica</option>
                        <option value="acondicionamiento_disciplinario">Acondicionamiento disciplinario</option>
                        <option value="cancelacion_disciplinaria">Cancelación disciplinaria</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Número de acta</label>
                    {{-- El número se genera automáticamente al expedir el acta. --}}
                    <p class="mt-1 w-full rounded-lg border border-dashed border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-500">
                        Se genera automáticamente (AC-{{ now()->format('Y') }}-…)
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha de expedición</label>
                    <input type="date" name="fecha_expedicion" required value="{{ old('fecha_expedicion', now()->toDateString()) }}"
                           class="mt-1 w-full rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Meses de inhabilitación</label>
                    <input type="number" name="meses_inhabilitacion" min="0" placeholder="Solo aplica a cancelaciones"
                           class="mt-1 w-full rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Descripción de la sanción</label>
                <textarea name="sancion_descripcion" rows="4" required
                          class="mt-1 w-full rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2"
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

<script>
    // Componente de los selectores dependientes Ficha → Aprendiz del formulario
    // de actas. Alpine (defer) se inicializa después de que este script corre,
    // por lo que la función ya existe cuando se evalúa x-data.
    function formularioActa() {
        return {
            fichas: @json($fichasCombo),
            aprendices: @json($aprendicesFicha),
            fichaId: @json($fichaSeleccionada ?: null),
            aprendizId: @json($aprendizInicial ?: null),
            urlAprendices: '{{ route('coordinacion.actas.aprendicesPorFicha', ['ficha' => '__FICHA__']) }}',
            fichaAbierta: false,
            aprendizAbierto: false,
            fichaFiltro: '',
            aprendizFiltro: '',
            cargando: false,
            errorFicha: false,
            errorAprendiz: false,

            get fichaLabel() {
                const f = this.fichas.find((x) => x.id === this.fichaId);
                return f ? f.label : '';
            },
            get aprendizTexto() {
                if (!this.fichaId) return 'Selecciona una ficha primero';
                if (this.cargando) return 'Cargando aprendices...';
                const a = this.aprendices.find((x) => x.id === this.aprendizId);
                return a ? a.nombre : 'Selecciona un aprendiz';
            },

            // Coincidencia parcial por palabras, ignorando mayúsculas y tildes.
            // NFD separa la tilde de la letra y luego se descartan los signos
            // diacríticos (código Unicode 768 a 879).
            normalizar(texto) {
                return Array.from((texto ?? '').toString().toLowerCase().normalize('NFD'))
                    .filter((caracter) => {
                        const codigo = caracter.charCodeAt(0);
                        return codigo < 768 || codigo > 879;
                    })
                    .join('');
            },
            coincide(texto, filtro) {
                const pajar = this.normalizar(texto);
                return this.normalizar(filtro).split(/\s+/).filter(Boolean).every((palabra) => pajar.includes(palabra));
            },
            fichasFiltradas() {
                return this.fichas.filter((f) => this.coincide(f.label, this.fichaFiltro));
            },
            aprendicesFiltrados() {
                return this.aprendices.filter((a) => this.coincide(a.nombre + ' ' + a.documento, this.aprendizFiltro));
            },

            alternarFicha() {
                this.fichaAbierta = !this.fichaAbierta;
                if (this.fichaAbierta) {
                    this.aprendizAbierto = false;
                    this.$nextTick(() => this.$refs.buscadorFicha.focus());
                }
            },
            alternarAprendiz() {
                this.aprendizAbierto = !this.aprendizAbierto;
                if (this.aprendizAbierto) {
                    this.fichaAbierta = false;
                    this.$nextTick(() => this.$refs.buscadorAprendiz.focus());
                }
            },

            // Al elegir ficha se cargan por AJAX solo sus aprendices matriculados.
            async seleccionarFicha(ficha) {
                this.fichaAbierta = false;
                this.fichaFiltro = '';
                this.errorFicha = false;
                if (this.fichaId === ficha.id) return;

                this.fichaId = ficha.id;
                this.aprendizId = null;
                this.aprendizFiltro = '';
                this.aprendices = [];
                this.cargando = true;
                try {
                    const respuesta = await fetch(this.urlAprendices.replace('__FICHA__', ficha.id), {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (respuesta.ok) this.aprendices = await respuesta.json();
                } catch (e) {
                    console.error(e);
                } finally {
                    this.cargando = false;
                }
            },
            seleccionarAprendiz(aprendiz) {
                this.aprendizId = aprendiz.id;
                this.aprendizAbierto = false;
                this.aprendizFiltro = '';
                this.errorAprendiz = false;
            },

            // Los inputs ocultos no soportan required: se valida antes de enviar.
            validarSeleccion(evento) {
                this.errorFicha = !this.fichaId;
                this.errorAprendiz = !this.aprendizId;
                if (this.errorFicha || this.errorAprendiz) evento.preventDefault();
            },
        };
    }
</script>
@endsection
