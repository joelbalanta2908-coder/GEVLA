{{-- Panel de carga masiva por Excel. Requiere:
       $urlPlantilla  ruta para descargar la plantilla
       $urlImportar   ruta POST para importar el archivo
       $tituloPanel   etiqueta del tipo (p. ej. «aprendices»)
     Muestra además el resultado de la última importación (éxito o el reporte
     detallado de errores por fila) desde la sesión. --}}

{{-- Reporte de la última importación --}}
@if(session('import_fallo'))
    <div class="rounded-xl border border-red-200 bg-red-50 p-4">
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-red-700">{{ session('import_fallo') }}</p>

                @if(session('import_errores'))
                    <div class="mt-3 max-h-72 overflow-y-auto rounded-lg border border-red-200 bg-white">
                        <table class="w-full text-left text-xs">
                            <thead class="sticky top-0 bg-red-100 font-bold uppercase text-red-700">
                                <tr>
                                    <th class="whitespace-nowrap px-3 py-2">Fila</th>
                                    <th class="whitespace-nowrap px-3 py-2">Campo</th>
                                    <th class="px-3 py-2">Descripción del problema</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-red-100 text-red-800">
                                @foreach(session('import_errores') as $e)
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-2 font-bold">{{ $e['fila'] }}</td>
                                        <td class="whitespace-nowrap px-3 py-2">{{ $e['campo'] }}</td>
                                        <td class="px-3 py-2">{{ $e['mensaje'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-2 text-xs text-red-600">{{ count(session('import_errores')) }} error(es) en total. Corrige el archivo y vuelve a importarlo.</p>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- Panel de carga masiva: plegable para no ocupar espacio, y con los
     controles en su propia fila para que sea legible en cualquier pantalla. --}}
<div x-data="{ abierto: {{ (session('import_fallo') || $errors->has('archivo')) ? 'true' : 'false' }} }"
     class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

    {{-- Cabecera (clic para desplegar) --}}
    <button type="button" @click="abierto = !abierto"
            class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-gray-50">
        <div class="flex min-w-0 items-center gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900]">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15V3m0 0 4 4m-4-4-4 4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-gray-900">Carga masiva de {{ $tituloPanel }} (Excel)</p>
                <p class="hidden truncate text-xs text-gray-500 sm:block">Descarga la plantilla, complétala y súbela. Todo o nada: con un solo error no se registra ningún usuario.</p>
            </div>
        </div>
        <svg class="h-5 w-5 shrink-0 text-gray-400 transition-transform duration-200" :class="abierto && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
    </button>

    {{-- Controles --}}
    <div x-show="abierto" x-cloak class="border-t border-gray-100 px-4 py-4">
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-[auto_1fr_auto] lg:items-center">
            {{-- 1. Descargar plantilla --}}
            <a href="{{ $urlPlantilla }}"
               class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-[#39A900] px-4 py-2.5 text-sm font-semibold text-[#39A900] transition hover:bg-[#39A900]/10">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                Descargar plantilla Excel
            </a>

            {{-- 2. Archivo + 3. Importar (mismo form, ancho completo) --}}
            <form method="POST" action="{{ $urlImportar }}" enctype="multipart/form-data"
                  class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_auto] sm:items-center lg:col-span-2">
                @csrf
                <input type="file" name="archivo" required accept=".xlsx,.xls"
                       class="block w-full rounded-lg border border-dashed border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#39A900]/10 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-[#247200] hover:file:bg-[#39A900]/20">
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-[#39A900] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2D8200]">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15V3m0 0 4 4m-4-4-4 4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                    Importar Excel
                </button>
            </form>
        </div>
        <p class="mt-2 text-xs text-gray-400 sm:hidden">Descarga la plantilla, complétala y súbela. Todo o nada: con un solo error no se registra ningún usuario.</p>
        @error('archivo')
            <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
