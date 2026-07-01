{{-- Botones de exportación de reportes. Requiere: $rutaBase (nombre de ruta con {formato}). --}}
<div class="inline-flex items-center rounded-full border border-[#e6eadf] bg-white p-1 shadow-sm">
    <span class="px-3 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Exportar</span>
    <a href="{{ route($rutaBase, 'pdf') }}" target="_blank" rel="noopener"
       class="rounded-full px-3 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-50" title="Abrir versión imprimible / Guardar como PDF">PDF</a>
    <a href="{{ route($rutaBase, 'excel') }}"
       class="rounded-full px-3 py-1.5 text-xs font-bold text-[#247200] transition hover:bg-[#39A900]/10" title="Descargar Excel (.xls)">Excel</a>
    <a href="{{ route($rutaBase, 'word') }}"
       class="rounded-full px-3 py-1.5 text-xs font-bold text-blue-600 transition hover:bg-blue-50" title="Descargar Word (.doc)">Word</a>
</div>
