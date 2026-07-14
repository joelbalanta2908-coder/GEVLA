{{-- Botones de exportación individual (PDF, Excel, Word).
     Recibe: $pdfUrl, $excelUrl, $wordUrl y opcionalmente $etiqueta. --}}
<div class="flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
    <span class="mr-1 text-xs font-bold uppercase tracking-wide text-gray-400">{{ $etiqueta ?? 'Exportar' }}:</span>
    <a href="{{ $pdfUrl }}" target="_blank" rel="noopener"
       class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-sm font-semibold text-red-600 transition hover:bg-red-100" title="Abrir versión imprimible (Guardar como PDF)">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5"/><path d="M9 13h6M9 17h6"/></svg>
        PDF
    </a>
    <a href="{{ $excelUrl }}"
       class="inline-flex items-center gap-1.5 rounded-lg bg-green-50 px-3 py-1.5 text-sm font-semibold text-green-700 transition hover:bg-green-100" title="Descargar en Excel">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5"/><path d="m9 12 6 6M15 12l-6 6"/></svg>
        Excel
    </a>
    <a href="{{ $wordUrl }}"
       class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-100" title="Descargar en Word">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5"/><path d="M9 13h6M9 17h4"/></svg>
        Word
    </a>
</div>
