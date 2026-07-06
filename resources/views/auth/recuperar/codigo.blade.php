@extends('layouts.autenticacion')

@section('titulo', 'Verificar código')
@section('paso', '2')

@section('contenido')
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Revisa tu correo</h2>
        <p class="mt-1 text-sm text-slate-500">
            Enviamos un código de 6 dígitos a <span class="font-bold text-slate-700">{{ $correoEnmascarado }}</span>.
            Digítalo aquí para continuar.
        </p>
    </div>

    <form method="POST" action="{{ route('recuperar.verificar') }}" class="space-y-5">
        @csrf

        <div>
            <label for="codigo" class="mb-2 block text-sm font-semibold text-slate-700">Código de verificación</label>
            <input
                type="text"
                name="codigo"
                id="codigo"
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                placeholder="••••••"
                required
                autofocus
                autocomplete="one-time-code"
                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-center text-2xl font-extrabold tracking-[0.6em] text-slate-900 outline-none transition focus:border-[#39A900] focus:bg-white focus:ring-4 focus:ring-green-100"
            >
            <p class="mt-2 text-xs font-medium text-slate-400">El código vence en 10 minutos. Revisa también la carpeta de spam.</p>
        </div>

        <button type="submit" class="btn-primary flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold text-white transition focus:outline-none">
            Verificar código
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
        </button>
    </form>

    <div class="mt-5 flex items-center justify-between text-sm font-medium text-slate-500">
        <form method="POST" action="{{ route('recuperar.reenviar') }}">
            @csrf
            <button type="submit" class="font-bold text-[#39A900] transition hover:text-[#247200]">Reenviar código</button>
        </form>
        <a href="{{ route('recuperar.solicitud') }}" class="transition hover:text-slate-700">Usar otro correo</a>
    </div>

    <p class="mt-4 text-center text-sm font-medium text-slate-500">
        <a href="{{ route('login') }}" class="font-bold text-[#39A900] transition hover:text-[#247200]">← Volver a iniciar sesión</a>
    </p>

    <script>
        // Solo dígitos en el campo del código.
        document.getElementById('codigo').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });
    </script>
@endsection
