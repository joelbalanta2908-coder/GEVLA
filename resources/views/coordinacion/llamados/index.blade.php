@extends('layouts.coordinador')

@section('titulo', 'Llamados de atención')

@section('contenido')
<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Llamados de atención</h2>
            <p class="text-gray-500">Revisa y da seguimiento a los llamados reportados por los instructores.</p>
        </div>
        @include('reportes._botones', ['rutaBase' => 'coordinacion.llamados.export', 'fichas' => $fichasExport])
    </div>

    @isset($trendLabels)
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <h3 class="text-base font-extrabold text-slate-900">Tendencia de llamados</h3>
                    <p class="mt-1 text-sm text-slate-500">Evolución mensual de los llamados de atención.</p>
                </div>
                <div class="p-5">
                    <canvas id="chart-llamados-trend" class="w-full h-72"></canvas>
                </div>
            </div>
            <div class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <h3 class="text-base font-extrabold text-slate-900">Llamados por estado</h3>
                    <p class="mt-1 text-sm text-slate-500">Distribución de estados actuales.</p>
                </div>
                <div class="p-5">
                    <canvas id="chart-llamados-state" class="w-full h-72"></canvas>
                </div>
            </div>
        </div>
    @endisset

    {{-- Reporte por aprendiz: buscador con sugerencias para localizar a un
         aprendiz y descargar su reporte completo o abrir su perfil. --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" x-data="reporteAprendiz()">
        <p class="text-sm font-bold text-gray-900">Reporte por aprendiz</p>
        <p class="mt-0.5 text-xs text-gray-500">Busca un aprendiz para descargar el reporte completo de sus llamados o ver su perfil.</p>

        <div class="mt-3 grid grid-cols-1 gap-3 lg:grid-cols-[1fr_auto_auto] lg:items-center">
            {{-- Buscador con sugerencias --}}
            <div class="relative" @keydown.escape.prevent.stop="abierto = false">
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
            </div>

            {{-- Acciones (se habilitan al elegir un aprendiz) --}}
            <a :href="aprendizId ? '{{ url('coordinacion/aprendices') }}/' + aprendizId + '/reporte/pdf' : '#'" target="_blank" rel="noopener"
               :class="aprendizId ? 'bg-[#39A900] text-white hover:bg-[#2D8200]' : 'cursor-not-allowed bg-gray-200 text-gray-400 pointer-events-none'"
               class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                Descargar reporte (PDF)
            </a>
            <a :href="aprendizId ? '{{ url('coordinacion/aprendices') }}/' + aprendizId : '#'"
               :class="aprendizId ? 'border-[#39A900] text-[#39A900] hover:bg-[#39A900]/10' : 'cursor-not-allowed border-gray-200 text-gray-400 pointer-events-none'"
               class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg border px-4 py-2 text-sm font-semibold transition">
                Ver perfil
            </a>
        </div>
    </div>

    {{-- Sin búsqueda automática: los filtros solo se aplican al pulsar "Filtrar". --}}
    <form method="GET" class="flex flex-wrap gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por aprendiz o asunto"
               class="min-w-[220px] flex-1 rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">

        <select name="categoria" class="rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">
            <option value="">Categoría: todas</option>
            <option value="academico" @selected(request('categoria') == 'academico')>Académico</option>
            <option value="disciplinario" @selected(request('categoria') == 'disciplinario')>Disciplinario</option>
        </select>

        <select name="estado" class="rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">
            <option value="">Estado: todos</option>
            @foreach(['registrado','en_revision','notificado','cerrado','cancelado'] as $estado)
                <option value="{{ $estado }}" @selected(request('estado') == $estado)>
                    {{ str($estado)->replace('_',' ')->ucfirst() }}
                </option>
            @endforeach
        </select>

        <button class="rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Filtrar</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="responsive-cards w-full min-w-[1000px] text-sm">
            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                <tr>
                    <th class="whitespace-nowrap px-5 py-3">Aprendiz</th>
                    <th class="whitespace-nowrap px-5 py-3">Instructor</th>
                    <th class="whitespace-nowrap px-5 py-3">Fecha</th>
                    <th class="whitespace-nowrap px-5 py-3">Categoría</th>
                    <th class="whitespace-nowrap px-5 py-3">Asunto</th>
                    <th class="whitespace-nowrap px-5 py-3">Estado</th>
                    <th class="whitespace-nowrap px-5 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($llamados as $llamado)
                    @php
                        $ua = $llamado->aprendiz?->usuario;
                        $estadoBadge = match($llamado->estado_llamado) {
                            'registrado'  => 'bg-gray-100 text-gray-600',
                            'en_revision' => 'bg-amber-100 text-amber-700',
                            'notificado'  => 'bg-blue-100 text-blue-700',
                            'cerrado'     => 'bg-green-100 text-green-700',
                            'cancelado'   => 'bg-red-100 text-red-700',
                            default       => 'bg-gray-100 text-gray-600',
                        };
                        $catBadge = $llamado->categoria === 'disciplinario' ? 'bg-rose-50 text-rose-600' : 'bg-sky-50 text-sky-600';
                    @endphp
                    <tr class="hover:bg-gray-50">
                        {{-- Aprendiz con su foto de perfil (o iniciales) --}}
                        <td class="px-5 py-3" data-label="Aprendiz">
                            <div class="flex items-center gap-3 whitespace-nowrap">
                                @if($ua?->fotoUrl())
                                    <img src="{{ $ua->fotoUrl() }}" alt="Foto de {{ $ua->nombres }}" class="h-9 w-9 shrink-0 rounded-full object-cover ring-1 ring-gray-200">
                                @else
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#39A900]/10 text-xs font-black text-[#39A900]">
                                        {{ $ua?->iniciales() ?? 'A' }}
                                    </span>
                                @endif
                                <span class="font-medium text-gray-900">{{ $ua?->nombres }} {{ $ua?->apellidos }}</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-gray-600" data-label="Instructor">{{ $llamado->instructor->usuario->nombres }} {{ $llamado->instructor->usuario->apellidos }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-gray-600" data-label="Fecha">{{ \Carbon\Carbon::parse($llamado->fecha_llamado)->format('d/m/Y') }}</td>
                        <td class="px-5 py-3" data-label="Categoría">
                            <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $catBadge }}">{{ $llamado->categoria_label }}</span>
                        </td>
                        <td class="px-5 py-3 text-gray-600" data-label="Asunto">{{ $llamado->asunto }}</td>
                        <td class="px-5 py-3" data-label="Estado">
                            <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $estadoBadge }}">
                                {{ $llamado->estado_label }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right" data-label="Acción">
                            <a href="{{ route('coordinacion.llamados.show', $llamado->id_llamado) }}" class="whitespace-nowrap font-medium text-[#39A900] hover:underline">Ver detalle</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">No se encontraron llamados con los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($llamados ?? null, 'links'))
        {{ $llamados->links() }}
    @endif
</div>

<script>
    // Buscador con sugerencias para el reporte por aprendiz (la lista ya viene
    // cargada; filtra por nombre o documento ignorando mayúsculas y tildes).
    function reporteAprendiz() {
        return {
            aprendices: @json($aprendicesReporte),
            aprendizId: null,
            abierto: false,
            filtro: '',

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
            },
        };
    }
