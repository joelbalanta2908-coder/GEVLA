{{-- Botones de exportación de reportes. Requiere: $rutaBase (nombre de ruta con {formato}). --}}
<div class="inline-flex items-center rounded-full border border-[#e6eadf] bg-white p-1 shadow-sm">
    <span class="px-3 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Exportar</span>

    <a href="{{ route($rutaBase, 'pdf') }}" target="_blank" rel="noopener"
       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-50" title="Abrir versión imprimible / Guardar como PDF">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5"/><path d="M9 13h1.5a1.5 1.5 0 0 1 0 3H9v-3Zm0 0v5"/></svg>
        PDF
    </a>

    <a href="{{ route($rutaBase, 'excel') }}"
       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold text-[#247200] transition hover:bg-[#39A900]/10" title="Descargar Excel (.xls)">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5"/><path d="m9.5 12.5 4 5m0-5-4 5"/></svg>
        Excel
    </a>

    <a href="{{ route($rutaBase, 'word') }}"
       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold text-blue-600 transition hover:bg-blue-50" title="Descargar Word (.doc)">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5"/><path d="m8.5 12.5 1.2 5 1.3-3.5 1.3 3.5 1.2-5"/></svg>
        Word
    </a>
</div>
