@extends('layouts.coordinador')

@section('titulo', 'Procesos disciplinarios')

@section('contenido')
<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Procesos disciplinarios</h2>
            <p class="text-gray-500">Seguimiento de las etapas de cada proceso abierto a partir de un llamado de atención.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @include('reportes._botones', ['rutaBase' => 'coordinacion.procesos.export', 'fichas' => $fichasExport])
            <a href="{{ route('coordinacion.procesos.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2D8200]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Nuevo proceso
            </a>
        </div>
    </div>

    @isset($trendLabels)
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <h3 class="text-base font-extrabold text-slate-900">Tendencia de procesos</h3>
                    <p class="mt-1 text-sm text-slate-500">Procesos iniciados en los últimos 6 meses.</p>
                </div>
                <div class="p-5">
                    <canvas id="chart-procesos-trend" class="w-full h-72"></canvas>
                </div>
            </div>
            <div class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <h3 class="text-base font-extrabold text-slate-900">Procesos por estado</h3>
                    <p class="mt-1 text-sm text-slate-500">Distribución de estados actuales.</p>
                </div>
                <div class="p-5">
                    <canvas id="chart-procesos-state" class="w-full h-72"></canvas>
                </div>
            </div>
        </div>
    @endisset

    <form method="GET" data-live-form class="flex flex-wrap gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre del aprendiz..."
               data-live class="min-w-[220px] flex-1 rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">

        <select name="estado_proceso" data-live class="rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">
            <option value="">Estado: todos</option>
            @foreach(['activo','cerrado','anulado'] as $estado)
                <option value="{{ $estado }}" @selected(request('estado_proceso') == $estado)>{{ ucfirst($estado) }}</option>
            @endforeach
        </select>

        <select name="etapa_actual" data-live class="rounded-lg border border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900] px-3 py-2">
            <option value="">Etapa: todas</option>
            @foreach(['llamado_escrito' => 'Llamado escrito', 'acondicionamiento' => 'Condicionamiento', 'cancelacion_matricula' => 'Cancelación de matrícula', 'finalizado' => 'Finalizado'] as $etapa => $etiquetaEtapa)
                <option value="{{ $etapa }}" @selected(request('etapa_actual') == $etapa)>{{ $etiquetaEtapa }}</option>
            @endforeach
        </select>

        <button class="rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Filtrar</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="responsive-cards w-full min-w-[640px] text-sm">
            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3">Aprendiz</th>
                    <th class="px-5 py-3">Etapa actual</th>
                    <th class="px-5 py-3">Inicio</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($procesos as $proceso)
                    @php
                        $estadoBadge = match($proceso->estado_proceso) {
                            'activo'  => 'bg-green-100 text-green-700',
                            'cerrado' => 'bg-gray-100 text-gray-600',
                            'anulado' => 'bg-red-100 text-red-700',
                            default   => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-900" data-label="Aprendiz">
                            {{ $proceso->aprendiz->usuario->nombres }} {{ $proceso->aprendiz->usuario->apellidos }}
                        </td>
                        <td class="px-5 py-3 text-gray-600" data-label="Etapa actual">{{ ['llamado_escrito' => 'Llamado escrito', 'acondicionamiento' => 'Condicionamiento', 'cancelacion_matricula' => 'Cancelación de matrícula', 'finalizado' => 'Finalizado'][$proceso->etapa_actual] ?? ucfirst(str_replace('_', ' ', (string) $proceso->etapa_actual)) }}</td>
                        <td class="px-5 py-3 text-gray-600" data-label="Inicio">{{ \Carbon\Carbon::parse($proceso->fecha_inicio)->format('d/m/Y') }}</td>
                        <td class="px-5 py-3" data-label="Estado">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $estadoBadge }}">{{ ucfirst($proceso->estado_proceso) }}</span>
                        </td>
                        <td class="px-5 py-3 text-right" data-label="Acción">
                            <a href="{{ route('coordinacion.procesos.show', $proceso->id_proceso) }}" class="font-medium text-[#39A900] hover:underline">Ver historial</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">No hay procesos disciplinarios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($procesos ?? null, 'links'))
        {{ $procesos->links() }}
    @endif
</div>
@endsection

@section('scripts')
<script>
    const procesosTrendLabels = @json($trendLabels ?? []);
    const procesosTrendData = @json($procesosTrend ?? []);
    const procesosElement = document.getElementById('chart-procesos-trend');

    if (procesosElement) {
        new Chart(procesosElement, {
            type: 'line',
            data: {
                labels: procesosTrendLabels,
                datasets: [{
                    label: 'Procesos',
                    data: procesosTrendData,
                    borderColor: '#ff6a13',
                    backgroundColor: '#ff6a1333',
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

    const procesosStateLabels = {!! json_encode($statusLabels ?? ['Activo','Suspendido','Finalizado','Apelación']) !!};
    const procesosStateData = {!! json_encode($procesosEstadoData ?? []) !!};
    const procesosStateElement = document.getElementById('chart-procesos-state');

    if (procesosStateElement) {
        new Chart(procesosStateElement, {
            type: 'bar',
            data: {
                labels: procesosStateLabels,
                datasets: [{
                    label: 'Estados',
                    data: procesosStateData,
                    backgroundColor: ['#39A90033','#ff6a1333','#10b98133','#f9731633'],
                    borderColor: ['#39A900','#ff6a13','#10b981','#f97316'],
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
