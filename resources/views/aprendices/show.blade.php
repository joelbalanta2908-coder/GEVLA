@extends($layout)

@section('titulo', 'Información del aprendiz')

@section('contenido')
@php $u = $aprendiz->usuario; @endphp
<div class="mx-auto max-w-6xl space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ $volver }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-[#39A900]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 19l-7-7 7-7"/></svg>
            Volver
        </a>
        @php $esCoordinadorPerfil = ($rolActivo ?? session('rol_activo')) === \App\Support\Roles::COORDINADOR; @endphp
        <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
            {{-- Reporte completo del aprendiz: todos sus llamados, actas y procesos en un solo PDF. --}}
            @if($esCoordinadorPerfil)
                <a href="{{ route('coordinacion.aprendices.reporte', ['id' => $aprendiz->id_aprendiz, 'formato' => 'pdf']) }}" target="_blank" rel="noopener"
                   title="Descargar el reporte completo del aprendiz con todos sus llamados (PDF)"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-[#39A900] px-3.5 py-2 text-sm font-semibold text-[#39A900] transition hover:bg-[#39A900]/10">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                    Descargar reporte completo
                </a>
            @endif
            {{-- Solo el coordinador recibe la URL de edición; los demás roles ven la hoja de vida en solo lectura. --}}
            @isset($editarUrl)
                <a href="{{ $editarUrl }}" title="Editar datos del aprendiz"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-amber-50 px-3.5 py-2 text-sm font-semibold text-amber-700 ring-1 ring-amber-200 transition hover:bg-amber-100">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editar datos
                </a>
            @endisset
        </div>
    </div>

    {{-- Cabecera del aprendiz --}}
    <section class="overflow-hidden rounded-[28px] border border-[#e6eadf] bg-white shadow-[0_12px_40px_rgba(0,0,0,0.06)]">
        <div class="flex flex-col gap-4 border-b border-[#eef1e8] bg-[#fafbf8] px-6 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <div class="flex items-center gap-4">
                @if($u && $u->fotoUrl())
                    <img src="{{ $u->fotoUrl() }}" alt="Foto" class="h-16 w-16 rounded-3xl object-cover shadow-sm">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-[#e8f7e7] text-2xl font-extrabold text-[#39A900] shadow-sm">
                        {{ $u?->iniciales() ?? 'A' }}
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900">{{ $u->nombres ?? '' }} {{ $u->apellidos ?? '' }}</h1>
                    {{-- Correo principal de la cuenta; si no existe, el institucional. --}}
                    <p class="mt-1 text-sm text-slate-500">{{ $u->correo ?? $aprendiz->correo_institucional ?? 'Sin correo' }}</p>
                </div>
            </div>
            @php
                $estadoBadge = match($aprendiz->estado_academico) {
                    'en_formacion' => 'bg-[#39A900]/10 text-[#247200]',
                    'aplazado' => 'bg-amber-100 text-amber-700',
                    'cancelado' => 'bg-red-100 text-red-700',
                    'certificado' => 'bg-blue-100 text-blue-700',
                    default => 'bg-slate-100 text-slate-600',
                };
            @endphp
            <span class="inline-flex w-fit items-center gap-2 rounded-full px-4 py-2 text-sm font-bold {{ $estadoBadge }}">
                {{ ucfirst(str_replace('_', ' ', $aprendiz->estado_academico ?? 'Sin estado')) }}
            </span>
        </div>

        {{-- Indicadores --}}
        <div class="grid grid-cols-2 gap-4 px-6 py-6 sm:grid-cols-4 sm:px-8">
            <div class="rounded-2xl bg-[#f6faf4] p-4 text-center">
                <p class="text-2xl font-extrabold text-slate-900">{{ $aprendiz->llamadosAtencion->count() }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Llamados</p>
            </div>
            <div class="rounded-2xl bg-[#f7f8fb] p-4 text-center">
                <p class="text-2xl font-extrabold text-slate-900">{{ $aprendiz->actasCoordinacion->count() }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Actas</p>
            </div>
            <div class="rounded-2xl bg-[#f9f7ef] p-4 text-center">
                <p class="text-2xl font-extrabold text-slate-900">{{ $aprendiz->procesosDisciplinarios->count() }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Procesos</p>
            </div>
            <div class="rounded-2xl bg-[#f4f9ee] p-4 text-center">
                <p class="text-2xl font-extrabold text-slate-900">{{ $aprendiz->matriculas->count() }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Fichas</p>
            </div>
        </div>
    </section>

    {{-- Datos personales y de la cuenta --}}
    <section class="overflow-hidden rounded-[24px] border border-[#e6eadf] bg-white shadow-sm">
        <div class="border-b border-[#eef1e8] px-6 py-4"><h2 class="text-base font-extrabold text-slate-900">Datos personales</h2></div>
        <dl class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <dt class="text-xs font-medium uppercase text-slate-400">Nombres</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $u?->nombres ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-slate-400">Apellidos</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $u?->apellidos ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-slate-400">Documento</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $u ? $u->tipo_documento . ' ' . $u->numero_documento : '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-slate-400">Teléfono</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $u?->telefono ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-slate-400">Correo principal</dt>
                <dd class="mt-1 break-all text-sm text-slate-900">{{ $u?->correo ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-slate-400">Correo institucional</dt>
                <dd class="mt-1 break-all text-sm text-slate-900">{{ $aprendiz->correo_institucional ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-slate-400">Usuario de acceso</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $u?->username ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-slate-400">Estado de la cuenta</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $u ? ucfirst($u->estado_usuario) : 'Sin cuenta' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-slate-400">Último acceso</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $u?->ultimo_acceso ? $u->ultimo_acceso->locale('es')->diffForHumans() : 'Nunca' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-slate-400">Apoyo de sostenimiento</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $aprendiz->tiene_apoyo_sostenimiento ? 'Sí' : 'No' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium uppercase text-slate-400">Roles en el sistema</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $u ? (implode(' · ', \App\Support\Roles::disponiblesPara($u)) ?: 'Aprendiz') : '—' }}</dd>
            </div>
        </dl>
    </section>

    {{-- Fichas / matrículas --}}
    @if($aprendiz->matriculas->isNotEmpty())
        <section class="overflow-hidden rounded-[24px] border border-[#e6eadf] bg-white shadow-sm">
            <div class="border-b border-[#eef1e8] px-6 py-4"><h2 class="text-base font-extrabold text-slate-900">Formación</h2></div>
            <div class="divide-y divide-[#f1f4ee]">
                @foreach($aprendiz->matriculas as $m)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ optional($m->ficha->programa)->nombre_programa ?? 'Programa' }}</p>
                            <p class="text-xs text-slate-500">Ficha {{ optional($m->ficha)->numero_ficha ?? '—' }} · {{ optional($m->ficha)->modalidad_label ?? '' }}</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ ucfirst($m->estado_matricula) }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Llamados --}}
    <section class="overflow-hidden rounded-[24px] border border-[#e6eadf] bg-white shadow-sm">
        <div class="border-b border-[#eef1e8] px-6 py-4"><h2 class="text-base font-extrabold text-slate-900">Llamados de atención</h2></div>
        @if($aprendiz->llamadosAtencion->isEmpty())
            <p class="px-6 py-8 text-center text-sm text-slate-400">Sin llamados registrados.</p>
        @else
            <div class="overflow-x-auto">
                <table class="responsive-cards w-full min-w-[640px] text-left text-sm text-slate-600">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                        <tr>
                            <th class="whitespace-nowrap px-6 py-3">Fecha</th>
                            <th class="whitespace-nowrap px-6 py-3">Asunto</th>
                            <th class="whitespace-nowrap px-6 py-3">Categoría</th>
                            <th class="whitespace-nowrap px-6 py-3">Calificación</th>
                            <th class="whitespace-nowrap px-6 py-3">Estado</th>
                            <th class="whitespace-nowrap px-6 py-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($aprendiz->llamadosAtencion as $ll)
                            <tr class="hover:bg-gray-50/50">
                                <td class="whitespace-nowrap px-6 py-3.5" data-label="Fecha">{{ \Illuminate\Support\Carbon::parse($ll->fecha_llamado)->format('d/m/Y') }}</td>
                                <td class="px-6 py-3.5 font-medium text-slate-900" data-label="Asunto">{{ $ll->asunto }}</td>
                                <td class="whitespace-nowrap px-6 py-3.5" data-label="Categoría">{{ $ll->categoria_label }}</td>
                                <td class="whitespace-nowrap px-6 py-3.5" data-label="Calificación">{{ $ll->calificacion_label }}</td>
                                <td class="whitespace-nowrap px-6 py-3.5" data-label="Estado">{{ $ll->estado_label }}</td>
                                <td class="px-6 py-3.5 text-right" data-label="Acción">
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        @if($esCoordinadorPerfil)
                                            <a href="{{ route('coordinacion.llamados.show', $ll->id_llamado) }}" class="font-medium text-[#39A900] hover:underline">Ver</a>
                                            <a href="{{ route('coordinacion.llamados.documento', $ll->id_llamado) }}" target="_blank" rel="noopener"
                                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition hover:bg-blue-100" title="Descargar este llamado (PDF)">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                                            </a>
                                        @elseif(($rolActivo ?? session('rol_activo')) === \App\Support\Roles::INSTRUCTOR)
                                            <a href="{{ route('instructor.llamados.show', $ll->id_llamado) }}" class="font-medium text-[#39A900] hover:underline">Ver</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Actas y procesos --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <section class="overflow-hidden rounded-[24px] border border-[#e6eadf] bg-white shadow-sm">
            <div class="border-b border-[#eef1e8] px-6 py-4"><h2 class="text-base font-extrabold text-slate-900">Actas de coordinación</h2></div>
            @if($aprendiz->actasCoordinacion->isEmpty())
                <p class="px-6 py-8 text-center text-sm text-slate-400">Sin actas.</p>
            @else
                <ul class="divide-y divide-[#f1f4ee]">
                    @foreach($aprendiz->actasCoordinacion as $acta)
                        <li class="flex items-center justify-between gap-3 px-6 py-4">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $acta->numero_acta }}</p>
                                <p class="text-xs text-slate-500">{{ str($acta->tipo_acta)->replace('_',' ')->ucfirst() }} · {{ \Illuminate\Support\Carbon::parse($acta->fecha_expedicion)->format('d/m/Y') }}</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ ucfirst($acta->estado_acta) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="overflow-hidden rounded-[24px] border border-[#e6eadf] bg-white shadow-sm">
            <div class="border-b border-[#eef1e8] px-6 py-4"><h2 class="text-base font-extrabold text-slate-900">Procesos disciplinarios</h2></div>
            @if($aprendiz->procesosDisciplinarios->isEmpty())
                <p class="px-6 py-8 text-center text-sm text-slate-400">Sin procesos.</p>
            @else
                <ul class="divide-y divide-[#f1f4ee]">
                    @foreach($aprendiz->procesosDisciplinarios as $proceso)
                        <li class="flex items-center justify-between gap-3 px-6 py-4">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ str($proceso->etapa_actual)->replace('_',' ')->ucfirst() }}</p>
                                <p class="text-xs text-slate-500">Inicio {{ \Illuminate\Support\Carbon::parse($proceso->fecha_inicio)->format('d/m/Y') }}</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ ucfirst($proceso->estado_proceso) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</div>
@endsection
