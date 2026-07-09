@extends('layouts.aprendiz')

@section('titulo', 'Detalle del Llamado')

@section('contenido')
@php
    $estadoBadge = match($llamado->estado_llamado) {
        'registrado'  => 'bg-gray-100 text-gray-600',
        'en_revision' => 'bg-amber-100 text-amber-700',
        'notificado'  => 'bg-blue-100 text-blue-700',
        'cerrado'     => 'bg-green-100 text-green-700',
        'cancelado'   => 'bg-red-100 text-red-700',
        default       => 'bg-gray-100 text-gray-600',
    };
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('aprendiz.llamados.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
            ← Volver a mis llamados
        </a>
        <div class="flex flex-wrap items-center gap-3">
            {{-- Exportar este llamado en específico (PDF / Excel / Word). --}}
            @include('reportes._botones', ['rutaBase' => 'aprendiz.llamados.detalle.export', 'rutaParams' => ['id' => $llamado->id_llamado]])
            <a href="{{ asset('formatos/F002-008-25-formato-llamado-de-atencion-V01.pdf') }}" download="F002-008-25-Formato-Llamado-de-Atencion-V01.pdf"
               class="inline-flex items-center gap-2 rounded-full border border-[#39A900] px-4 py-2 text-sm font-bold text-[#39A900] transition hover:bg-[#39A900]/10"
               title="Descargar el formato oficial de llamado de atención del SENA (F002-008-25 V01) en PDF">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                Formato oficial (PDF)
            </a>
            {{-- Documento del llamado con las firmas registradas --}}
            <a href="{{ route('aprendiz.llamados.documento', $llamado->id_llamado) }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 rounded-full border border-[#00324D]/30 bg-[#00324D]/5 px-4 py-2 text-sm font-bold text-[#00324D] transition hover:bg-[#00324D]/10"
               title="Ver el documento del llamado con las firmas registradas (imprimible como PDF)">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5"/></svg>
                Documento firmado (PDF)
            </a>
        </div>
    </div>

    {{-- Firma del aprendiz: firmar (aceptar) el llamado o estado de la firma --}}
    @if(($puedeFirmar ?? false))
        <div class="flex flex-col gap-3 rounded-xl border border-[#39A900]/30 bg-[#39A900]/5 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold text-[#247200]">Este llamado está pendiente de tu firma</p>
                <p class="text-xs text-gray-600">
                    @if($tieneFirmaImg ?? false)
                        Al firmar, tu firma registrada en Mi Perfil se insertará en el documento y quedará la fecha y hora del acto.
                    @else
                        Primero registra tu firma en <a href="{{ route('perfil.show') }}" class="font-semibold underline">Mi Perfil (sección Firma)</a> y luego podrás firmar este llamado.
                    @endif
                </p>
            </div>
            <form method="POST" action="{{ route('aprendiz.llamados.firmar', $llamado->id_llamado) }}"
                  data-confirm="¿Firmar este llamado de atención? Tu firma quedará registrada con fecha y hora y se insertará en el documento."
                  data-confirm-title="Firmar llamado" data-confirm-btn="Sí, firmar">
                @csrf
                <button type="submit" @disabled(! ($tieneFirmaImg ?? false))
                        class="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-bold text-white shadow-sm transition {{ ($tieneFirmaImg ?? false) ? 'bg-[#39A900] hover:bg-[#247200]' : 'cursor-not-allowed bg-gray-300' }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg>
                    Firmar llamado
                </button>
            </form>
        </div>
    @elseif(($firmaAprendiz ?? null))
        <div class="flex items-center gap-3 rounded-xl border border-[#39A900]/20 bg-[#39A900]/5 px-4 py-3 text-sm font-medium text-[#247200]">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Firmaste este llamado el {{ $firmaAprendiz->fecha_firma->translatedFormat('d \d\e F \d\e Y \a \l\a\s h:i a') }}.
        </div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-[#00324D]">{{ $llamado->asunto }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Reportado el {{ \Carbon\Carbon::parse($llamado->fecha_llamado)->translatedFormat('d \d\e F \d\e Y') }}
                </p>
            </div>
            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-medium {{ $estadoBadge }}">
                {{ str($llamado->estado_llamado)->replace('_',' ')->ucfirst() }}
            </span>
        </div>

        <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Instructor que reporta</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $llamado->instructor->usuario->nombres }} {{ $llamado->instructor->usuario->apellidos }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Fecha del llamado</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($llamado->fecha_llamado)->translatedFormat('d \d\e F \d\e Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Tipo de llamado</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $llamado->tipo_label }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Categoría</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $llamado->categoria_label }}</dd>
            </div>
            @if($llamado->calificacion_falta)
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Calificación de la falta</dt>
                    @php
                        $califBadgeAp = match($llamado->calificacion_falta) {
                            'leve'      => 'bg-amber-100 text-amber-700',
                            'grave'     => 'bg-orange-100 text-orange-700',
                            'muy_grave' => 'bg-red-100 text-red-700',
                            default     => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <dd class="mt-1"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $califBadgeAp }}">{{ $llamado->calificacion_label }}</span></dd>
                </div>
            @endif
            <div>
                <dt class="text-xs font-medium uppercase text-gray-400">Estado del llamado</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $llamado->estado_label }}</dd>
            </div>
            @if($llamado->articulo)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase text-gray-400">Artículo del reglamento infringido</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="font-semibold">{{ $llamado->articulo->numero_articulo }}</span> — {{ $llamado->articulo->titulo }}
                        <a href="{{ route('reglamento.index', ['buscar' => $llamado->articulo->numero_articulo]) }}" class="ml-1 text-xs font-semibold text-[#39A900] hover:underline">Ver en el reglamento</a>
                    </dd>
                </div>
            @endif
        </dl>

        <div class="mt-6 space-y-4">
            <div>
                <h3 class="text-xs font-medium uppercase text-gray-400">Descripción de los hechos</h3>
                <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ $llamado->descripcion_hechos }}</p>
            </div>
            @include('llamados._pruebas_muestra')
        </div>
    </div>

    {{-- Faltas asociadas al llamado (si coordinación registró alguna) --}}
    @if($llamado->faltas && $llamado->faltas->count())
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="font-semibold text-gray-900">Faltas asociadas</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($llamado->faltas as $falta)
                    @php
                        $califBadge = match($falta->calificacion_falta) {
                            'leve'      => 'bg-green-100 text-green-700',
                            'grave'     => 'bg-amber-100 text-amber-700',
                            'muy_grave' => 'bg-red-100 text-red-700',
                            default     => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900">{{ $falta->principio_valor_infringido }}</p>
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $califBadge }}">
                                {{ str($falta->calificacion_falta)->replace('_',' ')->ucfirst() }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">{{ $falta->descripcion_hechos }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($llamado->observaciones)
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-[#00324D] mb-2">Observaciones de Coordinación</h3>
            <p class="text-sm text-gray-700">{{ $llamado->observaciones }}</p>
        </div>
    @endif
</div>
@endsection
