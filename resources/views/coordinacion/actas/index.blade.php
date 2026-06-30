@extends('layouts.coordinador')

@section('titulo', 'Actas de coordinación')

@section('contenido')
<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Actas de coordinación</h2>
            <p class="text-gray-500">Acondicionamientos y cancelaciones expedidas por coordinación.</p>
        </div>
        <a href="{{ route('coordinacion.actas.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-[#39A900] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#2D8200]">
            + Expedir nueva acta
        </a>
    </div>

    @isset($trendLabels)
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <h3 class="text-base font-extrabold text-slate-900">Tendencia de actas</h3>
                    <p class="mt-1 text-sm text-slate-500">Actas expedidas en los últimos 6 meses.</p>
                </div>
                <div class="p-5">
                    <canvas id="chart-actas-trend" class="w-full h-72"></canvas>
                </div>
            </div>
            <div class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <h3 class="text-base font-extrabold text-slate-900">Actas por estado</h3>
                    <p class="mt-1 text-sm text-slate-500">Distribución de estados actuales.</p>
                </div>
                <div class="p-5">
                    <canvas id="chart-actas-state" class="w-full h-72"></canvas>
                </div>
            </div>
        </div>
    @endisset

    <form method="GET" class="flex flex-wrap gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <select name="tipo_acta" class="rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
            <option value="">Tipo: todas</option>
            @foreach(['acondicionamiento_academico','cancelacion_academica','acondicionamiento_disciplinario','cancelacion_disciplinaria'] as $tipo)
                <option value="{{ $tipo }}" @selected(request('tipo_acta') == $tipo)>
                    {{ str($tipo)->replace('_',' ')->ucfirst() }}
                </option>
            @endforeach
        </select>

        <select name="estado_acta" class="rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
            <option value="">Estado: todos</option>
            @foreach(['expedido','notificado','firme'] as $estado)
                <option value="{{ $estado }}" @selected(request('estado_acta') == $estado)>{{ ucfirst($estado) }}</option>
            @endforeach
        </select>

        <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Filtrar</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3">N° acta</th>
                    <th class="px-5 py-3">Aprendiz</th>
                    <th class="px-5 py-3">Tipo</th>
                    <th class="px-5 py-3">Expedición</th>
                    <th class="px-5 py-3">Firmeza</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($actas as $acta)
                    @php
                        $estadoBadge = match($acta->estado_acta) {
                            'expedido'   => 'bg-blue-100 text-blue-700',
                            'notificado' => 'bg-amber-100 text-amber-700',
                            'firme'      => 'bg-green-100 text-green-700',
                            default      => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $acta->numero_acta }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $acta->aprendiz->usuario->nombres }} {{ $acta->aprendiz->usuario->apellidos }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ str($acta->tipo_acta)->replace('_',' ')->ucfirst() }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ \Carbon\Carbon::parse($acta->fecha_expedicion)->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-gray-600">
                            {{ $acta->fecha_firmeza ? \Carbon\Carbon::parse($acta->fecha_firmeza)->format('d/m/Y') : '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $estadoBadge }}">{{ ucfirst($acta->estado_acta) }}</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('coordinacion.actas.show', $acta->id_acta) }}" class="font-medium text-[#39A900] hover:underline">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">Aún no se han expedido actas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($actas ?? null, 'links'))
        {{ $actas->links() }}
    @endif
</div>

@section('scripts')
<script>
    const actasTrendLabels = @json($trendLabels ?? []);
    const actasTrendData = @json($actasTrend ?? []);
    const actasElement = document.getElementById('chart-actas-trend');

    if (actasElement) {
        new Chart(actasElement, {
            type: 'bar',
            data: {
                labels: actasTrendLabels,
                datasets: [{
                    label: 'Actas',
                    data: actasTrendData,
                    backgroundColor: '#00324dcc',
                    borderColor: '#00324d',
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

    const actasStateLabels = @json($statusLabels ?? ['Expedido','Notificado','Firme']);
    const actasStateData = @json($actasEstadoData ?? []);
    const actasStateElement = document.getElementById('chart-actas-state');

    if (actasStateElement) {
        new Chart(actasStateElement, {
            type: 'bar',
            data: {
                labels: actasStateLabels,
                datasets: [{
                    label: 'Estados',
                    data: actasStateData,
                    backgroundColor: ['#00324d33','#ff6a1333','#10b98133'],
                    borderColor: ['#00324d','#ff6a13','#10b981'],
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
