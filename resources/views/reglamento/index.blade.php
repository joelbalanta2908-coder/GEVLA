@extends($layout)

@section('titulo', 'Reglamento del Aprendiz')

@section('contenido')
<div class="mx-auto max-w-5xl space-y-6">
    {{-- Encabezado --}}
    <div class="overflow-hidden rounded-[28px] border border-[#e6eadf] bg-white p-6 shadow-[0_10px_30px_rgba(0,0,0,0.05)] sm:p-8">
        <p class="text-xs font-bold uppercase tracking-[0.28em] text-[#39A900]">Normatividad institucional</p>
        <h1 class="mt-2 text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ $reglamento->nombre_reglamento ?? 'Reglamento del Aprendiz SENA' }}</h1>
        <p class="mt-1 text-sm text-slate-500">
            {{ $reglamento->version ?? 'Acuerdo 09 de 2024' }}
            @if($reglamento && $reglamento->fecha_vigencia)
                · Vigente desde {{ \Illuminate\Support\Carbon::parse($reglamento->fecha_vigencia)->locale('es')->translatedFormat('d \d\e F \d\e Y') }}
            @endif
            · {{ $totalArticulos }} artículos
        </p>
        @if($reglamento && $reglamento->descripcion)
            <p class="mt-3 text-sm text-slate-600">{{ $reglamento->descripcion }}</p>
        @endif

        {{-- Buscador --}}
        <form method="GET" action="{{ route('reglamento.index') }}" class="mt-5 flex flex-col gap-3 sm:flex-row">
            <input type="text" name="buscar" value="{{ $buscar }}" placeholder="Buscar por número, título o contenido del artículo..."
                   class="w-full rounded-full border border-[#d9e4d4] bg-[#f8faf6] px-5 py-2.5 text-sm text-slate-900 caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            <select name="calificacion"
                    class="rounded-full border border-[#d9e4d4] bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                <option value="">Todas las faltas</option>
                @foreach($calificaciones as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected($calificacion == $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-full bg-[#39A900] px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#247200]">
                Buscar
            </button>
            @if($buscar !== '' || $calificacion)
                <a href="{{ route('reglamento.index') }}" class="inline-flex items-center justify-center rounded-full border border-[#d8e2cf] bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Limpiar
                </a>
            @endif
        </form>
    </div>

    {{-- Capítulos --}}
    @forelse($capitulos as $capitulo)
        <div x-data="{ abierto: {{ ($buscar !== '' || $calificacion) ? 'true' : 'false' }} }"
             class="overflow-hidden rounded-[24px] border border-[#e6eadf] bg-white shadow-[0_8px_24px_rgba(0,0,0,0.04)]">
            <button type="button" @click="abierto = !abierto"
                    class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left transition hover:bg-[#fafbf8]">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#39A900]/10 text-sm font-black text-[#39A900]">{{ $capitulo->numero_capitulo }}</span>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Capítulo {{ $capitulo->numero_capitulo }}</p>
                        <h2 class="text-base font-extrabold text-slate-900 sm:text-lg">{{ $capitulo->titulo }}</h2>
                    </div>
                </div>
                <svg class="h-5 w-5 shrink-0 text-slate-400 transition-transform duration-200" :class="abierto && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </button>

            <div x-show="abierto" x-cloak class="border-t border-[#eef1e8]">
                <div class="divide-y divide-[#f1f4ee]">
                    @foreach($capitulo->articulos as $articulo)
                        <article class="px-6 py-5">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-extrabold text-slate-900">{{ $articulo->numero_articulo }} — {{ $articulo->titulo }}</h3>
                                @if($articulo->calificacion)
                                    @php
                                        $badge = match($articulo->calificacion) {
                                            'leve' => 'bg-amber-100 text-amber-700',
                                            'grave' => 'bg-orange-100 text-orange-700',
                                            'muy_grave' => 'bg-red-100 text-red-700',
                                            default => 'bg-slate-100 text-slate-600',
                                        };
                                    @endphp
                                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-[0.12em] {{ $badge }}">
                                        {{ $calificaciones[$articulo->calificacion] ?? $articulo->calificacion }}
                                    </span>
                                @endif
                            </div>
                            @if($articulo->contenido)
                                <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $articulo->contenido }}</p>
                            @endif
                            @if($articulo->paragrafos->isNotEmpty())
                                <div class="mt-3 space-y-2 border-l-2 border-[#39A900]/20 pl-4">
                                    @foreach($articulo->paragrafos as $paragrafo)
                                        <p class="text-sm text-slate-500"><span class="font-semibold text-slate-700">{{ $paragrafo->numero_paragrafo }}:</span> {{ $paragrafo->contenido }}</p>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-[24px] border border-[#e6eadf] bg-white p-10 text-center text-slate-500 shadow-sm">
            <p class="text-sm">No se encontraron artículos para tu búsqueda.</p>
        </div>
    @endforelse
</div>
@endsection
