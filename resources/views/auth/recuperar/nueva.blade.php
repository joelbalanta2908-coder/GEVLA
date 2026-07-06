@extends('layouts.autenticacion')

@section('titulo', 'Nueva contraseña')
@section('paso', '3')

@section('contenido')
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Crea tu nueva contraseña</h2>
        <p class="mt-1 text-sm text-slate-500">
            El código fue verificado. Define tu nueva contraseña y confírmala para terminar.
        </p>
    </div>

    <form method="POST" action="{{ route('recuperar.guardar') }}" class="space-y-5">
        @csrf

        <div>
            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Nueva contraseña</label>
            <div class="group relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center text-slate-400 transition group-focus-within:text-[#39A900]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                </span>
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Mínimo 8 caracteres"
                    required
                    minlength="8"
                    autofocus
                    autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 py-3 pl-11 pr-12 text-sm text-slate-900 outline-none transition focus:border-[#39A900] focus:bg-white focus:ring-4 focus:ring-green-100"
                >
                <button type="button" data-alternar="password" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 transition hover:text-slate-700" aria-label="Mostrar u ocultar contraseña">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
            </div>
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirmar contraseña</label>
            <div class="group relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center text-slate-400 transition group-focus-within:text-[#39A900]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/><path d="M9 15l2 2 4-4"/></svg>
                </span>
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    placeholder="Repite la contraseña"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 py-3 pl-11 pr-12 text-sm text-slate-900 outline-none transition focus:border-[#39A900] focus:bg-white focus:ring-4 focus:ring-green-100"
                >
                <button type="button" data-alternar="password_confirmation" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 transition hover:text-slate-700" aria-label="Mostrar u ocultar contraseña">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
            </div>
            <p id="aviso-coincidencia" class="mt-2 hidden text-xs font-semibold text-red-600">Las contraseñas no coinciden.</p>
        </div>

        <button type="submit" class="btn-primary flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold text-white transition focus:outline-none">
            Guardar contraseña
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
        </button>
    </form>

    <p class="mt-5 text-center text-sm font-medium text-slate-500">
        <a href="{{ route('login') }}" class="font-bold text-[#39A900] transition hover:text-[#247200]">← Volver a iniciar sesión</a>
    </p>

    <script>
        // Mostrar / ocultar cada campo de contraseña.
        document.querySelectorAll('[data-alternar]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var campo = document.getElementById(boton.dataset.alternar);
                campo.type = campo.type === 'password' ? 'text' : 'password';
            });
        });

        // Aviso en vivo si la confirmación no coincide.
        var clave = document.getElementById('password');
        var confirmacion = document.getElementById('password_confirmation');
        var aviso = document.getElementById('aviso-coincidencia');

        function validarCoincidencia() {
            var distintas = confirmacion.value.length > 0 && clave.value !== confirmacion.value;
            aviso.classList.toggle('hidden', !distintas);
            confirmacion.setCustomValidity(distintas ? 'Las contraseñas no coinciden.' : '');
        }

        clave.addEventListener('input', validarCoincidencia);
        confirmacion.addEventListener('input', validarCoincidencia);
    </script>
@endsection
