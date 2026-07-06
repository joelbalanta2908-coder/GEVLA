<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEVLA | @yield('titulo', 'Recuperar contraseña')</title>
    <link rel="icon" type="image/png" href="https://oficinavirtualderadicacion.sena.edu.co/oficinavirtual/Resources/logoSenaNaranja.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Work Sans', ui-sans-serif, system-ui, sans-serif; }

        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(22px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .auth-carta { animation: fadeSlideUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards; }

        .fondo-institucional {
            background:
                radial-gradient(circle at 12% 18%, rgba(57, 169, 0, 0.28), transparent 42%),
                radial-gradient(circle at 88% 82%, rgba(57, 169, 0, 0.16), transparent 45%),
                linear-gradient(135deg, #00324D 0%, #01283d 55%, #001d2e 100%);
        }

        .btn-primary {
            background: #39A900;
            box-shadow: 0 10px 24px -10px rgba(57, 169, 0, 0.55);
        }
        .btn-primary:hover { background: #247200; }
        .btn-primary:focus { box-shadow: 0 0 0 4px rgba(57, 169, 0, 0.18); }
    </style>
</head>
<body class="fondo-institucional min-h-screen text-slate-900">
    <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-4 py-8">
        <section class="auth-carta relative w-full max-w-[440px] overflow-hidden rounded-2xl border border-white/70 bg-white/97 p-6 shadow-[0_24px_70px_rgba(0,0,0,0.32)] backdrop-blur sm:p-8">
            <div class="mb-6 flex items-center gap-3">
                <img src="https://oficinavirtualderadicacion.sena.edu.co/oficinavirtual/Resources/logoSenaNaranja.png" alt="Logosímbolo SENA" class="h-14 w-auto">
                <div class="leading-tight">
                    <p class="text-2xl font-extrabold tracking-tight text-[#39A900]">GEVLA</p>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">SENA</p>
                </div>
            </div>

            {{-- Indicador de pasos del flujo de recuperación --}}
            @hasSection('paso')
                @php $pasoActual = (int) trim($__env->yieldContent('paso')); @endphp
                <div class="mb-6 flex items-center gap-2" aria-label="Progreso de la recuperación">
                    @foreach(['Correo', 'Código', 'Nueva clave'] as $indice => $etiqueta)
                        @php $numero = $indice + 1; @endphp
                        <div class="flex flex-1 flex-col items-center gap-1.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-extrabold
                                {{ $numero < $pasoActual ? 'bg-[#39A900] text-white' : ($numero === $pasoActual ? 'bg-[#39A900]/15 text-[#247200] ring-2 ring-[#39A900]' : 'bg-slate-100 text-slate-400') }}">
                                @if($numero < $pasoActual)
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                                @else
                                    {{ $numero }}
                                @endif
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wide {{ $numero === $pasoActual ? 'text-[#247200]' : 'text-slate-400' }}">{{ $etiqueta }}</span>
                        </div>
                        @if($numero < 3)
                            <div class="mb-4 h-0.5 w-6 shrink-0 rounded {{ $numero < $pasoActual ? 'bg-[#39A900]' : 'bg-slate-200' }}"></div>
                        @endif
                    @endforeach
                </div>
            @endif

            @if (session('status'))
                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            @yield('contenido')

            <div class="mt-6 flex items-center justify-center gap-2 border-t border-slate-100 pt-5 text-xs font-medium text-slate-400">
                <svg class="h-4 w-4 text-[#39A900]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                Conexión segura · &copy; {{ date('Y') }} SENA — GEVLA
            </div>
        </section>
    </main>
</body>
</html>
