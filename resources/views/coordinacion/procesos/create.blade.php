@extends('layouts.coordinador')

@section('titulo', 'Crear proceso disciplinario')

@php
    // Combos para el buscador con sugerencias (aprendiz) y para filtrar los
    // llamados según el aprendiz elegido. Se filtran en el cliente, sin
    // peticiones al servidor (mismo enfoque que el formulario de fichas).
    $aprendicesCombo = $aprendices->map(fn ($a) => [
        'id'    => $a->id_aprendiz,
        'label' => trim(($a->usuario->nombres ?? '') . ' ' . ($a->usuario->apellidos ?? '')) ?: 'Aprendiz #' . $a->id_aprendiz,
        'doc'   => (string) ($a->usuario->numero_documento ?? ''),
    ])->values();

    $llamadosCombo = $llamados->map(fn ($l) => [
        'id'          => $l->id_llamado,
        'id_aprendiz' => $l->id_aprendiz,
        'label'       => $l->asunto . ' (' . \Carbon\Carbon::parse($l->fecha_llamado)->format('d/m/Y') . ')',
    ])->values();

    $preAprendizJs = old('id_aprendiz', $preAprendiz ?? null);
    $preAprendizJs = $preAprendizJs !== null && $preAprendizJs !== '' ? (int) $preAprendizJs : null;
    $preLlamadoJs  = old('id_llamado', $preLlamado ?? null);
    $preLlamadoJs  = $preLlamadoJs !== null && $preLlamadoJs !== '' ? (string) $preLlamadoJs : '';

    // Cuando el proceso se inicia DESDE un llamado de atención, el aprendiz
    // viene definido por ese llamado y NO debe poder cambiarse.
    $aprendizBloqueado = ! empty($preLlamado) && ! empty($preAprendiz);
    $aprendizFijo = $aprendizBloqueado ? $aprendicesCombo->firstWhere('id', (int) $preAprendiz) : null;
@endphp

