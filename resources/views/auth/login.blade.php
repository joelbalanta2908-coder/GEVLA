<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="GEVLA - Inicio de sesion para aprendices, instructores y coordinadores del SENA.">
    <title>GEVLA | Iniciar sesion</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        body {
            background:
                radial-gradient(circle at 12% 18%, rgba(57, 169, 0, 0.12), transparent 30%),
                linear-gradient(135deg, #f7faf7 0%, #eef4f1 48%, #f8fafc 100%);
        }
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(22px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-shell { animation: fadeSlideUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards; }
        .role-card input:checked + span {
            border-color: #39A900;
            background: #f0fdf4;
            box-shadow: 0 0 0 4px rgba(57, 169, 0, 0.12);
        }
        .role-card input:checked + span .role-dot { background: #39A900; border-color: #39A900; }
        .role-card input:checked + span .role-dot::after { opacity: 1; }
    </style>
</head>
<body class="min-h-screen px-4 py-8 text-slate-900">
    <main class="login-shell mx-auto grid min-h-[calc(100vh-4rem)] w-full max-w-6xl items-center gap-8 lg:grid-cols-[1.05fr_0.95fr]">
        <section class="hidden lg:block">
            <div class="max-w-xl">
                <div class="mb-8 inline-flex items-center gap-3 rounded-full border border-emerald-200 bg-white/80 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm">
                    <span class="h-2.5 w-2.5 rounded-full bg-[#39A900]"></span>
                    Plataforma institucional SENA
                </div>
                <h1 class="text-6xl font-extrabold tracking-tight text-slate-950">GEVLA</h1>
                <p class="mt-5 max-w-lg text-lg leading-8 text-slate-600">
                    Gestiona el acceso por rol al sistema de seguimiento disciplinario y formativo con una experiencia clara, segura y profesional.
                </p>
                <div class="mt-10 grid max-w-lg grid-cols-3 gap-3">
                    <div class="rounded-lg border border-white bg-white/75 p-4 shadow-sm">
                        <p class="text-2xl font-bold text-slate-950">01</p>
                        <p class="mt-1 text-sm font-medium text-slate-500">Aprendiz</p>
                    </div>
                    <div class="rounded-lg border border-white bg-white/75 p-4 shadow-sm">
                        <p class="text-2xl font-bold text-slate-950">02</p>
                        <p class="mt-1 text-sm font-medium text-slate-500">Instructor</p>
                    </div>
                    <div class="rounded-lg border border-white bg-white/75 p-4 shadow-sm">
                        <p class="text-2xl font-bold text-slate-950">03</p>
                        <p class="mt-1 text-sm font-medium text-slate-500">Coordinador</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto w-full max-w-[480px] rounded-2xl border border-white bg-white/95 p-6 shadow-[0_24px_70px_rgba(15,23,42,0.14)] sm:p-8">
            <div class="mb-7 flex items-center gap-4">
                <img
                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/Logo_del_SENA.svg/512px-Logo_del_SENA.svg.png"
                    alt="Logo SENA"
                    class="h-14 w-14 object-contain"
                >
                <div>
                    <p class="text-3xl font-extrabold tracking-tight text-[#39A900]">GEVLA</p>
                    <p class="text-sm font-medium text-slate-500">Inicio de sesion por rol</p>
                </div>
            </div>

            @if ($errors->has('login'))
                <div class="mb-5 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    <span class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-xs">!</span>
                    <p>{{ $errors->first('login') }}</p>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ url('/login') }}" id="login-form" class="space-y-5">
                @csrf

                <fieldset>
                    <legend class="mb-3 text-sm font-semibold text-slate-700">Selecciona tu rol</legend>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @php($selectedRole = old('role', 'Aprendiz'))
                        @foreach (['Aprendiz', 'Instructor', 'Coordinador'] as $role)
                            <label class="role-card cursor-pointer">
                                <input type="radio" name="role" value="{{ $role }}" class="sr-only" @checked($selectedRole === $role)>
                                <span class="flex h-full min-h-[86px] flex-col justify-between rounded-lg border border-slate-200 bg-white px-3 py-3 transition">
                                    <span class="role-dot relative h-4 w-4 rounded-full border border-slate-300 bg-white transition after:absolute after:left-1/2 after:top-1/2 after:h-1.5 after:w-1.5 after:-translate-x-1/2 after:-translate-y-1/2 after:rounded-full after:bg-white after:opacity-0"></span>
                                    <span class="mt-3 text-sm font-bold text-slate-800">{{ $role }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div>
                    <label for="username" class="mb-2 block text-sm font-semibold text-slate-700">Correo personal</label>
                    <input
                        type="email"
                        name="username"
                        id="username"
                        value="{{ old('username') }}"
                        placeholder="correo.personal@ejemplo.com"
                        required
                        autocomplete="email"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#39A900] focus:bg-white focus:ring-4 focus:ring-green-100"
                    >
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Contrasena</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Ingresa tu contrasena"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 pr-12 text-sm text-slate-900 outline-none transition focus:border-[#39A900] focus:bg-white focus:ring-4 focus:ring-green-100"
                        >
                        <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 transition hover:text-slate-700" aria-label="Mostrar u ocultar contrasena">
                            <svg id="icon-eye-open" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="icon-eye-closed" class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 3l18 18M10.58 10.58A2 2 0 0012 14a2 2 0 001.42-.59M9.88 5.09A10.15 10.15 0 0112 5c4.48 0 8.27 2.94 9.54 7a10.57 10.57 0 01-2.17 3.57M6.61 6.61A10.52 10.52 0 002.46 12C3.73 16.06 7.52 19 12 19c1.2 0 2.35-.21 3.41-.6"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-600">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300" style="accent-color: #39A900;">
                        Recordarme
                    </label>
                </div>

                <button type="submit" class="w-full rounded-lg bg-[#39A900] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-green-200 transition hover:bg-[#2f8f00] focus:outline-none focus:ring-4 focus:ring-green-100">
                    Ingresar a GEVLA
                </button>
            </form>

            <p class="mt-6 text-center text-xs font-medium text-slate-400">
                &copy; {{ date('Y') }} SENA - GEVLA
            </p>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('password');
            const iconOpen = document.getElementById('icon-eye-open');
            const iconClosed = document.getElementById('icon-eye-closed');

            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                iconOpen.classList.toggle('hidden', isPassword);
                iconClosed.classList.toggle('hidden', !isPassword);
            });
        });
    </script>
</body>
</html>
