{{-- Muestra las pruebas aportadas de un llamado: la descripción de texto y/o
     las fotos de evidencia. Compatible con llamados antiguos (solo texto).
     Requiere: $llamado. --}}
@if($llamado->tiene_pruebas)
    <div>
        <h3 class="text-xs font-medium uppercase text-gray-400">Pruebas aportadas</h3>
        @if($llamado->pruebas_texto !== '')
            <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ $llamado->pruebas_texto }}</p>
        @endif
        @if(count($llamado->pruebas_fotos))
            <div class="mt-2 flex flex-wrap gap-3">
                @foreach($llamado->pruebas_fotos as $foto)
                    <a href="{{ asset('storage/' . $foto) }}" target="_blank" rel="noopener" title="Ver foto en tamaño completo">
                        <img src="{{ asset('storage/' . $foto) }}" alt="Prueba aportada"
                             class="h-28 w-28 rounded-lg border border-gray-200 object-cover transition hover:opacity-90">
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endif
