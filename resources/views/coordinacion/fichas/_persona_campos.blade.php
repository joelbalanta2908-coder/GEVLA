{{-- Campos personales compartidos para crear o editar un aprendiz o un instructor.
     En modo edición se pasa $persona (el Usuario) para precargar los valores y
     $esEdicion = true para ajustar los textos de ayuda. --}}
@php
    $persona = $persona ?? null;
    $esEdicion = $esEdicion ?? false;

    // Rol "ancla" de la sección actual (Aprendiz en /aprendices, Instructor en
    // /docentes, Coordinador en /coordinadores): esa casilla siempre queda
    // marcada y bloqueada, porque su perfil lo gestiona el flujo propio de la
    // sección. Las otras dos casillas permiten sumar (o quitar, en edición)
    // roles adicionales compatibles.
    $rolAncla = $rolAncla ?? \App\Support\Roles::APRENDIZ;
    $rolesActuales = $persona ? \App\Support\Roles::disponiblesPara($persona) : [$rolAncla];
    $rolesSeleccionados = old('roles', $rolesActuales);

    $catalogoRoles = [
        \App\Support\Roles::APRENDIZ    => 'Aprendiz',
        \App\Support\Roles::INSTRUCTOR  => 'Instructor',
        \App\Support\Roles::COORDINADOR => 'Coordinador',
    ];
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
        @foreach(['CC' => 'Cédula de ciudadanía', 'TI' => 'Tarjeta de identidad', 'CE' => 'Cédula de extranjería', 'PEP' => 'PEP', 'PPT' => 'PPT (Permiso por Protección Temporal)', 'PA' => 'Pasaporte'] as $valor => $etiqueta)
            <option value="{{ $valor }}" @selected(old('tipo_documento', $persona->tipo_documento ?? 'CC') === $valor)>{{ $etiqueta }}</option>
        @endforeach
    </select>
