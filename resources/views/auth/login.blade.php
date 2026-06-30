<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="GEVLA - Inicio de sesion para aprendices, instructores y coordinadores del SENA.">
    <title>GEVLA | Iniciar sesion</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --gevla-green: #39A900;
            --gevla-green-dark: #247200;
            --gevla-green-soft: rgba(57, 169, 0, 0.14);
            --gevla-navy: #00324D;
        }

        * { font-family: 'Work Sans', ui-sans-serif, system-ui, sans-serif; }

        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(22px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-shell { animation: fadeSlideUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards; }

        .role-card input:checked + span {
            border-color: var(--gevla-green);
            background: #f0fdf4;
            box-shadow: 0 0 0 4px rgba(57, 169, 0, 0.12);
        }

        .role-card input:checked + span .role-icon { color: var(--gevla-green); }

        /* ---------- Carrusel: overlay con paleta institucional, no slate/negro genérico ---------- */
        .carousel-slide {
            opacity: 0;
            transform: scale(1.04);
            transition: opacity 750ms ease, transform 1200ms ease;
        }

        .carousel-slide.is-active {
            opacity: 1;
            transform: scale(1);
        }

        /* Degradado direccional: oscuro donde vive el texto (izq/abajo), más abierto hacia
           donde está el sujeto de la foto (centro/derecha). Azul institucional #00324D en vez
           de slate-950, con el verde solo como acento, no como tinte dominante. */
        .carousel-overlay {
            background: linear-gradient(
                115deg,
                rgba(0, 50, 77, 0.55) 0%,
                rgba(0, 50, 77, 0.38) 30%,
                rgba(0, 50, 77, 0.18) 55%,
                rgba(57, 169, 0, 0.10) 100%
            );
        }

        .carousel-vignette {
            background: radial-gradient(circle at 16% 22%, rgba(57, 169, 0, 0.22), transparent 36%);
        }

        .carousel-dot.is-active {
            width: 2rem;
            background: var(--gevla-green);
        }

        /* Sombra de texto sutil: asegura legibilidad del párrafo aunque la foto tenga zonas claras */
        .copy-on-photo {
            text-shadow: 0 1px 12px rgba(0, 0, 0, 0.35);
        }

        .btn-primary {
            background: var(--gevla-green);
            box-shadow: 0 10px 24px -10px rgba(57, 169, 0, 0.55);
        }
        .btn-primary:hover { background: var(--gevla-green-dark); }
        .btn-primary:focus { box-shadow: 0 0 0 4px rgba(57, 169, 0, 0.18); }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-950 text-slate-900">
    <section class="fixed inset-0 z-0" aria-label="Carrusel de fondo GEVLA">
        <div class="carousel-slide is-active absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1800&q=85');" data-carousel-slide></div>
        <div class="carousel-slide absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1800&q=85');" data-carousel-slide></div>
        <div class="carousel-slide absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1800&q=85');" data-carousel-slide></div>
        <div class="carousel-overlay absolute inset-0"></div>
        <div class="carousel-vignette absolute inset-0"></div>
    </section>

    <main class="login-shell relative z-10 mx-auto grid min-h-screen w-full max-w-6xl items-center gap-8 px-4 py-8 lg:grid-cols-[1fr_480px]">
        <section class="hidden max-w-xl text-white lg:block">
            <div class="mb-8 inline-flex items-center gap-3 rounded-full border border-white/20 bg-white/15 px-4 py-2 text-sm font-bold backdrop-blur">
                <span class="h-2.5 w-2.5 rounded-full bg-[#39A900]"></span>
                Plataforma institucional SENA
            </div>
            <h1 class="copy-on-photo text-6xl font-extrabold tracking-tight">GEVLA</h1>
            <p class="copy-on-photo mt-5 max-w-lg text-lg leading-8 text-white/90">
                Acceso seguro por rol para aprendices, instructores y coordinadores del sistema de seguimiento disciplinario y formativo.
            </p>

            <div class="mt-10 flex items-center gap-3" aria-label="Indicadores del carrusel">
                <button type="button" class="carousel-dot is-active h-2.5 w-2.5 rounded-full bg-white/70 transition-all" aria-label="Ver imagen 1" data-carousel-dot="0"></button>
                <button type="button" class="carousel-dot h-2.5 w-2.5 rounded-full bg-white/70 transition-all" aria-label="Ver imagen 2" data-carousel-dot="1"></button>
                <button type="button" class="carousel-dot h-2.5 w-2.5 rounded-full bg-white/70 transition-all" aria-label="Ver imagen 3" data-carousel-dot="2"></button>
            </div>
        </section>

        <section class="mx-auto w-full max-w-[480px] rounded-lg border border-white/70 bg-white/97 p-6 shadow-[0_24px_70px_rgba(0,0,0,0.32)] backdrop-blur sm:p-8">
            <div class="mb-7 flex items-center gap-4">
                <img src="https://oficinavirtualderadicacion.sena.edu.co/oficinavirtual/Resources/logoSenaNaranja.png" alt="Logosímbolo SENA" class="h-14 w-auto sm:h-16">
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
                <div id="status-flash" class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 transition-opacity duration-500">
                    {{ session('status') }}
                </div>
                <script>
                    setTimeout(function () {
                        var el = document.getElementById('status-flash');
                        if (!el) return;
                        el.style.opacity = '0';
                        setTimeout(function () { el.remove(); }, 500);
                    }, 8000);
                </script>
            @endif

            <form method="POST" action="{{ url('/login') }}" id="login-form" class="space-y-5">
                @csrf

                <fieldset>
                    <legend class="mb-3 text-sm font-semibold text-slate-700">Selecciona tu rol</legend>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @php($selectedRole = old('role', 'Aprendiz'))
                        @php($roleIcons = ['Aprendiz' => 'ri-graduation-cap-line', 'Instructor' => 'ri-presentation-line', 'Coordinador' => 'ri-shield-user-line'])
                        @foreach (['Aprendiz', 'Instructor', 'Coordinador'] as $role)
                            <label class="role-card cursor-pointer">
                                <input type="radio" name="role" value="{{ $role }}" class="sr-only" @checked($selectedRole === $role)>
                                <span class="flex h-full min-h-[70px] flex-col items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-2 text-center transition">
                                    <i class="{{ $roleIcons[$role] }} role-icon text-3xl text-slate-400 transition"></i>
                                    <span class="text-sm font-bold text-slate-800">{{ $role }}</span>
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

                <button type="submit" class="btn-primary w-full rounded-lg px-4 py-3 text-sm font-bold text-white transition focus:outline-none">
                    Ingresar a GEVLA
                </button>
            </form>

            <p class="mt-6 text-center text-xs font-medium text-slate-400">
                &copy; {{ date('Y') }} SENA - GEVLA
            </p>
        </section>
    </main>

    <div class="pointer-events-none fixed bottom-6 left-1/2 z-20 flex -translate-x-1/2 items-center gap-3 lg:hidden" aria-hidden="true">
        <span class="carousel-dot is-active h-2.5 w-2.5 rounded-full bg-white/70 transition-all" data-carousel-dot-mobile="0"></span>
        <span class="carousel-dot h-2.5 w-2.5 rounded-full bg-white/70 transition-all" data-carousel-dot-mobile="1"></span>
        <span class="carousel-dot h-2.5 w-2.5 rounded-full bg-white/70 transition-all" data-carousel-dot-mobile="2"></span>
    </div>

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

            const slides = Array.from(document.querySelectorAll('[data-carousel-slide]'));
            const dots = Array.from(document.querySelectorAll('[data-carousel-dot], [data-carousel-dot-mobile]'));
            let activeIndex = 0;
            let intervalId;

            function showSlide(index) {
                activeIndex = (index + slides.length) % slides.length;

                slides.forEach((slide, slideIndex) => {
                    slide.classList.toggle('is-active', slideIndex === activeIndex);
                });

                dots.forEach((dot) => {
                    const dotIndex = Number(dot.dataset.carouselDot ?? dot.dataset.carouselDotMobile);
                    dot.classList.toggle('is-active', dotIndex === activeIndex);
                });
            }

            function startCarousel() {
                intervalId = window.setInterval(() => showSlide(activeIndex + 1), 5000);
            }

            function restartCarousel() {
                window.clearInterval(intervalId);
                startCarousel();
            }

            dots.forEach((dot) => {
                if (dot.dataset.carouselDot === undefined) {
                    return;
                }

                dot.addEventListener('click', function () {
                    showSlide(Number(dot.dataset.carouselDot));
                    restartCarousel();
                });
            });

            if (slides.length > 1) {
                startCarousel();
            }
        });
    </script>
</body>
</html>