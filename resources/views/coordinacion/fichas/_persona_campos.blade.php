{{-- Campos personales compartidos para crear o editar un aprendiz o un instructor.
     En modo edición se pasa $persona (el Usuario) para precargar los valores y
     $esEdicion = true para ajustar los textos de ayuda. --}}
@php
    $persona = $persona ?? null;
    $esEdicion = $esEdicion ?? false;
@endphp
<div>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Nombres</label>
    <input type="text" name="nombres" value="{{ old('nombres', $persona->nombres ?? '') }}" required maxlength="100"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
<div>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Apellidos</label>
    <input type="text" name="apellidos" value="{{ old('apellidos', $persona->apellidos ?? '') }}" required maxlength="100"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
<div>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Tipo de documento</label>
    <select name="tipo_documento"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
        @foreach(['CC' => 'Cédula de ciudadanía', 'TI' => 'Tarjeta de identidad', 'CE' => 'Cédula de extranjería', 'PEP' => 'PEP'] as $valor => $etiqueta)
            <option value="{{ $valor }}" @selected(old('tipo_documento', $persona->tipo_documento ?? 'CC') === $valor)>{{ $etiqueta }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Número de documento</label>
    <input type="text" name="numero_documento" value="{{ old('numero_documento', $persona->numero_documento ?? '') }}" required maxlength="20"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
<div class="sm:col-span-2">
    <label class="mb-1 block text-xs font-semibold text-gray-600">Correo</label>
    <input type="email" name="correo" value="{{ old('correo', $persona->correo ?? '') }}" required maxlength="120"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
<div class="sm:col-span-2">
    <label class="mb-1 block text-xs font-semibold text-gray-600">Teléfono <span class="font-normal text-gray-400">(opcional)</span></label>
    <input type="text" name="telefono" value="{{ old('telefono', $persona->telefono ?? '') }}" maxlength="20"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
<div>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Contraseña <span class="font-normal text-gray-400">(opcional)</span></label>
    <input type="password" name="password" minlength="6" autocomplete="new-password" placeholder="{{ $esEdicion ? 'Déjala vacía para no cambiarla' : 'Mínimo 6 caracteres' }}"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
<div>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Confirmar contraseña</label>
    <input type="password" name="password_confirmation" minlength="6" autocomplete="new-password"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
@if($esEdicion)
    <p class="text-[11px] text-gray-400 sm:col-span-2">Si dejas la contraseña vacía, la persona conserva su contraseña actual.</p>
@else
    <p class="text-[11px] text-gray-400 sm:col-span-2">Si dejas la contraseña vacía, la inicial será el número de documento. Para iniciar sesión se usa el correo o el documento.</p>
@endif
