@php
    /** @var \App\Models\Ficha|null $ficha */
    $esEdicion = $ficha !== null;
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="numero_ficha" class="mb-1 block text-sm font-semibold text-gray-700">Número de ficha</label>
        <input type="text" id="numero_ficha" name="numero_ficha" maxlength="20" required
               value="{{ old('numero_ficha', $ficha->numero_ficha ?? '') }}"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
    </div>

    <div class="sm:col-span-2">
        <label for="id_programa" class="mb-1 block text-sm font-semibold text-gray-700">Programa de formación</label>
        <select id="id_programa" name="id_programa" required
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            <option value="">Selecciona un programa...</option>
            @foreach($programas as $p)
                <option value="{{ $p->id_programa }}" @selected((string) old('id_programa', $ficha->id_programa ?? '') === (string) $p->id_programa)>
                    {{ $p->codigo_programa }} — {{ $p->nombre_programa }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="modalidad" class="mb-1 block text-sm font-semibold text-gray-700">Modalidad</label>
        <select id="modalidad" name="modalidad" required
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            @foreach($modalidades as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('modalidad', $ficha->modalidad ?? '') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="estado_ficha" class="mb-1 block text-sm font-semibold text-gray-700">Estado</label>
        <select id="estado_ficha" name="estado_ficha" required
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            @foreach($estados as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('estado_ficha', $ficha->estado_ficha ?? 'en_ejecucion') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="fecha_inicio" class="mb-1 block text-sm font-semibold text-gray-700">Fecha de inicio</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" required
               value="{{ old('fecha_inicio', optional($ficha->fecha_inicio ?? null)->format('Y-m-d')) }}"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
    </div>

    <div>
        <label for="fecha_fin_programada" class="mb-1 block text-sm font-semibold text-gray-700">Fecha de finalización <span class="font-normal text-gray-400">(opcional)</span></label>
        <input type="date" id="fecha_fin_programada" name="fecha_fin_programada"
               value="{{ old('fecha_fin_programada', optional($ficha->fecha_fin_programada ?? null)->format('Y-m-d')) }}"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
    </div>

    @if($esEdicion)
        {{-- El instructor líder se cambia desde la vista de detalle (acción dedicada con auditoría). --}}
        <input type="hidden" name="id_instructor_lider" value="{{ $ficha->id_instructor_lider }}">
    @else
        <div class="sm:col-span-2">
            <label for="id_instructor_lider" class="mb-1 block text-sm font-semibold text-gray-700">Instructor líder inicial</label>
            <select id="id_instructor_lider" name="id_instructor_lider" required
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                <option value="">Selecciona un instructor...</option>
                @foreach($instructores as $ins)
                    @php $iu = $ins->usuario; @endphp
                    <option value="{{ $ins->id_instructor }}" @selected((string) old('id_instructor_lider') === (string) $ins->id_instructor)>
                        {{ $iu ? trim($iu->nombres.' '.$iu->apellidos) : $ins->codigo_instructor }} ({{ $ins->codigo_instructor }})
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-400">Quedará también asociado como instructor de la ficha. Podrás cambiar el líder más adelante.</p>
        </div>
    @endif
</div>
