{{-- Campos personales compartidos para crear o editar un aprendiz o un instructor.
     En modo edición se pasa $persona (el Usuario) para precargar los valores y
     $esEdicion = true para ajustar los textos de ayuda. --}}
@php
    $persona = $persona ?? null;
    $esEdicion = $esEdicion ?? false;
@endphp
<div data-campo>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Nombres</label>
    <input type="text" name="nombres" value="{{ old('nombres', $persona->nombres ?? '') }}" required
           minlength="2" maxlength="100" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]+" data-solo-letras
           data-validar="minlen" data-min="2" data-msg-invalido="Mínimo 2 caracteres."
           title="Solo letras y espacios, sin números ni caracteres especiales"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
    <p data-ayuda class="mt-1 text-xs font-medium text-gray-400">Mínimo 2 caracteres, solo letras.</p>
</div>
<div data-campo>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Apellidos</label>
    <input type="text" name="apellidos" value="{{ old('apellidos', $persona->apellidos ?? '') }}" required
           minlength="2" maxlength="100" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]+" data-solo-letras
           data-validar="minlen" data-min="2" data-msg-invalido="Mínimo 2 caracteres."
           title="Solo letras y espacios, sin números ni caracteres especiales"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
    <p data-ayuda class="mt-1 text-xs font-medium text-gray-400">Mínimo 2 caracteres, solo letras.</p>
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
<div data-campo>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Número de documento</label>
    <input type="text" name="numero_documento" value="{{ old('numero_documento', $persona->numero_documento ?? '') }}" required
           inputmode="numeric" minlength="8" maxlength="10" pattern="[0-9]{8,10}" data-solo-numeros
           data-validar="digits" data-min="8" data-max="10" data-msg-invalido="Entre 8 y 10 dígitos numéricos."
           title="Solo números, entre 8 y 10 dígitos"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
    <p data-ayuda class="mt-1 text-xs font-medium text-gray-400">Entre 8 y 10 dígitos, solo números.</p>
</div>
<div class="sm:col-span-2" data-campo>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Correo</label>
    <input type="email" name="correo" value="{{ old('correo', $persona->correo ?? '') }}" required maxlength="120"
           data-validar="email" data-msg-invalido="Debe ser un correo válido (con @ y dominio)."
           title="Debe ser un correo válido con @"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
    <p data-ayuda class="mt-1 text-xs font-medium text-gray-400">Debe ser un correo válido, ejemplo: nombre@dominio.com.</p>
</div>
<div class="sm:col-span-2" data-campo>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Teléfono <span class="font-normal text-gray-400">(opcional)</span></label>
    <input type="text" name="telefono" value="{{ old('telefono', $persona->telefono ?? '') }}"
           inputmode="numeric" minlength="10" maxlength="10" pattern="[0-9]{10}" data-solo-numeros
           data-validar="digits-exact" data-len="10" data-msg-invalido="Deben ser exactamente 10 dígitos numéricos."
           title="Solo números, exactamente 10 dígitos"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
    <p data-ayuda class="mt-1 text-xs font-medium text-gray-400">10 dígitos numéricos (opcional).</p>
</div>
<div>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Contraseña <span class="font-normal text-gray-400">(opcional)</span></label>
    <input type="password" name="password" id="password" minlength="6" autocomplete="new-password" placeholder="{{ $esEdicion ? 'Déjala vacía para no cambiarla' : 'Mínimo 6 caracteres' }}"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
<div data-campo>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Confirmar contraseña</label>
    <input type="password" name="password_confirmation" minlength="6" autocomplete="new-password"
           data-validar="match" data-target="#password" data-msg-invalido="Las contraseñas no coinciden."
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
    <p data-ayuda class="mt-1 text-xs font-medium text-gray-400">Debe coincidir con la contraseña ingresada.</p>
</div>
@if($esEdicion)
    <p class="text-[11px] text-gray-400 sm:col-span-2">Si dejas la contraseña vacía, la persona conserva su contraseña actual.</p>
@else
    <p class="text-[11px] text-gray-400 sm:col-span-2">Si dejas la contraseña vacía, la inicial será el número de documento. Para iniciar sesión se usa el correo o el documento.</p>
@endif
