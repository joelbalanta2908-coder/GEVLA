<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GEVLA | @yield('titulo', 'Coordinador')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
               transform transition-transform duration-200 lg:static lg:translate-x-0">

        <div class="flex h-16 items-center gap-2 border-b border-gray-200 px-6">
            <svg class="h-7 w-7 text-[#39A900]" viewBox="0 0 24 24" fill="none">
                <path d="M12 2C9 7 4 9 4 14a8 8 0 0 0 16 0c0-5-5-7-8-12Z" fill="currentColor"/>
            </svg>
            <span class="text-xl font-extrabold tracking-tight text-[#39A900]">GEVLA</span>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
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
            @foreach($navItems as $item)
                @php $active = request()->routeIs($item['route'].'*'); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                          {{ $active ? 'bg-green-50 text-[#39A900]' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $icons[$item['icon']] }}"/>
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="border-t border-gray-200 p-4">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-50 px-3 py-1 text-xs text-gray-500">
                <span class="h-1.5 w-1.5 rounded-full bg-[#39A900]"></span>
                Plataforma institucional SENA
            </span>
        </div>
    </aside>

    {{-- Contenido principal --}}
    <div class="flex min-h-screen flex-1 flex-col">
        <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="text-gray-500 lg:hidden">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/>
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-900">@yield('titulo', 'Panel del coordinador')</h1>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-medium text-gray-900">{{ auth()->user()->nombres ?? 'Coordinador' }} {{ auth()->user()->apellidos ?? '' }}</p>
                    <p class="text-xs text-gray-500">{{ auth()->user()->coordinacion->cargo ?? 'Coordinación' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <div class="mx-auto max-w-7xl space-y-6">
                @if (session('success'))
                    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
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
