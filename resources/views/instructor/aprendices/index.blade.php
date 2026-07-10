@extends('layouts.instructor')

@section('titulo', 'Aprendices')

@section('contenido')
<div class="space-y-5">
    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Aprendices de mis fichas</h2>
            <p class="mt-1 text-sm text-gray-500">Aprendices matriculados en las fichas donde impartes clases. Puedes crear nuevos o asociar existentes.</p>
        </div>
        <a href="{{ route('instructor.aprendices.crear') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2D8200]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Nuevo aprendiz
        </a>
    </div>

    @if($fichas->isEmpty())
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700">
            Aún no tienes fichas asignadas: pide a coordinación que te asocie a una ficha para poder gestionar aprendices.
        </div>
    @else
        {{-- Carga masiva por Excel (solo aprendices, solo en mis fichas) --}}
        @include('importacion._panel', [
            'tituloPanel'  => 'aprendices',
            'urlPlantilla' => route('instructor.importacion.plantilla'),
            'urlImportar'  => route('instructor.importacion.importar'),
        ])

        {{-- Asociar un aprendiz existente a una de mis fichas --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" x-data="asociarAprendiz()">
            <p class="text-sm font-bold text-gray-900">Asociar aprendiz existente</p>
            <p class="mt-0.5 text-xs text-gray-500">Matricula en una de tus fichas a un aprendiz que ya está registrado en el sistema.</p>

            <form method="POST" action="{{ route('instructor.aprendices.asociar') }}" class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-start" @submit="validar($event)">
                @csrf

                {{-- Buscador con sugerencias del aprendiz --}}
                <div class="relative flex-1" @keydown.escape.prevent.stop="abierto = false">
                    <button type="button" @click="alternar()"
                            class="flex w-full items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm transition focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                        <span class="truncate" :class="aprendizId ? 'text-gray-900' : 'text-gray-500'" x-text="etiqueta || 'Busca un aprendiz por nombre o documento...'"></span>
                        <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div x-show="abierto" x-cloak @click.outside="abierto = false"
                         class="absolute z-30 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                        <div class="border-b border-gray-100 p-2">
                            <input type="text" x-ref="buscador" x-model="filtro" placeholder="Nombre o documento..."
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                        </div>
                        <ul class="max-h-56 overflow-y-auto py-1">
                            <template x-for="a in filtrados()" :key="a.id">
                                <li>
                                    <button type="button" @click="seleccionar(a)"
                                            class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-[#39A900]/10"
                                            :class="aprendizId === a.id && 'bg-[#39A900]/10 font-semibold text-[#247200]'">
                                        <span class="truncate" x-text="a.label"></span>
                                        <span class="shrink-0 text-xs text-gray-400" x-text="a.doc"></span>
                                    </button>
                                </li>
                            </template>
                            <li x-show="filtrados().length === 0" x-cloak class="px-3 py-4 text-center text-sm text-gray-400">Sin coincidencias.</li>
                        </ul>
                    </div>
                    <input type="hidden" name="id_aprendiz" :value="aprendizId || ''">
                    <p x-show="errorAprendiz" x-cloak class="mt-1 text-xs font-medium text-red-600">Selecciona un aprendiz.</p>
                </div>

                {{-- Ficha destino (solo mis fichas) --}}
                <select name="id_ficha" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                    @foreach($fichas as $f)
                        <option value="{{ $f->id_ficha }}">Ficha {{ $f->numero_ficha }} — {{ $f->programa?->nombre_programa }}</option>
                    @endforeach
                </select>

                <button type="submit" class="rounded-lg border border-[#39A900] px-4 py-2 text-sm font-semibold text-[#39A900] transition hover:bg-[#39A900]/10">
                    Asociar a la ficha
                </button>
            </form>
        </div>

        {{-- Filtros del listado --}}
        <form method="GET" action="{{ route('instructor.aprendices.index') }}" data-live-form class="flex flex-wrap gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <input type="text" name="buscar" value="{{ $buscar }}" data-live placeholder="Buscar por nombre, apellido o documento..."
                   class="min-w-[220px] flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
            <select name="id_ficha" data-live class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                <option value="">Ficha: todas</option>
                @foreach($fichas as $f)
                    <option value="{{ $f->id_ficha }}" @selected($idFicha === (int) $f->id_ficha)>Ficha {{ $f->numero_ficha }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Filtrar</button>
        </form>

        {{-- Tabla --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="responsive-cards w-full min-w-[860px] text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                    <tr>
                        <th class="whitespace-nowrap px-5 py-3">Aprendiz</th>
                        <th class="whitespace-nowrap px-5 py-3">Documento</th>
                        <th class="whitespace-nowrap px-5 py-3">Correo</th>
                        <th class="whitespace-nowrap px-5 py-3">Ficha</th>
                        <th class="whitespace-nowrap px-5 py-3">Matrícula</th>
                        <th class="whitespace-nowrap px-5 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($matriculas as $m)
                        @php
                            $u = $m->aprendiz?->usuario;
                            $matBadge = match($m->estado_matricula) {
                                'activa'   => 'bg-green-100 text-green-700',
                                'retirada' => 'bg-red-100 text-red-700',
                                'aplazada' => 'bg-amber-100 text-amber-700',
                                default    => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900" data-label="Aprendiz">{{ $u?->nombres }} {{ $u?->apellidos }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600" data-label="Documento">{{ $u?->tipo_documento }} {{ $u?->numero_documento }}</td>
                            <td class="px-5 py-3 text-gray-600" data-label="Correo">{{ $u?->correo ?? $m->aprendiz?->correo_institucional }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600" data-label="Ficha">Ficha {{ $m->ficha?->numero_ficha }}</td>
                            <td class="px-5 py-3" data-label="Matrícula">
                                <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $matBadge }}">{{ ucfirst($m->estado_matricula) }}</span>
                            </td>
                            <td class="px-5 py-3 text-right" data-label="Acción">
                                <a href="{{ route('instructor.aprendices.show', $m->id_aprendiz) }}" class="whitespace-nowrap font-medium text-[#39A900] hover:underline">Ver historial</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No hay aprendices matriculados en tus fichas con los filtros seleccionados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($matriculas ?? null, 'links'))
            {{ $matriculas->links() }}
        @endif
    @endif
</div>

<script>
    // Buscador con sugerencias para asociar un aprendiz existente (sin
    // peticiones al servidor: la lista ya viene cargada), igual que en el
    // formulario de procesos disciplinarios.
    function asociarAprendiz() {
        return {
            aprendices: @json($aprendicesAsociables),
            aprendizId: null,
            abierto: false,
            filtro: '',
            errorAprendiz: false,

            get etiqueta() {
                const a = this.aprendices.find((x) => x.id === this.aprendizId);
                return a ? a.label + ' (' + a.doc + ')' : '';
            },
            normalizar(texto) {
                return Array.from((texto ?? '').toString().toLowerCase().normalize('NFD'))
                    .filter((c) => { const n = c.charCodeAt(0); return n < 768 || n > 879; })
                    .join('');
            },
            coincide(texto, filtro) {
                const pajar = this.normalizar(texto);
                return this.normalizar(filtro).split(/\s+/).filter(Boolean).every((p) => pajar.includes(p));
            },
            filtrados() {
                return this.aprendices.filter((a) => this.coincide(a.label, this.filtro) || this.coincide(a.doc, this.filtro));
            },
            alternar() {
                this.abierto = !this.abierto;
                if (this.abierto) this.$nextTick(() => this.$refs.buscador.focus());
            },
            seleccionar(a) {
                this.aprendizId = a.id;
                this.abierto = false;
                this.filtro = '';
                this.errorAprendiz = false;
            },
            validar(evento) {
                this.errorAprendiz = !this.aprendizId;
                if (this.errorAprendiz) {
                    evento.preventDefault();
                    this.abierto = true;
                    this.$nextTick(() => this.$refs.buscador.focus());
                }
            },
        };
    }
</script>
@endsection
