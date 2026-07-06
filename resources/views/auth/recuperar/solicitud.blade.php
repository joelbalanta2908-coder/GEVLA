@extends('layouts.autenticacion')

@section('titulo', 'Recuperar contraseña')
@section('paso', '1')

@section('contenido')
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">¿Olvidaste tu contraseña?</h2>
        <p class="mt-1 text-sm text-slate-500">
            Ingresa el correo asociado a tu perfil y te enviaremos un código de 6 dígitos para restablecerla.
        </p>
    </div>

    <form method="POST" action="{{ route('recuperar.enviar') }}" class="space-y-5">
        @csrf

        <div>
            <label for="correo" class="mb-2 block text-sm font-semibold text-slate-700">Correo asociado a tu perfil</label>
            <div class="group relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center text-slate-400 transition group-focus-within:text-[#39A900]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v12H4zM4 7l8 6 8-6"/></svg>
                </span>
                <input
                    type="email"
                    name="correo"
                    id="correo"
                    value="{{ old('correo') }}"
                    placeholder="correo.personal@ejemplo.com"
                    required
                    autofocus
                    autocomplete="email"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition focus:border-[#39A900] focus:bg-white focus:ring-4 focus:ring-green-100"
                >
            </div>
        </div>

        <button type="submit" class="btn-primary flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold text-white transition focus:outline-none">
            Enviarme el código
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
    </form>

    <p class="mt-5 text-center text-sm font-medium text-slate-500">
        <a href="{{ route('login') }}" class="font-bold text-[#39A900] transition hover:text-[#247200]">← Volver a iniciar sesión</a>
    </p>
@endsection
