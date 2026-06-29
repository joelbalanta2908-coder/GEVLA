@extends('layouts.coordinador')

@section('titulo', 'Dashboard Institucional')

@section('contenido')
<div class="space-y-5">
    @php
        $porcentajeCierre = $totalLlamados > 0
            ? round((($totalLlamados - $llamadosPendientes) / $totalLlamados) * 100, 1)
            : 0;

        $metricas = [
            [
                'label' => 'Llamados de atención',
                'value' => $totalLlamados ?? 0,
                'icon' => 'bell',
                'accent' => 'text-[#39A900]',
                'tone' => 'bg-[#39A900]/10',
                'border' => 'border-[#94d46f]',
            ],
            [
                'label' => 'Pendientes de revisión',
                'value' => $llamadosPendientes ?? 0,
                'icon' => 'clock',
                'accent' => 'text-[#ff6a13]',
                'tone' => 'bg-[#ff6a13]/10',
                'border' => 'border-[#ffb07a]',
            ],
            [
                'label' => 'Actas expedidas',
                'value' => $totalActas ?? 0,
                'icon' => 'doc',
                'accent' => 'text-[#00324d]',
                'tone' => 'bg-[#00324d]/10',
                'border' => 'border-[#9fb0bb]',
            ],
            [
                'label' => 'Procesos activos',
                'value' => $procesosActivos ?? 0,
                'icon' => 'flow',
                'accent' => 'text-[#39A900]',
                'tone' => 'bg-[#39A900]/10',
                'border' => 'border-[#94d46f]',
            ],
            [
                'label' => 'Tasa global de cierre',
                'value' => $porcentajeCierre.'%',
                'icon' => 'pulse',
                'accent' => 'text-[#39A900]',
                'tone' => 'bg-[#f4f9ee]',
                'border' => 'border-[#39A900]',
                'featured' => true,
            ],
        ];

        $icons = [
            'bell' => 'M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9',
            'clock' => 'M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            'doc' => 'M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M9 12h6M9 16h6',
            'flow' => 'M5 6h4v4H5V6Zm10 0h4v4h-4V6ZM5 16h4v4H5v-4Zm10 0h4v4h-4v-4M9 8h4m2 0h0M9 18h4m2-12v8m0 0v4',
            'pulse' => 'M3 12h4l2-6 4 12 2-6h6',
        ];
    @endphp

    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <p class="text-[11px] font-extrabold uppercase tracking-[0.32em] text-[#39A900]">Gestión académica</p>
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-[2rem] lg:text-[2.25rem]">Panel Administrativo</h2>
            <p class="max-w-2xl text-base text-slate-500">Bienvenido, {{ auth()->user()->nombres ?? 'Coordinador' }}. Aquí tienes el seguimiento general de llamados, actas y procesos disciplinarios.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('coordinacion.actas.index') }}" class="rounded-2xl border border-[#d8e2cf] bg-white px-4 py-3 text-sm font-extrabold text-[#39A900] shadow-[0_10px_30px_rgba(0,0,0,0.04)] transition hover:border-[#b9d8a5] hover:shadow-[0_12px_34px_rgba(57,169,0,0.08)]">
                Reportes
            </a>
            <a href="{{ route('coordinacion.actas.create') }}" class="rounded-2xl bg-gradient-to-r from-[#39A900] to-[#2f8b00] px-4 py-3 text-sm font-extrabold text-white shadow-[0_14px_32px_rgba(57,169,0,0.28)] transition hover:shadow-[0_16px_36px_rgba(57,169,0,0.35)]">
                Crear acta
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach($metricas as $metrica)
            <div class="relative overflow-hidden rounded-3xl border bg-white p-4 shadow-[0_10px_28px_rgba(0,0,0,0.04)] transition hover:-translate-y-0.5 hover:shadow-[0_14px_34px_rgba(0,0,0,0.07)] {{ $metrica['featured'] ?? false ? 'border-[#39A900] bg-[#f6fbea]' : $metrica['border'].' border-opacity-60' }}">
                <div class="absolute inset-x-0 top-0 h-1 {{ $metrica['featured'] ?? false ? 'bg-[#39A900]' : ($metrica['accent'] === 'text-[#ff6a13]' ? 'bg-[#ff6a13]' : ($metrica['accent'] === 'text-[#00324d]' ? 'bg-[#00324d]' : 'bg-[#39A900]')) }}"></div>
                <div class="mb-7 flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl {{ $metrica['tone'] }} {{ $metrica['accent'] }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="{{ $icons[$metrica['icon']] }}" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">{{ $metrica['label'] }}</p>
                <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-[2rem]">{{ $metrica['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2 overflow-hidden rounded-[28px] border border-[#e6eadf] bg-white shadow-[0_10px_28px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between border-b border-[#eef1e8] bg-[#fafbf8] px-5 py-4 sm:px-6">
                <div>
                    <h3 class="flex items-center gap-2 text-base font-extrabold text-slate-900 sm:text-lg">
                        <span class="h-3 w-3 rounded-full bg-[#39A900]"></span>
                        Últimos llamados de atención
                    </h3>
                    <p class="mt-1 text-sm text-slate-500 sm:text-base">Registro reciente de seguimiento disciplinario.</p>
                </div>
                <a href="{{ route('coordinacion.llamados.index') }}" class="rounded-xl border border-[#d8e2cf] bg-white px-4 py-2.5 text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#39A900] transition hover:border-[#b9d8a5]">
                    Ver todos
                </a>
            </div>

            <div class="divide-y divide-[#eef1e8]">
                @forelse($llamadosRecientes ?? [] as $llamado)
                    <div class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-[#fbfcf8] sm:px-6">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[#39A900]/10 text-sm font-black text-[#39A900]">
                                {{ substr($llamado->aprendiz->usuario->nombres, 0, 1) }}{{ substr($llamado->aprendiz->usuario->apellidos, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-base font-bold text-slate-900">{{ $llamado->aprendiz->usuario->nombres }} {{ $llamado->aprendiz->usuario->apellidos }}</p>
                                <p class="truncate text-sm font-medium text-slate-500">{{ $llamado->asunto }}</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                            <span class="hidden text-xs font-semibold text-slate-400 sm:block">{{ \Carbon\Carbon::parse($llamado->fecha_llamado)->format('d M, Y') }}</span>
                            @php
                                $estadoBadge = match($llamado->estado_llamado) {
                                    'registrado'  => 'bg-slate-100 text-slate-600',
                                    'en_revision' => 'bg-[#ff6a13]/10 text-[#ff6a13] border border-[#ff6a13]/20',
                                    'notificado'  => 'bg-[#00324d]/10 text-[#00324d] border border-[#00324d]/20',
                                    'cerrado'     => 'bg-[#39A900]/10 text-[#39A900] border border-[#39A900]/20',
                                    'cancelado'   => 'bg-red-100 text-red-700',
                                    default       => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <span class="rounded-full px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.18em] {{ $estadoBadge }}">
                                {{ str($llamado->estado_llamado)->replace('_',' ') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        </div>
                        <p class="text-base font-medium text-slate-500">No hay llamados registrados aún.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-[28px] border border-[#e6eadf] bg-white p-5 shadow-[0_10px_28px_rgba(0,0,0,0.04)]">
                <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-[#39A900]">Accesos rápidos</p>
                <h3 class="mt-2 text-base font-extrabold text-slate-900">Gestión directa</h3>
                <p class="mt-1 text-base text-slate-500">Accede a los módulos principales sin salir del panel.</p>
            </div>

            <a href="{{ route('coordinacion.actas.create') }}" class="group flex items-center justify-between rounded-[26px] bg-gradient-to-r from-[#39A900] to-[#2f8b00] p-4 shadow-[0_14px_32px_rgba(57,169,0,0.24)] transition hover:-translate-y-0.5">
                <div>
                    <p class="text-base font-extrabold text-white">Crear acta</p>
                    <p class="mt-1 text-sm font-medium text-green-100">Registrar una nueva acta de coordinación</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition group-hover:scale-110">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </a>

            <a href="{{ route('coordinacion.procesos.index') }}" class="group flex items-center justify-between rounded-[26px] border border-[#e6eadf] bg-white p-4 shadow-[0_10px_28px_rgba(0,0,0,0.04)] transition hover:border-[#39A900]/30 hover:shadow-[0_14px_34px_rgba(57,169,0,0.08)]">
                <div>
                    <p class="text-base font-extrabold text-slate-900">Procesos disciplinarios</p>
                    <p class="mt-1 text-sm font-medium text-slate-500">Hacer seguimiento a etapas</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#39A900]/10 text-[#39A900] transition group-hover:bg-[#39A900] group-hover:text-white">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </a>

            <a href="{{ route('coordinacion.llamados.index') }}" class="group flex items-center justify-between rounded-[26px] border border-[#e6eadf] bg-white p-4 shadow-[0_10px_28px_rgba(0,0,0,0.04)] transition hover:border-[#ff6a13]/30 hover:shadow-[0_14px_34px_rgba(255,106,19,0.08)]">
                <div>
                    <p class="text-base font-extrabold text-slate-900">Consultar llamados</p>
                    <p class="mt-1 text-sm font-medium text-slate-500">Revisar historial de reportes</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#ff6a13]/10 text-[#ff6a13] transition group-hover:bg-[#ff6a13] group-hover:text-white">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
