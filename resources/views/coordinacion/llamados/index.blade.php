@extends('layouts.coordinador')

@section('titulo', 'Llamados de atención')

@section('contenido')
<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Llamados de atención</h2>
            <p class="text-gray-500">Revisa y da seguimiento a los llamados reportados por los instructores.</p>
        </div>
        @include('reportes._botones', ['rutaBase' => 'coordinacion.llamados.export'])
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

    <form method="GET" class="flex flex-wrap gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por aprendiz o asunto"
               class="min-w-[220px] flex-1 rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">

        <select name="categoria" class="rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
            <option value="">Categoría: todas</option>
            <option value="academico" @selected(request('categoria') == 'academico')>Académico</option>
            <option value="disciplinario" @selected(request('categoria') == 'disciplinario')>Disciplinario</option>
        </select>

        <select name="estado" class="rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
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
        <table class="responsive-cards w-full min-w-[640px] text-sm">
            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3">Aprendiz</th>
                    <th class="px-5 py-3">Instructor</th>
                    <th class="px-5 py-3">Fecha</th>
                    <th class="px-5 py-3">Categoría</th>
                    <th class="px-5 py-3">Asunto</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($llamados as $llamado)
                    @php
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
                        <td class="px-5 py-3 font-medium text-gray-900" data-label="Aprendiz">{{ $llamado->aprendiz->usuario->nombres }} {{ $llamado->aprendiz->usuario->apellidos }}</td>
                        <td class="px-5 py-3 text-gray-600" data-label="Instructor">{{ $llamado->instructor->usuario->nombres }} {{ $llamado->instructor->usuario->apellidos }}</td>
                        <td class="px-5 py-3 text-gray-600" data-label="Fecha">{{ \Carbon\Carbon::parse($llamado->fecha_llamado)->format('d/m/Y') }}</td>
                        <td class="px-5 py-3" data-label="Categoría">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $catBadge }}">{{ ucfirst($llamado->categoria) }}</span>
                        </td>
                        <td class="px-5 py-3 text-gray-600" data-label="Asunto">{{ $llamado->asunto }}</td>
                        <td class="px-5 py-3" data-label="Estado">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $estadoBadge }}">
                                {{ str($llamado->estado_llamado)->replace('_',' ')->ucfirst() }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right" data-label="Acción">
                            <a href="{{ route('coordinacion.llamados.show', $llamado->id_llamado) }}" class="font-medium text-[#39A900] hover:underline">Ver detalle</a>
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
