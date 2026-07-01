@extends('layouts.instructor')

@section('titulo', 'Mis Reportes')

@section('contenido')
<div class="space-y-5">
    {{-- Encabezado --}}
    <div class="overflow-hidden rounded-[24px] border border-[#e6eadf] bg-white p-6 shadow-[0_10px_28px_rgba(0,0,0,0.04)] sm:p-7">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#39A900]/10 text-[#39A900]">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/>
                    </svg>
                </span>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-[#39A900]">Seguimiento disciplinario</p>
                    <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Mis Reportes</h2>
                    <p class="mt-1 text-sm text-slate-500">Listado de los llamados de atención que has emitido a los aprendices.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-2 rounded-full border border-[#e6eadf] bg-[#f8faf6] px-4 py-2.5 text-sm font-bold text-slate-600">
                    {{ $llamados->total() }} {{ \Illuminate\Support\Str::plural('reporte', $llamados->total()) }}
                </span>
                {{-- Exportar reportes --}}
                <div class="inline-flex items-center rounded-full border border-[#e6eadf] bg-white p-1 shadow-sm">
                    <span class="px-3 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Exportar</span>
                    <a href="{{ route('instructor.llamados.export', 'pdf') }}" target="_blank" rel="noopener"
                       class="rounded-full px-3 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-50" title="Abrir versión imprimible / Guardar como PDF">PDF</a>
                    <a href="{{ route('instructor.llamados.export', 'excel') }}"
                       class="rounded-full px-3 py-1.5 text-xs font-bold text-[#247200] transition hover:bg-[#39A900]/10" title="Descargar Excel (.xls)">Excel</a>
                    <a href="{{ route('instructor.llamados.export', 'word') }}"
                       class="rounded-full px-3 py-1.5 text-xs font-bold text-blue-600 transition hover:bg-blue-50" title="Descargar Word (.doc)">Word</a>
                </div>
                <a href="{{ route('instructor.llamados.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-full bg-[#39A900] px-5 py-2.5 text-sm font-bold text-white shadow-[0_10px_24px_rgba(57,169,0,0.28)] transition hover:bg-[#247200]">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    Nuevo llamado
                </a>
            </div>
        </div>
    </div>

    {{-- Listado --}}
    <div class="overflow-hidden rounded-[24px] border border-[#e6eadf] bg-white shadow-[0_10px_28px_rgba(0,0,0,0.04)]">
        @if($llamados->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-400">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <p class="text-base font-semibold text-slate-600">Aún no has reportado ningún llamado de atención.</p>
                <p class="mt-1 text-sm text-slate-400">Usa el botón «Nuevo llamado» para registrar el primero.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="responsive-cards w-full min-w-[640px] text-left text-sm text-slate-600">
                    <thead class="border-b border-[#eef1e8] bg-[#fafbf8] text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Fecha</th>
                            <th class="px-6 py-4">Aprendiz</th>
                            <th class="px-6 py-4">Asunto</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f1f4ee]">
                        @foreach($llamados as $llamado)
                            @php
                                $estadoBadge = match($llamado->estado_llamado) {
                                    'registrado'  => 'bg-slate-100 text-slate-600',
                                    'en_revision' => 'bg-amber-100 text-amber-700',
                                    'notificado'  => 'bg-blue-100 text-blue-700',
                                    'cerrado'     => 'bg-[#39A900]/10 text-[#247200]',
                                    'cancelado'   => 'bg-red-100 text-red-700',
                                    default       => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr>
                                <td class="px-6 py-4 font-bold text-slate-900" data-label="ID">#{{ $llamado->id_llamado }}</td>
                                <td class="px-6 py-4" data-label="Fecha">{{ \Carbon\Carbon::parse($llamado->fecha_llamado)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 font-medium text-slate-900" data-label="Aprendiz">{{ $llamado->aprendiz->usuario->nombres }} {{ $llamado->aprendiz->usuario->apellidos }}</td>
                                <td class="px-6 py-4" data-label="Asunto">{{ $llamado->asunto }}</td>
                                <td class="px-6 py-4" data-label="Estado">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.08em] {{ $estadoBadge }}">
                                        {{ str($llamado->estado_llamado)->replace('_',' ')->ucfirst() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right" data-label="Acciones">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('instructor.llamados.show', $llamado->id_llamado) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition hover:bg-blue-100" title="Ver detalle">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>
                                        @if($llamado->estado_llamado === 'registrado')
                                            <a href="{{ route('instructor.llamados.edit', $llamado->id_llamado) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition hover:bg-amber-100" title="Editar">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </a>
                                            <form method="POST" action="{{ route('instructor.llamados.destroy', $llamado->id_llamado) }}" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este reporte?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-red-50 text-red-600 transition hover:bg-red-100" title="Eliminar">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[#eef1e8] px-6 py-4">
                {{ $llamados->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
