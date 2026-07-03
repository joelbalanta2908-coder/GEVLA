@extends('layouts.coordinador')

@section('titulo', 'Reportes')

@section('contenido')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Reportes</h2>
        <p class="mt-1 text-sm text-gray-500">Genera y exporta los reportes institucionales en PDF, Excel o Word. Puedes clasificar cada reporte por ficha antes de exportarlo.</p>
    </div>

    @php
        $tarjetas = [
            [
                'titulo'      => 'Llamados de atención',
                'descripcion' => 'Reporte con la fecha, el aprendiz y su documento, la ficha, el programa, el instructor líder, el instructor que reportó, el asunto y el estado.',
                'total'       => $resumen['llamados'],
                'rutaBase'    => 'coordinacion.llamados.export',
                'indice'      => route('coordinacion.llamados.index'),
                'icon'        => 'M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9',
                'accent'      => 'bg-[#39A900]/10 text-[#39A900]',
            ],
            [
                'titulo'      => 'Actas de coordinación',
                'descripcion' => 'Reporte con el número de acta, la fecha de expedición, el aprendiz y su documento, la ficha, el programa, el instructor líder, el tipo y el estado.',
                'total'       => $resumen['actas'],
                'rutaBase'    => 'coordinacion.actas.export',
                'indice'      => route('coordinacion.actas.index'),
                'icon'        => 'M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M9 12h6M9 16h6',
                'accent'      => 'bg-[#00324d]/10 text-[#00324d]',
            ],
            [
                'titulo'      => 'Procesos disciplinarios',
                'descripcion' => 'Reporte con la fecha de inicio, el aprendiz y su documento, la ficha, el programa, el instructor líder, el instructor que reportó, la etapa y el estado.',
                'total'       => $resumen['procesos'],
                'rutaBase'    => 'coordinacion.procesos.export',
                'indice'      => route('coordinacion.procesos.index'),
                'icon'        => 'M5 6h4v4H5V6Zm10 0h4v4h-4V6ZM5 16h4v4H5v-4Zm10 0h4v4h-4v-4M9 8h4m2 0h0M9 18h4m2-12v8m0 0v4',
                'accent'      => 'bg-[#ff6a13]/10 text-[#ff6a13]',
            ],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        @foreach($tarjetas as $t)
            <div class="flex flex-col overflow-hidden rounded-[28px] border border-[#e6eadf] bg-white shadow-[0_10px_28px_rgba(0,0,0,0.04)]">
                <div class="flex items-start justify-between gap-3 border-b border-[#eef1e8] bg-[#fafbf8] px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $t['accent'] }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $t['icon'] }}"/></svg>
                        </span>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">{{ $t['titulo'] }}</h3>
                            <p class="text-xs font-semibold text-slate-400">{{ $t['total'] }} {{ \Illuminate\Support\Str::plural('registro', $t['total']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-1 flex-col justify-between gap-4 px-5 py-4">
                    <p class="text-sm text-slate-500">{{ $t['descripcion'] }}</p>

                    <div class="space-y-3">
                        @include('reportes._botones', ['rutaBase' => $t['rutaBase'], 'fichas' => $fichas])
                        <a href="{{ $t['indice'] }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#39A900] transition hover:text-[#247200]">
                            Ir al listado completo
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-blue-100 bg-blue-50/60 px-4 py-3 text-xs text-blue-700">
        El formato PDF abre una vista imprimible (usa «Guardar como PDF» del navegador). Excel y Word descargan el archivo directamente. Si eliges una ficha en el selector, el reporte solo incluirá los aprendices matriculados en ella.
    </div>
</div>
@endsection
