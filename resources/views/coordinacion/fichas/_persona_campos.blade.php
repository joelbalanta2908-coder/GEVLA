{{-- Campos personales compartidos para dar de alta un aprendiz o un instructor. --}}
<div>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Nombres</label>
    <input type="text" name="nombres" value="{{ old('nombres') }}" required maxlength="100"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
<div>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Apellidos</label>
    <input type="text" name="apellidos" value="{{ old('apellidos') }}" required maxlength="100"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
<div>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Tipo de documento</label>
    <select name="tipo_documento"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
        @foreach(['CC' => 'Cédula de ciudadanía', 'TI' => 'Tarjeta de identidad', 'CE' => 'Cédula de extranjería', 'PEP' => 'PEP'] as $valor => $etiqueta)
            <option value="{{ $valor }}" @selected(old('tipo_documento', 'CC') === $valor)>{{ $etiqueta }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Número de documento</label>
    <input type="text" name="numero_documento" value="{{ old('numero_documento') }}" required maxlength="20"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
<div class="sm:col-span-2">
    <label class="mb-1 block text-xs font-semibold text-gray-600">Correo</label>
    <input type="email" name="correo" value="{{ old('correo') }}" required maxlength="120"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
<div class="sm:col-span-2">
    <label class="mb-1 block text-xs font-semibold text-gray-600">Teléfono <span class="font-normal text-gray-400">(opcional)</span></label>
    <input type="text" name="telefono" value="{{ old('telefono') }}" maxlength="20"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
<div>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Contraseña <span class="font-normal text-gray-400">(opcional)</span></label>
    <input type="password" name="password" minlength="6" autocomplete="new-password" placeholder="Mínimo 6 caracteres"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
<div>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Confirmar contraseña</label>
    <input type="password" name="password_confirmation" minlength="6" autocomplete="new-password"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
</div>
<p class="text-[11px] text-gray-400 sm:col-span-2">Si dejas la contraseña vacía, la inicial será el número de documento. Para iniciar sesión se usa el correo o el documento.</p>
