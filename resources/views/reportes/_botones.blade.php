{{-- Botones de exportación de reportes. Requiere: $rutaBase (nombre de ruta con {formato}).
     Opcional: $fichas (colección de fichas) para filtrar el reporte por ficha.
     Opcional: $rutaParams (array) con parámetros de ruta extra, p. ej. el id de
     un llamado específico: ['id' => 5]. --}}
@php $rutaParams = $rutaParams ?? []; @endphp
<div class="flex flex-wrap items-center gap-2" data-export-grupo>
    @isset($fichas)
        <label class="sr-only" for="export-ficha-{{ md5($rutaBase) }}">Filtrar reporte por ficha</label>
        <select id="export-ficha-{{ md5($rutaBase) }}" data-export-ficha title="Filtrar el reporte por ficha"
                class="rounded-full border border-[#e6eadf] bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            <option value="">Reporte: todas las fichas</option>
            @foreach($fichas as $f)
                <option value="{{ $f->id_ficha }}">Ficha {{ $f->numero_ficha }}</option>
            @endforeach
        </select>
    @endisset

    <div class="inline-flex items-center rounded-full border border-[#e6eadf] bg-white p-1 shadow-sm">
        <span class="px-3 text-[11px] font-bold uppercase tracking-[0.14em] text-black">Exportar</span>

        <a href="{{ route($rutaBase, array_merge($rutaParams, ['formato' => 'pdf'])) }}" data-export-url="{{ route($rutaBase, array_merge($rutaParams, ['formato' => 'pdf'])) }}" target="_blank" rel="noopener"
           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-50" title="Abrir versión imprimible / Guardar como PDF">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5"/><path d="M9 13h1.5a1.5 1.5 0 0 1 0 3H9v-3Zm0 0v5"/></svg>
            PDF
        </a>

        <a href="{{ route($rutaBase, array_merge($rutaParams, ['formato' => 'excel'])) }}" data-export-url="{{ route($rutaBase, array_merge($rutaParams, ['formato' => 'excel'])) }}"
           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold text-[#247200] transition hover:bg-[#39A900]/10" title="Descargar Excel (.xls)">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5"/><path d="m9.5 12.5 4 5m0-5-4 5"/></svg>
            Excel
        </a>

        <a href="{{ route($rutaBase, array_merge($rutaParams, ['formato' => 'word'])) }}" data-export-url="{{ route($rutaBase, array_merge($rutaParams, ['formato' => 'word'])) }}"
           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold text-blue-600 transition hover:bg-blue-50" title="Descargar Word (.doc)">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5"/><path d="m8.5 12.5 1.2 5 1.3-3.5 1.3 3.5 1.2-5"/></svg>
            Word
        </a>
    </div>
</div>

@isset($fichas)
    <script>
        // Al elegir una ficha, los enlaces de exportación llevan ?ficha=ID.
        if (!window.__gevlaExportFicha) {
            window.__gevlaExportFicha = true;
            document.addEventListener('change', function (e) {
                var select = e.target.closest('[data-export-ficha]');
                if (!select) return;
                var grupo = select.closest('[data-export-grupo]');
                grupo.querySelectorAll('[data-export-url]').forEach(function (enlace) {
                    var base = enlace.getAttribute('data-export-url');
                    enlace.href = select.value ? base + '?ficha=' + encodeURIComponent(select.value) : base;
                });
            });
        }
    </script>
@endisset