</div>
<div data-campo>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Número de documento</label>
    <input type="text" name="numero_documento" value="{{ old('numero_documento', $persona->numero_documento ?? '') }}" required
           minlength="6" maxlength="10" pattern="[A-Za-z0-9]{6,10}" data-alfanumerico
           data-validar="alfanum" data-min="6" data-max="10" data-msg-invalido="Entre 6 y 10 caracteres, solo letras y números."
           title="Letras y números, entre 6 y 10 caracteres, sin espacios ni caracteres especiales"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
    <p data-ayuda class="mt-1 text-xs font-medium text-gray-400">Entre 6 y 10 caracteres, letras y números (ej. pasaporte AB1234567).</p>
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
    <div class="relative">
        <input type="password" name="password" id="password" minlength="6" autocomplete="new-password" placeholder="{{ $esEdicion ? 'Déjala vacía para no cambiarla' : 'Mínimo 6 caracteres' }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
        <button type="button" data-ver-password="#password" title="Ver contraseña"
                class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-gray-400 transition hover:text-[#39A900]">
            <svg data-ojo-abierto class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg data-ojo-cerrado class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>
        </button>
    </div>
</div>
<div data-campo>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Confirmar contraseña</label>
    <div class="relative">
        <input type="password" name="password_confirmation" id="password_confirmation" minlength="6" autocomplete="new-password"
               data-validar="match" data-target="#password" data-msg-invalido="Las contraseñas no coinciden."
               class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
        <button type="button" data-ver-password="#password_confirmation" title="Ver contraseña"
                class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-gray-400 transition hover:text-[#39A900]">
            <svg data-ojo-abierto class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg data-ojo-cerrado class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>
        </button>
    </div>
    <p data-ayuda class="mt-1 text-xs font-medium text-gray-400">Debe coincidir con la contraseña ingresada.</p>
</div>

<script>
    // Alternar visibilidad de las contraseñas (botón del ojo).
    if (!window.__gevlaVerPassword) {
        window.__gevlaVerPassword = true;
        document.addEventListener('click', function (e) {
            var boton = e.target.closest('[data-ver-password]');
            if (!boton) return;
            var campo = document.querySelector(boton.getAttribute('data-ver-password'));
            if (!campo) return;
            var visible = campo.type === 'text';
            campo.type = visible ? 'password' : 'text';
            boton.querySelector('[data-ojo-abierto]').classList.toggle('hidden', !visible);
            boton.querySelector('[data-ojo-cerrado]').classList.toggle('hidden', visible);
        });
    }
</script>
@if($esEdicion)
    <p class="text-[11px] text-gray-400 sm:col-span-2">Si dejas la contraseña vacía, la persona conserva su contraseña actual.</p>
@else
    <p class="text-[11px] text-gray-400 sm:col-span-2">Si dejas la contraseña vacía, la inicial será el número de documento. Para iniciar sesión se usa el correo o el documento.</p>
@endif

{{-- Roles: el rol ancla de esta sección va siempre marcado (con un input
     oculto que garantiza que se envíe aunque el checkbox se muestre
     deshabilitado). Un Coordinador nunca puede ser también Aprendiz; el
     script de abajo lo refleja en vivo, y el backend lo vuelve a validar.
     Con 'mostrarRoles' => false la sección se omite por completo (p. ej. el
     instructor da de alta aprendices sin gestionar roles). --}}
@if($mostrarRoles ?? true)
<div class="sm:col-span-2" data-roles-contenedor>
    <label class="mb-1 block text-xs font-semibold text-gray-600">Roles del usuario</label>
    <div class="flex flex-wrap gap-4 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5">
        @foreach($catalogoRoles as $valorRol => $etiquetaRol)
            @php $marcado = in_array($valorRol, $rolesSeleccionados, true); @endphp
            <label class="inline-flex items-center gap-1.5 text-sm text-gray-700">
                @if($valorRol === $rolAncla)
                    <input type="checkbox" checked disabled
                           class="h-4 w-4 rounded border-gray-300 text-[#39A900] focus:ring-[#39A900]/30">
                    <input type="hidden" name="roles[]" value="{{ $valorRol }}">
                @else
                    <input type="checkbox" name="roles[]" value="{{ $valorRol }}" data-rol-checkbox @checked($marcado)
                           class="h-4 w-4 rounded border-gray-300 text-[#39A900] focus:ring-[#39A900]/30">
                @endif
                {{ $etiquetaRol }}
                @if($valorRol === $rolAncla)
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">(fijo)</span>
                @endif
            </label>
        @endforeach
    </div>
    <p class="mt-1 text-xs font-medium text-gray-400">
        Un Coordinador no puede tener también el rol de Aprendiz. Las demás combinaciones sí son válidas
        (Instructor + Aprendiz, Coordinador + Instructor).
    </p>
    <p data-roles-error class="mt-1 hidden text-xs font-medium text-red-600">
        Un Coordinador no puede tener también el rol de Aprendiz.
    </p>
</div>

<script>
    // Refleja en vivo la única incompatibilidad de roles: Coordinador + Aprendiz.
    // El backend (Roles::mensajeIncompatibilidad) es quien realmente lo exige.
    (function () {
        var contenedor = document.querySelector('[data-roles-contenedor]');
        if (!contenedor || contenedor.__rolesWired) return;
        contenedor.__rolesWired = true;

        var error = contenedor.querySelector('[data-roles-error]');

        function marcado(rol) {
            var fijo = contenedor.querySelector('input[type="hidden"][value="' + rol + '"]');
            if (fijo) return true;
            var casilla = contenedor.querySelector('input[data-rol-checkbox][value="' + rol + '"]');
            return casilla ? casilla.checked : false;
        }

        function actualizar() {
            var conflicto = marcado('Coordinador') && marcado('Aprendiz');
            error.classList.toggle('hidden', !conflicto);

            contenedor.querySelectorAll('[data-rol-checkbox]').forEach(function (casilla) {
                var esParDelConflicto = casilla.value === 'Coordinador' || casilla.value === 'Aprendiz';
                casilla.disabled = conflicto && esParDelConflicto && !casilla.checked;
            });
        }

        contenedor.querySelectorAll('[data-rol-checkbox]').forEach(function (casilla) {
            casilla.addEventListener('change', actualizar);
        });
        actualizar();
    })();
</script>
@endif