</script>
@endsection

@section('scripts')
<script>
    const llamadosTrendLabels = {!! json_encode($trendLabels ?? []) !!};
    const llamadosTrendData = {!! json_encode($llamadosTrend ?? []) !!};
    const llamadosElement = document.getElementById('chart-llamados-trend');

    if (llamadosElement) {
        new Chart(llamadosElement, {
            type: 'line',
            data: {
                labels: llamadosTrendLabels,
                datasets: [{
                    label: 'Llamados',
                    data: llamadosTrendData,
                    borderColor: '#39A900',
                    backgroundColor: '#39A90033',
                    tension: 0.35,
                    pointRadius: 4,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: '#e5e7eb' } }, x: { grid: { display: false } } },
            },
        });
    }

    const llamadosStateLabels = {!! json_encode($statusLabels ?? ['Registrado','En revisión','Notificado','Cerrado','Cancelado']) !!};
    const llamadosStateData = {!! json_encode($llamadosEstadoData ?? []) !!};
    const llamadosStateElement = document.getElementById('chart-llamados-state');

    if (llamadosStateElement) {
        new Chart(llamadosStateElement, {
            type: 'bar',
            data: {
                labels: llamadosStateLabels,
                datasets: [{
                    label: 'Estados',
                    data: llamadosStateData,
                    backgroundColor: ['#39A90033','#ff6a1333','#00324d33','#10b98133','#f9731633'],
                    borderColor: ['#39A900','#ff6a13','#00324d','#10b981','#f97316'],
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: '#e5e7eb' } }, x: { grid: { display: false } } },
            },
        });
    }
</script>
@endsection
