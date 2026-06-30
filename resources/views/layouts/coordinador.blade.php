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

<div x-data="{ sidebarOpen: false, profileMenuOpen: false, profileModalOpen: false }" class="min-h-screen bg-[#f5f7f2] lg:flex">

    {{-- Overlay para móvil --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-40 flex w-56 flex-col transform border-r border-white/10 bg-gradient-to-b from-[#1e6a00] via-[#2a7a00] to-[#4db100] text-white shadow-[0_30px_70px_rgba(25,80,0,0.32)] transition-transform duration-200 lg:static lg:translate-x-0">

        <div class="flex h-14 items-center gap-3 border-b border-white/10 px-5 sm:px-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/90 shadow-sm ring-1 ring-white/30">
                <img src="https://oficinavirtualderadicacion.sena.edu.co/oficinavirtual/Resources/logoSenaNaranja.png" alt="Logosímbolo SENA" class="h-6 w-auto">
            </div>
            <div class="leading-tight">
                <p class="text-base font-extrabold tracking-tight text-white">Asistencias</p>
                <p class="text-xs font-semibold text-white/80">SENA Regional</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-5">
            @php
                $navItems = [
                    ['label' => 'Dashboard', 'route' => 'coordinacion.dashboard', 'icon' => 'home'],
                    ['label' => 'Llamados de atención', 'route' => 'coordinacion.llamados.index', 'icon' => 'bell'],
                    ['label' => 'Actas de coordinación', 'route' => 'coordinacion.actas.index', 'icon' => 'doc'],
                    ['label' => 'Procesos disciplinarios', 'route' => 'coordinacion.procesos.index', 'icon' => 'flow'],
                ];
                $icons = [
                    'home' => 'M3 11.5 12 4l9 7.5M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9',
                    'bell' => 'M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9',
                    'doc'  => 'M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M9 12h6M9 16h6',
                    'flow' => 'M5 6h4v4H5V6Zm10 0h4v4h-4V6ZM5 16h4v4H5v-4Zm10 0h4v4h-4v-4M9 8h4m2 0h0M9 18h4m2-12v8m0 0v4',
                ];
            @endphp

            <p class="px-2 pb-2 text-[11px] font-bold uppercase tracking-[0.22em] text-white/45">Principal</p>
            @foreach($navItems as $item)
                @php $active = request()->routeIs($item['route'].'*'); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-semibold transition-all duration-200
                          {{ $active ? 'bg-white/18 text-white shadow-[0_10px_30px_rgba(0,0,0,0.08)] ring-1 ring-white/15' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $icons[$item['icon']] }}"/>
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach

            <p class="px-2 pt-5 pb-2 text-[11px] font-bold uppercase tracking-[0.22em] text-white/45">Gestión</p>
            <div class="space-y-1">
                <a href="{{ route('coordinacion.llamados.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-semibold text-white/85 transition hover:bg-white/10 hover:text-white">
                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-white/10 text-[10px] font-black">L</span>
                    Llamados
                </a>
                <a href="{{ route('coordinacion.actas.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-semibold text-white/85 transition hover:bg-white/10 hover:text-white">
                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-white/10 text-[10px] font-black">A</span>
                    Actas
                </a>
                <a href="{{ route('coordinacion.procesos.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-semibold text-white/85 transition hover:bg-white/10 hover:text-white">
                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-white/10 text-[10px] font-black">P</span>
                    Procesos
                </a>
            </div>
        </nav>

        <div class="border-t border-white/10 p-4">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/12 px-3 py-1.5 text-[11px] font-medium text-white/80 ring-1 ring-white/10 backdrop-blur">
                <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                Plataforma institucional SENA
            </span>
        </div>
    </aside>

    {{-- Contenido principal --}}
    <div class="flex min-h-screen flex-1 flex-col">
        <header class="flex h-14 items-center justify-between border-b border-[#e3e7df] bg-white px-4 sm:px-5 lg:px-6 shadow-[0_8px_24px_rgba(0,0,0,0.03)]">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="text-slate-500 transition hover:text-[#39A900] lg:hidden">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="flex items-center gap-3">
                    <div class="flex h-7 w-7 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900] ring-1 ring-[#39A900]/15">
                        <img src="https://oficinavirtualderadicacion.sena.edu.co/oficinavirtual/Resources/logoSenaNaranja.png" alt="SENA" class="h-4 w-auto">
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">Panel Coordinador — SENA</p>
                        <p class="text-[11px] font-medium text-slate-500">@yield('titulo', 'Panel del coordinador')</p>
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
                        <p class="truncate font-semibold text-slate-900">{{ now()->timezone('America/Bogota')->format('h:i A') }}</p>
                        <p class="truncate text-[11px] uppercase tracking-[0.22em] text-slate-400">Hora local · Bogotá</p>
                    </div>
                </div>

                <div class="relative" @keydown.escape.window="profileMenuOpen = false; profileModalOpen = false">
                    <button type="button"
                            @click="profileMenuOpen = !profileMenuOpen"
                            class="flex items-center gap-3 rounded-full bg-white px-3 py-1.5 shadow-[0_10px_30px_rgba(0,0,0,0.08)] transition duration-200 hover:shadow-[0_14px_40px_rgba(0,0,0,0.10)]">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#39A900]/10 text-sm font-black text-[#39A900] ring-2 ring-[#39A900]/20">
                            {{ substr(auth()->user()->nombres ?? 'C', 0, 1) }}{{ substr(auth()->user()->apellidos ?? '', 0, 1) }}
                        </div>
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

        <main class="flex-1 bg-[#f5f7f2] p-4 sm:p-5 lg:p-6">
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

    @yield('scripts')
</body>
</html>