@section('contenido')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('coordinacion.procesos.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver a procesos
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-[#00324D]">Registrar nuevo proceso disciplinario</h2>
        <p class="mt-1 text-sm text-gray-500">Inicia un nuevo proceso disciplinario para un aprendiz.</p>

        <form method="POST" action="{{ route('coordinacion.procesos.store') }}" class="mt-6 space-y-5"
              x-data="procesoForm()" @submit="validar($event)">
            @csrf

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                {{-- Aprendiz: cuando el proceso nace de un llamado, queda FIJO
                     (no puede cambiarse); si no, buscador con sugerencias. --}}
                @if($aprendizBloqueado)
                <div>
                    <label class="block text-sm font-bold text-gray-700">Aprendiz</label>
                    <div class="mt-1 flex w-full items-center justify-between gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                        <span class="truncate font-semibold">{{ $aprendizFijo['label'] ?? 'Aprendiz del llamado' }}</span>
                        <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <input type="hidden" name="id_aprendiz" value="{{ (int) $preAprendiz }}">
                    <p class="mt-1 text-xs text-gray-400">Definido por el llamado de atención de origen: no puede cambiarse.</p>
                </div>
                @else
                <div>
                    <label class="block text-sm font-bold text-gray-700">Aprendiz</label>
                    <div class="relative mt-1" @keydown.escape.prevent.stop="aprendizAbierto = false">
                        <button type="button" @click="alternarAprendiz()"
                                class="flex w-full items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm transition focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                            <span class="truncate" :class="aprendizId ? 'text-gray-900' : 'text-gray-500'" x-text="aprendizLabel || 'Selecciona un aprendiz...'"></span>
                            <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div x-show="aprendizAbierto" x-cloak @click.outside="aprendizAbierto = false"
                             x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute z-30 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                            <div class="border-b border-gray-100 p-2">
                                <input type="text" x-ref="buscadorAprendiz" x-model="aprendizFiltro" placeholder="Buscar por nombre o documento..."
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                            </div>
                            <ul class="max-h-56 overflow-y-auto py-1">
                                <template x-for="a in aprendicesFiltrados()" :key="a.id">
                                    <li>
                                        <button type="button" @click="seleccionarAprendiz(a)"
                                                class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-[#39A900]/10"
                                                :class="aprendizId === a.id && 'bg-[#39A900]/10 font-semibold text-[#247200]'">
                                            <span class="truncate" x-text="a.label"></span>
                                            <span class="shrink-0 text-xs text-gray-400" x-text="a.doc"></span>
                                        </button>
                                    </li>
                                </template>
                                <li x-show="aprendicesFiltrados().length === 0" x-cloak class="px-3 py-4 text-center text-sm text-gray-400">Sin coincidencias.</li>
                            </ul>
                        </div>
                        <input type="hidden" name="id_aprendiz" :value="aprendizId || ''">
                    </div>
                    <p x-show="errorAprendiz" x-cloak class="mt-1 text-xs font-medium text-red-600">Selecciona un aprendiz.</p>
                </div>
                @endif

                {{-- Llamado asociado: depende del aprendiz seleccionado --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700">Llamado asociado (Opcional)</label>

                    {{-- Sin aprendiz elegido: no se muestra ningún llamado --}}
                    <div x-show="!aprendizId"
                         class="mt-1 w-full rounded-lg border border-dashed border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-400">
                        Selecciona primero un aprendiz.
                    </div>

                    {{-- Aprendiz elegido pero sin llamados --}}
                    <div x-show="aprendizId && llamadosDelAprendiz().length === 0" x-cloak
                         class="mt-1 w-full rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700">
                        Este aprendiz no tiene llamados de atención.
                    </div>

                    {{-- Aprendiz con llamados: lista filtrada --}}
                    <select name="id_llamado" x-model="llamadoId"
                            x-init="$nextTick(() => { if (llamadoId) $el.value = String(llamadoId); })"
                            x-show="aprendizId && llamadosDelAprendiz().length > 0" x-cloak
                            :disabled="!aprendizId || llamadosDelAprendiz().length === 0"
                            class="mt-1 w-full rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">
                        <option value="">Sin llamado previo</option>
                        <template x-for="l in llamadosDelAprendiz()" :key="l.id">
                            <option :value="l.id" x-text="l.label"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Fecha de inicio</label>
                    <input type="date" name="fecha_inicio" required value="{{ old('fecha_inicio', now()->toDateString()) }}"
                           class="mt-1 w-full rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Etapa inicial</label>
                    <select name="etapa_actual" required class="mt-1 w-full rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">
                        <option value="llamado_escrito" @selected(old('etapa_actual') == 'llamado_escrito')>Llamado escrito</option>
                        <option value="condicionamiento" @selected(old('etapa_actual') == 'condicionamiento')>Condicionamiento</option>
                        <option value="cancelacion_matricula" @selected(old('etapa_actual') == 'cancelacion_matricula')>Cancelación de matrícula</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Estado del proceso</label>
                    <select name="estado_proceso" required class="mt-1 w-full rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">
                        <option value="activo" @selected(old('estado_proceso') == 'activo')>Activo</option>
                        <option value="suspendido" @selected(old('estado_proceso') == 'suspendido')>Suspendido</option>
                        <option value="finalizado" @selected(old('estado_proceso') == 'finalizado')>Finalizado</option>
                        <option value="apelacion" @selected(old('estado_proceso') == 'apelacion')>En apelación</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700">Observaciones iniciales (Opcional)</label>
                <textarea name="observaciones" rows="3"
                          class="mt-1 w-full rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2"
                          placeholder="Anotaciones para la apertura del proceso...">{{ old('observaciones') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('coordinacion.procesos.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-[#39A900] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#247200]">
                    Crear proceso
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Buscador con sugerencias del aprendiz y filtrado dependiente de los
    // llamados (solo los del aprendiz elegido). Sin peticiones al servidor: las
    // listas ya vienen cargadas.
    function procesoForm() {
        return {
            aprendices: @json($aprendicesCombo),
            llamados: @json($llamadosCombo),

            aprendizId: @json($preAprendizJs),
            aprendizAbierto: false,
            aprendizFiltro: '',
            errorAprendiz: false,

            llamadoId: @json($preLlamadoJs),

            get aprendizLabel() {
                const a = this.aprendices.find((x) => x.id === this.aprendizId);
                return a ? a.label : '';
            },

            // Coincidencia parcial por palabras, ignorando mayúsculas y tildes.
            normalizar(texto) {
                return Array.from((texto ?? '').toString().toLowerCase().normalize('NFD'))
                    .filter((c) => { const n = c.charCodeAt(0); return n < 768 || n > 879; })
                    .join('');
            },
            coincide(texto, filtro) {
                const pajar = this.normalizar(texto);
                return this.normalizar(filtro).split(/\s+/).filter(Boolean).every((p) => pajar.includes(p));
            },
            aprendicesFiltrados() {
                return this.aprendices.filter((a) => this.coincide(a.label, this.aprendizFiltro) || this.coincide(a.doc, this.aprendizFiltro));
            },
            llamadosDelAprendiz() {
                if (!this.aprendizId) return [];
                return this.llamados.filter((l) => l.id_aprendiz === this.aprendizId);
            },

            alternarAprendiz() {
                this.aprendizAbierto = !this.aprendizAbierto;
                if (this.aprendizAbierto) this.$nextTick(() => this.$refs.buscadorAprendiz.focus());
            },
            seleccionarAprendiz(a) {
                this.aprendizId = a.id;
                this.aprendizAbierto = false;
                this.aprendizFiltro = '';
                this.errorAprendiz = false;
                // Si el llamado elegido no pertenece al nuevo aprendiz, se limpia.
                if (!this.llamados.some((l) => String(l.id) === String(this.llamadoId) && l.id_aprendiz === a.id)) {
                    this.llamadoId = '';
                }
            },

            // El id_aprendiz va en un input oculto (sin required nativo): se valida aquí.
            validar(evento) {
                this.errorAprendiz = !this.aprendizId;
                if (this.errorAprendiz) {
                    evento.preventDefault();
                    this.aprendizAbierto = true;
                    this.$nextTick(() => this.$refs.buscadorAprendiz.focus());
                }
            },
        };
    }
</script>
@endsection
