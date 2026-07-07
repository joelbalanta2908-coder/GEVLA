@php
    /** @var \App\Models\Ficha|null $ficha */
    $esEdicion = $ficha !== null;

    // Datos para los buscadores con sugerencias (programa e instructor líder):
    // se filtran en el cliente, sin peticiones al servidor.
    $programasCombo = $programas->map(fn ($p) => [
        'id' => $p->id_programa,
        'label' => $p->codigo_programa . ' — ' . $p->nombre_programa,
    ])->values();
    $programaSeleccionadoId = (int) old('id_programa', $ficha->id_programa ?? 0) ?: null;

    if (! $esEdicion) {
        $instructoresCombo = $instructores->map(fn ($i) => [
            'id' => $i->id_instructor,
            'nombre' => $i->usuario ? trim($i->usuario->nombres . ' ' . $i->usuario->apellidos) : $i->codigo_instructor,
            'codigo' => $i->codigo_instructor,
        ])->values();
        $instructorSeleccionadoId = (int) old('id_instructor_lider', 0) ?: null;
    }
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="numero_ficha" class="mb-1 block text-sm font-semibold text-gray-700">Número de ficha</label>
        <input type="text" id="numero_ficha" name="numero_ficha" maxlength="20" required
               inputmode="numeric" pattern="[0-9]+" data-solo-numeros title="Solo números"
               value="{{ old('numero_ficha', $ficha->numero_ficha ?? '') }}"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
    </div>

    {{-- Programa de formación: buscador con sugerencias por código o nombre --}}
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-semibold text-gray-700">Programa de formación</label>
        <div class="relative" @keydown.escape.prevent.stop="programaAbierto = false">
            <button type="button" @click="alternarPrograma()"
                    class="flex w-full items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-left text-sm shadow-sm transition focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                <span class="truncate" :class="programaId ? 'text-gray-900' : 'text-gray-500'" x-text="programaLabel || 'Selecciona un programa...'"></span>
                <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div x-show="programaAbierto" x-cloak @click.outside="programaAbierto = false"
                 x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                 class="absolute z-30 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                <div class="border-b border-gray-100 p-2">
                    <input type="text" x-ref="buscadorPrograma" x-model="programaFiltro" placeholder="Buscar por código o nombre..."
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                </div>
                <ul class="max-h-56 overflow-y-auto py-1">
                    <template x-for="p in programasFiltrados()" :key="p.id">
                        <li>
                            <button type="button" @click="seleccionarPrograma(p)"
                                    class="w-full px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-[#39A900]/10"
                                    :class="programaId === p.id && 'bg-[#39A900]/10 font-semibold text-[#247200]'" x-text="p.label"></button>
                        </li>
                    </template>
                    <li x-show="programasFiltrados().length === 0" x-cloak class="px-3 py-4 text-center text-sm text-gray-400">Sin coincidencias.</li>
                </ul>
            </div>
            <input type="hidden" name="id_programa" :value="programaId || ''">
        </div>
        <p x-show="errorPrograma" x-cloak class="mt-1 text-xs font-medium text-red-600">Selecciona un programa de formación.</p>
    </div>

    <div>
        <label for="modalidad" class="mb-1 block text-sm font-semibold text-gray-700">Modalidad</label>
        <select id="modalidad" name="modalidad" required
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            @foreach($modalidades as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('modalidad', $ficha->modalidad ?? '') === $valor)>{{ $etiqueta }}</option>
            @endforeach
            {{-- Fichas antiguas con una modalidad retirada del catálogo conservan su valor. --}}
            @if($esEdicion && $ficha->modalidad && ! array_key_exists($ficha->modalidad, $modalidades))
                <option value="{{ $ficha->modalidad }}" @selected(old('modalidad', $ficha->modalidad) === $ficha->modalidad)>{{ $ficha->modalidad_label }} (histórica)</option>
            @endif
        </select>
    </div>

    <div>
        <label for="estado_ficha" class="mb-1 block text-sm font-semibold text-gray-700">Estado</label>
        <select id="estado_ficha" name="estado_ficha" required
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            @foreach($estados as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('estado_ficha', $ficha->estado_ficha ?? 'en_ejecucion') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>

    @php $hoy = now()->timezone('America/Bogota')->toDateString(); @endphp

    <div>
        <label for="fecha_inicio" class="mb-1 block text-sm font-semibold text-gray-700">Fecha de inicio</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" required
               @unless($esEdicion) min="{{ $hoy }}" @endunless
               value="{{ old('fecha_inicio', optional($ficha->fecha_inicio ?? null)->format('Y-m-d')) }}"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
        <p class="mt-1 text-xs text-gray-400">No se permiten inicios de ficha en fechas pasadas.</p>
    </div>

    <div>
        <label for="fecha_fin_programada" class="mb-1 block text-sm font-semibold text-gray-700">Fecha de finalización <span class="font-normal text-gray-400">(opcional)</span></label>
        <input type="date" id="fecha_fin_programada" name="fecha_fin_programada"
               @unless($esEdicion) min="{{ $hoy }}" @endunless
               value="{{ old('fecha_fin_programada', optional($ficha->fecha_fin_programada ?? null)->format('Y-m-d')) }}"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
        <p class="mt-1 text-xs text-gray-400">Debe ser posterior a la fecha de inicio y no puede estar en el pasado.</p>
    </div>

    <script>
        // La fecha mínima de finalización se ajusta a la fecha de inicio elegida.
        (function () {
            var inicio = document.getElementById('fecha_inicio');
            var fin = document.getElementById('fecha_fin_programada');
            if (!inicio || !fin) return;
            function sincronizar() {
                if (inicio.value) {
                    fin.min = inicio.value;
                    if (fin.value && fin.value < inicio.value) fin.value = '';
                }
            }
            inicio.addEventListener('change', sincronizar);
            sincronizar();
        })();
    </script>

    @if($esEdicion)
        {{-- El instructor líder se cambia desde la vista de detalle (acción dedicada con auditoría). --}}
        <input type="hidden" name="id_instructor_lider" value="{{ $ficha->id_instructor_lider }}">
    @else
        {{-- Instructor líder: buscador con sugerencias por nombre o código --}}
        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-semibold text-gray-700">Instructor líder inicial</label>
            <div class="relative" @keydown.escape.prevent.stop="instructorAbierto = false">
                <button type="button" @click="alternarInstructor()"
                        class="flex w-full items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-left text-sm shadow-sm transition focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                    <span class="truncate" :class="instructorId ? 'text-gray-900' : 'text-gray-500'" x-text="instructorLabel || 'Selecciona un instructor...'"></span>
                    <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div x-show="instructorAbierto" x-cloak @click.outside="instructorAbierto = false"
                     x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute z-30 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                    <div class="border-b border-gray-100 p-2">
                        <input type="text" x-ref="buscadorInstructor" x-model="instructorFiltro" placeholder="Buscar por nombre o código..."
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                    </div>
                    <ul class="max-h-56 overflow-y-auto py-1">
                        <template x-for="i in instructoresFiltrados()" :key="i.id">
                            <li>
                                <button type="button" @click="seleccionarInstructor(i)"
                                        class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-[#39A900]/10"
                                        :class="instructorId === i.id && 'bg-[#39A900]/10 font-semibold text-[#247200]'">
                                    <span class="truncate" x-text="i.nombre"></span>
                                    <span class="shrink-0 text-xs text-gray-400" x-text="i.codigo"></span>
                                </button>
                            </li>
                        </template>
                        <li x-show="instructoresFiltrados().length === 0" x-cloak class="px-3 py-4 text-center text-sm text-gray-400">Sin coincidencias.</li>
                    </ul>
                </div>
                <input type="hidden" name="id_instructor_lider" :value="instructorId || ''">
            </div>
            <p x-show="errorInstructor" x-cloak class="mt-1 text-xs font-medium text-red-600">Selecciona un instructor líder.</p>
            <p class="mt-1 text-xs text-gray-400">Quedará también asociado como instructor de la ficha. Podrás cambiar el líder más adelante.</p>
        </div>
    @endif
</div>

<script>
    // Buscadores con sugerencias (sin peticiones al servidor: las listas ya
    // vienen cargadas) del programa de formación y, al crear, del instructor
    // líder inicial. Alpine (defer) se inicializa después de este script.
    function fichaComboboxes() {
        return {
            programas: @json($programasCombo),
            programaId: @json($programaSeleccionadoId ?: null),
            programaAbierto: false,
            programaFiltro: '',
            errorPrograma: false,

            instructores: @json($instructoresCombo ?? []),
            instructorId: @json($instructorSeleccionadoId ?? null),
            instructorAbierto: false,
            instructorFiltro: '',
            errorInstructor: false,
            esEdicion: @json($esEdicion),

            get programaLabel() {
                const p = this.programas.find((x) => x.id === this.programaId);
                return p ? p.label : '';
            },
            get instructorLabel() {
                const i = this.instructores.find((x) => x.id === this.instructorId);
                return i ? `${i.nombre} (${i.codigo})` : '';
            },

            // Coincidencia parcial por palabras, ignorando mayúsculas y tildes.
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
            programasFiltrados() {
                return this.programas.filter((p) => this.coincide(p.label, this.programaFiltro));
            },
            instructoresFiltrados() {
                // Se busca explícitamente por nombre O por código.
                return this.instructores.filter((i) => this.coincide(i.nombre, this.instructorFiltro) || this.coincide(i.codigo, this.instructorFiltro));
            },

            alternarPrograma() {
                this.programaAbierto = !this.programaAbierto;
                if (this.programaAbierto) {
                    this.instructorAbierto = false;
                    this.$nextTick(() => this.$refs.buscadorPrograma.focus());
                }
            },
            alternarInstructor() {
                this.instructorAbierto = !this.instructorAbierto;
                if (this.instructorAbierto) {
                    this.programaAbierto = false;
                    this.$nextTick(() => this.$refs.buscadorInstructor.focus());
                }
            },
            seleccionarPrograma(p) {
                this.programaId = p.id;
                this.programaAbierto = false;
                this.programaFiltro = '';
                this.errorPrograma = false;
            },
            seleccionarInstructor(i) {
                this.instructorId = i.id;
                this.instructorAbierto = false;
                this.instructorFiltro = '';
                this.errorInstructor = false;
            },

            // Los inputs ocultos no soportan required: se valida antes de enviar.
            validarCombos(evento) {
                this.errorPrograma = !this.programaId;
                this.errorInstructor = !this.esEdicion && !this.instructorId;
                if (this.errorPrograma || this.errorInstructor) evento.preventDefault();
            },
        };
    }
</script>
