@php
    /** @var \App\Models\ProgramaFormacion|null $programa */
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label for="codigo_programa" class="mb-1 block text-sm font-semibold text-gray-700">Código del programa</label>
        <input type="text" id="codigo_programa" name="codigo_programa" maxlength="20" required
               value="{{ old('codigo_programa', $programa->codigo_programa ?? '') }}"
               class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
    </div>

    <div>
        <label for="duracion_meses" class="mb-1 block text-sm font-semibold text-gray-700">Duración (meses)</label>
        <input type="number" id="duracion_meses" name="duracion_meses" min="1" max="120" required
               value="{{ old('duracion_meses', $programa->duracion_meses ?? '') }}"
               class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
    </div>

    <div class="sm:col-span-2">
        <label for="nombre_programa" class="mb-1 block text-sm font-semibold text-gray-700">Nombre del programa</label>
        <input type="text" id="nombre_programa" name="nombre_programa" maxlength="150" required
               value="{{ old('nombre_programa', $programa->nombre_programa ?? '') }}"
               class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
    </div>

    <div class="sm:col-span-2">
        <label for="nivel" class="mb-1 block text-sm font-semibold text-gray-700">Nivel de formación</label>
        <select id="nivel" name="nivel" required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            @foreach($niveles as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('nivel', $programa->nivel ?? '') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>
</div>
