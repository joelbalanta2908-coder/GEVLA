@extends('layouts.coordinador')

@section('titulo', 'Procesos disciplinarios')

@section('contenido')
<div class="space-y-6">

    <div>
        <h2 class="text-2xl font-bold text-gray-900">Procesos disciplinarios</h2>
        <p class="text-gray-500">Seguimiento de las etapas de cada proceso abierto a partir de un llamado de atención.</p>
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

    <form method="GET" class="flex flex-wrap gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <select name="estado_proceso" class="rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
            <option value="">Estado: todos</option>
            @foreach(['activo','cerrado','anulado'] as $estado)
                <option value="{{ $estado }}" @selected(request('estado_proceso') == $estado)>{{ ucfirst($estado) }}</option>
            @endforeach
        </select>

        <select name="etapa_actual" class="rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
            <option value="">Etapa: todas</option>
            @foreach(['llamado_escrito','acondicionamiento','cancelacion_matricula','finalizado'] as $etapa)
                <option value="{{ $etapa }}" @selected(request('etapa_actual') == $etapa)>
                    {{ str($etapa)->replace('_',' ')->ucfirst() }}
                </option>
            @endforeach
        </select>

        <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Filtrar</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-sm">
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
                        <td class="px-5 py-3 font-medium text-gray-900">
                            {{ $proceso->aprendiz->usuario->nombres }} {{ $proceso->aprendiz->usuario->apellidos }}
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ str($proceso->etapa_actual)->replace('_',' ')->ucfirst() }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ \Carbon\Carbon::parse($proceso->fecha_inicio)->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $estadoBadge }}">{{ ucfirst($proceso->estado_proceso) }}</span>
                        </td>
                        <td class="px-5 py-3 text-right">
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

    const procesosStateLabels = @json($statusLabels ?? ['Activo','Suspendido','Finalizado','Apelación']);
    const procesosStateData = @json($procesosEstadoData ?? []);
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
