<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GEVLA | @yield('titulo', 'Aprendiz')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        * { font-family: 'Work Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="h-full font-sans antialiased text-gray-900">

<div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">

    {{-- Overlay para móvil --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-white border-r border-gray-200
               transform transition-transform duration-200 lg:static lg:translate-x-0 shadow-sm">

        <div class="flex h-16 items-center gap-3 border-b border-gray-100 px-6">
            <img src="https://oficinavirtualderadicacion.sena.edu.co/oficinavirtual/Resources/logoSenaNaranja.png" alt="Logosímbolo SENA" class="h-10 w-auto">
            <span class="text-2xl font-extrabold tracking-tight text-[#39A900]">GEVLA</span>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            @php
                $navItems = [
                    ['label' => 'Mi Dashboard', 'route' => 'aprendiz.dashboard', 'icon' => 'home'],
                    ['label' => 'Mis Llamados', 'route' => 'aprendiz.llamados.index', 'icon' => 'bell'],
                    ['label' => 'Mis Actas', 'route' => 'aprendiz.actas.index', 'icon' => 'doc'],
                    ['label' => 'Mis Procesos', 'route' => 'aprendiz.procesos.index', 'icon' => 'flow'],
                ];
                $icons = [
                    'home' => 'M3 11.5 12 4l9 7.5M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9',
                    'bell' => 'M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9',
                    'doc'  => 'M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M9 12h6M9 16h6',
                    'flow' => 'M5 6h4v4H5V6Zm10 0h4v4h-4V6ZM5 16h4v4H5v-4Zm10 0h4v4h-4v-4M9 8h4m2 0h0M9 18h4m2-12v8m0 0v4',
                ];
            @endphp
            @foreach($navItems as $item)
                @php $active = request()->routeIs($item['route'].'*'); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition
                          {{ $active ? 'bg-green-50 text-[#39A900] shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $icons[$item['icon']] }}"/>
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="border-t border-gray-100 bg-gray-50/50 p-4">
            <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-gray-500 shadow-sm border border-gray-200/60">
                <span class="h-1.5 w-1.5 rounded-full bg-[#39A900] animate-pulse"></span>
                Portal Aprendiz
            </span>
        </div>
    </aside>

    {{-- Contenido principal --}}
    <div class="flex min-h-screen flex-1 flex-col">
        <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8 shadow-sm">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="text-gray-500 lg:hidden hover:text-[#39A900] transition">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/>
                    </svg>
                </button>
                <h1 class="text-lg font-bold text-gray-900">@yield('titulo', 'Portal del Aprendiz')</h1>
            </div>

            <div class="flex items-center gap-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100 text-sm font-bold text-[#39A900] ring-2 ring-white">
                        {{ substr(auth()->user()->nombres ?? 'A', 0, 1) }}{{ substr(auth()->user()->apellidos ?? '', 0, 1) }}
                    </div>
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-bold text-gray-900">{{ auth()->user()->nombres ?? 'Aprendiz' }} {{ auth()->user()->apellidos ?? '' }}</p>
                        <p class="text-xs font-medium text-gray-500">Aprendiz SENA</p>
                    </div>
                </div>
                
                <div class="h-8 w-px bg-gray-200 hidden sm:block"></div>
                
                <div class="flex items-center gap-2">
                    <a href="{{ route('perfil.show') }}" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition" title="Mi Perfil">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-[#39A900] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#247200] shadow-sm hover:shadow">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 lg:p-8 bg-gray-50/50">
            <div class="mx-auto max-w-7xl space-y-6">
                @if (session('success'))
                    <div class="rounded-lg border border-[#39A900]/20 bg-[#39A900]/10 px-4 py-3 text-sm font-medium text-[#247200] shadow-sm flex items-center gap-3">
                        <svg class="h-5 w-5 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 shadow-sm flex items-start gap-3">
                        <svg class="h-5 w-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

</body>
</html>
