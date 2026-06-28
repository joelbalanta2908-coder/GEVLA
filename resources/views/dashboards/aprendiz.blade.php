<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEVLA | Aprendiz</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="mx-auto flex min-h-screen max-w-5xl flex-col justify-center px-6 py-10">
        <p class="text-sm font-bold uppercase tracking-widest text-[#39A900]">GEVLA</p>
        <h1 class="mt-3 text-4xl font-extrabold tracking-tight">Panel del aprendiz</h1>
        <p class="mt-4 max-w-2xl text-slate-600">Bienvenido al espacio de consulta y seguimiento de tu proceso formativo.</p>
        <form method="POST" action="{{ route('logout') }}" class="mt-8">
            @csrf
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">Cerrar sesion</button>
        </form>
    </main>
</body>
</html>
