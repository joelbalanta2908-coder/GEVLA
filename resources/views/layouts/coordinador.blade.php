<!DOCTYPE html>
<html lang="es" class="h-full bg-[#f5f7f2]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GEVLA | @yield('titulo', 'Coordinador')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Work Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="h-full bg-[#f5f7f2] font-sans antialiased text-slate-900">

<div x-data="{ sidebarOpen: false, sidebarCollapsed: JSON.parse(localStorage.getItem('gevlaSidebarCollapsed') || 'false'), profileMenuOpen: false, profileModalOpen: false, logoutModalOpen: false }"
     x-effect="localStorage.setItem('gevlaSidebarCollapsed', sidebarCollapsed)"
     class="min-h-screen bg-[#f5f7f2] lg:flex lg:h-screen lg:overflow-hidden">

    {{-- Overlay para móvil --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside
        :class="(sidebarOpen ? 'translate-x-0' : '-translate-x-full') + ' ' + (sidebarCollapsed ? 'lg:w-20' : 'lg:w-60')"
        class="fixed inset-y-0 left-0 z-40 flex w-60 flex-col transform border-r border-white/10 bg-gradient-to-b from-[#1e6a00] via-[#2a7a00] to-[#4db100] text-white shadow-[0_30px_70px_rgba(25,80,0,0.32)] transition-all duration-200 lg:static lg:h-screen lg:translate-x-0">

        <div class="flex h-14 items-center justify-between gap-2 border-b border-white/10 px-4">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/90 shadow-sm ring-1 ring-white/30" :class="sidebarCollapsed && 'lg:hidden'">
                    <img src="https://oficinavirtualderadicacion.sena.edu.co/oficinavirtual/Resources/logoSenaNaranja.png" alt="Logosímbolo SENA" class="h-6 w-auto">
                </div>
                <div class="leading-tight" :class="sidebarCollapsed && 'lg:hidden'">
                    <p class="text-base font-extrabold tracking-tight text-white">GEVLA</p>
                    <p class="text-xs font-semibold text-white/80">SENA Regional</p>
                </div>
            </div>
            {{-- Botón desplegar/colapsar (escritorio) --}}
            <button type="button" @click="sidebarCollapsed = !sidebarCollapsed"
                    class="hidden h-8 w-8 shrink-0 items-center justify-center rounded-xl text-white/80 transition hover:bg-white/10 hover:text-white lg:flex"
                    :title="sidebarCollapsed ? 'Desplegar menú' : 'Colapsar menú'">
                <svg class="h-5 w-5 transition-transform duration-200" :class="sidebarCollapsed && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 6l-6 6 6 6"/>
                </svg>
            </button>
            {{-- Cerrar en móvil --}}
            <button type="button" @click="sidebarOpen = false"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-white/80 transition hover:bg-white/10 lg:hidden">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 6l12 12M18 6 6 18"/>
                </svg>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4" :class="sidebarCollapsed && 'lg:overflow-y-visible'">
            @php
                $navItems = [
                    ['label' => 'Dashboard', 'route' => 'coordinacion.dashboard', 'icon' => 'home'],
                    ['label' => 'Aprendices', 'route' => 'coordinacion.aprendices.index', 'icon' => 'users'],
                    ['label' => 'Fichas', 'route' => 'coordinacion.fichas.index', 'icon' => 'grid'],
                    ['label' => 'Llamados de atención', 'route' => 'coordinacion.llamados.index', 'icon' => 'bell'],
                    ['label' => 'Actas de coordinación', 'route' => 'coordinacion.actas.index', 'icon' => 'doc'],
                    ['label' => 'Procesos disciplinarios', 'route' => 'coordinacion.procesos.index', 'icon' => 'flow'],
                    ['label' => 'Reglamento', 'route' => 'reglamento.index', 'icon' => 'book'],
                ];
                $icons = [
                    'home' => 'M3 11.5 12 4l9 7.5M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9',
                    'bell' => 'M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9',
                    'doc'  => 'M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M9 12h6M9 16h6',
                    'flow' => 'M5 6h4v4H5V6Zm10 0h4v4h-4V6ZM5 16h4v4H5v-4Zm10 0h4v4h-4v-4M9 8h4m2 0h0M9 18h4m2-12v8m0 0v4',
                    'users' => 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm14 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75',
                    'grid' => 'M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z',
                    'book' => 'M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2zM22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z',
                ];
            @endphp

            <p class="px-2 pb-2 text-[11px] font-bold uppercase tracking-[0.22em] text-white/45" :class="sidebarCollapsed && 'lg:hidden'">Principal</p>
            @foreach($navItems as $item)
                @php $active = request()->routeIs($item['route'].'*'); @endphp
                <a href="{{ route($item['route']) }}"
                   :class="sidebarCollapsed && 'lg:justify-center'"
                   class="group relative mb-1 flex items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-semibold transition-all duration-200
                          {{ $active ? 'bg-white/18 text-white shadow-[0_10px_30px_rgba(0,0,0,0.08)] ring-1 ring-white/15' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $icons[$item['icon']] }}"/>
                    </svg>
                    <span :class="sidebarCollapsed && 'lg:hidden'">{{ $item['label'] }}</span>
                    {{-- Tooltip cuando está colapsado --}}
                    <span x-show="sidebarCollapsed" x-cloak
                          class="pointer-events-none absolute left-full top-1/2 z-50 ml-3 hidden -translate-y-1/2 whitespace-nowrap rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-semibold text-white opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100 lg:block">
                        {{ $item['label'] }}
                    </span>
                </a>
            @endforeach
        </nav>

        {{-- Cerrar sesión en la parte inferior del dashboard --}}
        <div class="border-t border-white/10 p-3">
            <form id="logout-form" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="button" @click="logoutModalOpen = true"
                        :class="sidebarCollapsed && 'lg:justify-center'"
                        class="group relative flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-semibold text-white/90 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 7V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-2"/>
                        <path d="M13 12h8m0 0-3-3m3 3-3 3"/>
                    </svg>
                    <span :class="sidebarCollapsed && 'lg:hidden'">Cerrar sesión</span>
                    <span x-show="sidebarCollapsed" x-cloak
                          class="pointer-events-none absolute left-full top-1/2 z-50 ml-3 hidden -translate-y-1/2 whitespace-nowrap rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-semibold text-white opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100 lg:block">
                        Cerrar sesión
                    </span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Contenido principal --}}
    <div class="flex min-h-screen flex-1 flex-col lg:h-screen lg:min-h-0">
        <header class="flex h-14 items-center justify-between border-b border-[#e3e7df] bg-white px-4 sm:px-5 lg:px-6 shadow-[0_8px_24px_rgba(0,0,0,0.03)]">
            @php $enDashboard = request()->routeIs('coordinacion.dashboard'); @endphp
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="text-slate-500 transition hover:text-[#39A900] lg:hidden">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/>
                    </svg>
                </button>
                @unless($enDashboard)
                    <a href="{{ route('coordinacion.dashboard') }}" title="Ir al panel principal"
                       class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900] ring-1 ring-[#39A900]/15 transition hover:bg-[#39A900]/20">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 11.5 12 4l9 7.5M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9"/>
                        </svg>
                    </a>
                @endunless
                <div class="flex items-center gap-3">
                    <div class="flex h-7 w-7 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900] ring-1 ring-[#39A900]/15">
                        <img src="https://oficinavirtualderadicacion.sena.edu.co/oficinavirtual/Resources/logoSenaNaranja.png" alt="SENA" class="h-4 w-auto">
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">Panel Coordinador — SENA</p>
                        <p class="text-xs font-medium text-slate-500">@yield('titulo', 'Panel del coordinador')</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
                <div class="hidden items-center gap-3 text-slate-600 md:flex">
                    <svg class="h-5 w-5 text-[#39A900]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 8v4l3 3" />
                        <circle cx="12" cy="12" r="9" />
                    </svg>
                    <div class="min-w-0 text-sm leading-tight">
                        <p class="truncate font-semibold text-slate-900" data-reloj>{{ now()->timezone('America/Bogota')->format('h:i:s A') }}</p>
                        <p class="truncate text-xs uppercase tracking-[0.22em] text-slate-400" data-fecha>Hora local · Bogotá</p>
                    </div>
                </div>

                <div class="relative" @keydown.escape.window="profileMenuOpen = false; profileModalOpen = false">
                    <button type="button"
                            @click="profileMenuOpen = !profileMenuOpen"
                            class="flex items-center gap-3 rounded-full bg-white px-3 py-1.5 shadow-[0_10px_30px_rgba(0,0,0,0.08)] transition duration-200 hover:shadow-[0_14px_40px_rgba(0,0,0,0.10)]">
                        @if(auth()->user()->fotoUrl())
                            <img src="{{ auth()->user()->fotoUrl() }}" alt="Foto de perfil" class="h-9 w-9 shrink-0 rounded-full object-cover ring-2 ring-[#39A900]/20">
                        @else
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#39A900]/10 text-sm font-black text-[#39A900] ring-2 ring-[#39A900]/20">
                                {{ auth()->user()->iniciales() }}
                            </div>
                        @endif
                        <div class="hidden text-left sm:block">
                            <p class="text-base font-bold text-slate-900">{{ auth()->user()->nombres ?? 'Coordinador' }}</p>
                            <p class="text-xs font-medium text-slate-500">{{ optional(auth()->user()->coordinacion)->cargo ?? 'Coordinación' }}</p>
                        </div>
                        <svg class="hidden h-4 w-4 text-slate-400 sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </button>

                    @include('layouts.profile-popover')
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto bg-[#f5f7f2] p-4 sm:p-5 lg:p-6">
            <div class="mx-auto max-w-7xl space-y-5">
                @if (session('success'))
                    <div class="flex items-center gap-3 rounded-2xl border border-[#39A900]/20 bg-[#39A900]/10 px-4 py-3 text-sm font-medium text-[#247200] shadow-sm">
                        <svg class="h-4 w-4 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 shadow-sm">
                        <svg class="h-4 w-4 shrink-0 mt-0.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('contenido')
            </div>
        </main>
    </div>
</div>

    <script>
        (function () {
            function actualizarReloj() {
                const ahora = new Date();
                const hora = ahora.toLocaleTimeString('es-CO', { timeZone: 'America/Bogota', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                const fecha = ahora.toLocaleDateString('es-CO', { timeZone: 'America/Bogota', weekday: 'long', day: '2-digit', month: 'long' });
                document.querySelectorAll('[data-reloj]').forEach(el => el.textContent = hora);
                document.querySelectorAll('[data-fecha]').forEach(el => el.textContent = fecha.charAt(0).toUpperCase() + fecha.slice(1) + ' · Bogotá');
                const h = parseInt(ahora.toLocaleString('en-US', { timeZone: 'America/Bogota', hour: '2-digit', hour12: false }), 10);
                const saludo = h < 12 ? 'Buenos días' : (h < 19 ? 'Buenas tardes' : 'Buenas noches');
                document.querySelectorAll('[data-saludo]').forEach(el => el.textContent = saludo);
            }
            actualizarReloj();
            setInterval(actualizarReloj, 1000);
        })();
    </script>
    @yield('scripts')
</body>
</html>
