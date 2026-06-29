@extends('layouts.coordinador')

@section('titulo', 'Dashboard Institucional')

@section('contenido')
<div class="space-y-8">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-[#00324D]">Bienvenido al GEVLA</h2>
            <p class="mt-1 text-sm font-medium text-gray-500">Gestión, Evaluación y Valoración en Línea para Aprendices.</p>
        </div>
        <div class="flex items-center gap-2 text-sm font-bold text-[#39A900] bg-[#39A900]/10 px-4 py-2 rounded-full border border-[#39A900]/20">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Resumen en tiempo real
        </div>
    </div>

    {{-- Tarjetas de estadísticas Institucionales --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Tarjeta 1: Llamados --}}
        <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-[#FF6A13]"></div>
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-[#FF6A13]">Llamados de atención</p>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#FF6A13]/10 text-[#FF6A13]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
            <p class="mt-4 text-4xl font-black text-[#00324D]">{{ $totalLlamados ?? 0 }}</p>
            <div class="mt-4 flex items-center text-sm">
                <span class="flex items-center font-bold text-[#FF6A13] bg-[#FF6A13]/10 px-2 py-0.5 rounded">
                    {{ $llamadosPendientes ?? 0 }}
                </span>
                <span class="ml-2 font-medium text-gray-500">pendientes de revisión</span>
            </div>
        </div>

        {{-- Tarjeta 2: Actas --}}
        <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-[#00324D]"></div>
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-[#00324D]">Actas expedidas</p>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#00324D]/10 text-[#00324D]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M9 12h6M9 16h6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
            <p class="mt-4 text-4xl font-black text-[#00324D]">{{ $totalActas ?? 0 }}</p>
            <div class="mt-4 flex items-center text-sm">
                <span class="flex items-center font-bold text-[#00324D] bg-[#00324D]/10 px-2 py-0.5 rounded">
                    {{ $actasExpedidas ?? 0 }}
                </span>
                <span class="ml-2 font-medium text-gray-500">en estado expedido</span>
            </div>
        </div>

        {{-- Tarjeta 3: Procesos --}}
        <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-[#39A900]"></div>
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-[#39A900]">Procesos disciplinarios</p>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#39A900]/10 text-[#39A900]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 6h4v4H5V6Zm10 0h4v4h-4V6ZM5 16h4v4H5v-4Zm10 0h4v4h-4v-4M9 8h4m2 0h0M9 18h4m2-12v8m0 0v4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
            <p class="mt-4 text-4xl font-black text-[#00324D]">{{ $totalProcesos ?? 0 }}</p>
            <div class="mt-4 flex items-center text-sm">
                <span class="flex items-center font-bold text-[#39A900] bg-[#39A900]/10 px-2 py-0.5 rounded">
                    {{ $procesosActivos ?? 0 }}
                </span>
                <span class="ml-2 font-medium text-gray-500">activos actualmente</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Últimos llamados (ocupa 2 columnas) --}}
        <div class="lg:col-span-2 rounded-2xl border border-gray-100 bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-5 flex items-center justify-between">
                <h3 class="font-bold text-[#00324D] flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-[#FF6A13]"></span>
                    Últimos llamados de atención
                </h3>
                <a href="{{ route('coordinacion.llamados.index') }}" class="text-xs font-bold text-[#39A900] hover:text-[#247200] transition bg-[#39A900]/10 px-3 py-1.5 rounded-lg">
                    Ver todos
                </a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($llamadosRecientes ?? [] as $llamado)
                    <div class="flex items-center justify-between px-6 py-4 transition hover:bg-gray-50/50">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-full bg-[#00324D]/5 flex items-center justify-center font-bold text-[#00324D]">
                                {{ substr($llamado->aprendiz->usuario->nombres, 0, 1) }}{{ substr($llamado->aprendiz->usuario->apellidos, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-[#00324D]">{{ $llamado->aprendiz->usuario->nombres }} {{ $llamado->aprendiz->usuario->apellidos }}</p>
                                <p class="text-xs font-medium text-gray-500 mt-0.5">{{ $llamado->asunto }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-xs font-semibold text-gray-400 hidden sm:block">
                                {{ \Carbon\Carbon::parse($llamado->fecha_llamado)->format('d M, Y') }}
                            </span>
                            @php
                                $estadoBadge = match($llamado->estado_llamado) {
                                    'registrado'  => 'bg-gray-100 text-gray-600',
                                    'en_revision' => 'bg-[#FF6A13]/10 text-[#FF6A13] border border-[#FF6A13]/20',
                                    'notificado'  => 'bg-[#00324D]/10 text-[#00324D] border border-[#00324D]/20',
                                    'cerrado'     => 'bg-[#39A900]/10 text-[#39A900] border border-[#39A900]/20',
                                    'cancelado'   => 'bg-red-100 text-red-700',
                                    default       => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide {{ $estadoBadge }}">
                                {{ str($llamado->estado_llamado)->replace('_',' ') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="mx-auto h-12 w-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 mb-3">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">No hay llamados registrados aún.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Accesos rápidos (ocupa 1 columna) --}}
        <div class="space-y-4">
            <h3 class="font-bold text-[#00324D] px-1 text-sm uppercase tracking-wider">Accesos rápidos</h3>
            
            <a href="{{ route('coordinacion.actas.create') }}"
               class="group flex items-center justify-between rounded-2xl bg-gradient-to-r from-[#39A900] to-[#247200] p-5 shadow-[0_8px_30px_rgba(57,169,0,0.2)] transition hover:-translate-y-0.5">
                <div>
                    <p class="text-sm font-bold text-white">Expedir Acta</p>
                    <p class="text-xs font-medium text-green-100 mt-1">Crear nueva acta de coordinación</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition group-hover:scale-110">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </a>

            <a href="{{ route('coordinacion.procesos.index') }}"
               class="group flex items-center justify-between rounded-2xl bg-white border border-gray-100 p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition hover:border-[#00324D]/30 hover:shadow-[0_8px_30px_rgb(0,50,77,0.08)]">
                <div>
                    <p class="text-sm font-bold text-[#00324D]">Procesos Disciplinarios</p>
                    <p class="text-xs font-medium text-gray-500 mt-1">Hacer seguimiento a etapas</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#00324D]/5 text-[#00324D] transition group-hover:bg-[#00324D] group-hover:text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </a>
            
            <a href="{{ route('coordinacion.llamados.index') }}"
               class="group flex items-center justify-between rounded-2xl bg-white border border-gray-100 p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition hover:border-[#FF6A13]/30 hover:shadow-[0_8px_30px_rgb(255,106,19,0.08)]">
                <div>
                    <p class="text-sm font-bold text-[#00324D]">Consultar Llamados</p>
                    <p class="text-xs font-medium text-gray-500 mt-1">Revisar historial de reportes</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#FF6A13]/5 text-[#FF6A13] transition group-hover:bg-[#FF6A13] group-hover:text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </a>
        </div>
    </div>

</div>
@endsection
