{{-- Bloque compartido para adjuntar fotos de evidencia a "pruebas aportadas".
     Requiere (opcional): $llamadoPruebas = modelo del llamado en edición, para
     mostrar y permitir quitar las fotos ya existentes. El formulario que lo
     incluye DEBE tener enctype="multipart/form-data". --}}
@php $llamadoPruebas = $llamadoPruebas ?? null; @endphp

@if($llamadoPruebas && count($llamadoPruebas->pruebas_fotos))
    <p class="mt-3 text-xs font-semibold text-gray-500">Fotos actuales</p>
    <div class="mt-2 flex flex-wrap gap-3">
        @foreach($llamadoPruebas->pruebas_fotos as $foto)
            <div class="w-24">
                <a href="{{ asset('storage/' . $foto) }}" target="_blank" rel="noopener">
                    <img src="{{ asset('storage/' . $foto) }}" alt="Prueba"
                         class="h-24 w-24 rounded-lg border border-gray-200 object-cover">
                </a>
                <label class="mt-1 flex items-center gap-1 text-xs font-medium text-red-600">
                    <input type="checkbox" name="pruebas_fotos_eliminar[]" value="{{ $foto }}"
                           class="h-3.5 w-3.5 rounded border-gray-300 text-red-600 focus:ring-red-500/30">
                    Quitar
                </label>
            </div>
        @endforeach
    </div>
@endif

<div class="mt-3">
    <label class="mb-1 block text-xs font-semibold text-gray-600">
        Adjuntar fotos <span class="font-normal text-gray-400">(JPG, PNG o WEBP · máx. 4 MB c/u · hasta 8)</span>
    </label>
    <input type="file" name="pruebas_fotos[]" multiple accept="image/jpeg,image/png,image/webp"
           class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#39A900]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#247200] hover:file:bg-[#39A900]/20">
    <div data-pruebas-previsualizacion class="mt-3 flex flex-wrap gap-3"></div>
</div>

<script>
    // Previsualización de las fotos seleccionadas antes de enviar el formulario.
    (function () {
        var input = document.currentScript.parentElement.querySelector('input[name="pruebas_fotos[]"]');
        var cont = document.currentScript.parentElement.querySelector('[data-pruebas-previsualizacion]');
        if (!input || !cont || input.__wired) return;
        input.__wired = true;
        input.addEventListener('change', function () {
            cont.innerHTML = '';
            Array.prototype.forEach.call(input.files, function (file) {
                if (!file.type.startsWith('image/')) return;
                var img = document.createElement('img');
                img.className = 'h-24 w-24 rounded-lg border border-gray-200 object-cover';
                img.src = URL.createObjectURL(file);
                img.onload = function () { URL.revokeObjectURL(img.src); };
                cont.appendChild(img);
            });
        });
    })();
</script>
