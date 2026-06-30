@extends($layout)

@section('titulo', 'Editar Mi Perfil')

@section('contenido')
<div class="mx-auto max-w-6xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.28em] text-[#39A900]">Perfil de usuario</p>
            <h1 class="text-3xl font-extrabold text-slate-900">Editar Datos Personales</h1>
            <p class="mt-2 text-sm text-slate-500">Actualiza tus datos principales para mantener tu perfil institucional al día.</p>
        </div>
        <a href="{{ route('perfil.show') }}" class="inline-flex items-center gap-2 rounded-full border border-[#d8e2cf] bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 19l-7-7 7-7" />
            </svg>
            Volver a mi perfil
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">
        <section class="overflow-hidden rounded-[30px] border border-[#e6eadf] bg-white shadow-[0_14px_40px_rgba(0,0,0,0.06)]">
            <div class="border-b border-[#eef1e8] bg-[#fafbf8] px-8 py-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Detalles de la cuenta</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-slate-900">Datos personales</h2>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-[#39A900]/10 px-4 py-2 text-sm font-semibold text-[#1f5a16]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 11c1.657 0 3-.895 3-2s-1.343-2-3-2-3 .895-3 2 1.343 2 3 2z" />
                            <path d="M4 20c0-2.21 2.686-4 7-4s7 1.79 7 4" />
                        </svg>
                        {{ ucfirst($usuario->estado_usuario ?? 'Sin estado') }}
                    </div>
                </div>
            </div>

            <div class="px-8 py-8">
                <form method="POST" action="{{ route('perfil.update') }}" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="space-y-3">
                            <label class="block text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Nombres</label>
                            <input type="text" name="nombres" required value="{{ old('nombres', $usuario->nombres) }}"
                                   class="w-full rounded-3xl border border-[#d9e4d4] bg-[#f8faf6] px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/20">
                        </div>
                        <div class="space-y-3">
                            <label class="block text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Apellidos</label>
                            <input type="text" name="apellidos" required value="{{ old('apellidos', $usuario->apellidos) }}"
                                   class="w-full rounded-3xl border border-[#d9e4d4] bg-[#f8faf6] px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/20">
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Correo electrónico</label>
                        <input type="email" name="correo" required value="{{ old('correo', $usuario->correo) }}"
                               class="w-full rounded-3xl border border-[#d9e4d4] bg-[#f8faf6] px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/20">
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="rounded-[26px] border border-[#eef1e8] bg-[#f7f8fb] p-5">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Último acceso</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->format('d/m/Y h:i A') : 'No disponible' }}</p>
                        </div>
                        <div class="rounded-[26px] border border-[#eef1e8] bg-[#f7f8fb] p-5">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Roles activos</p>
                            <p class="mt-2 flex flex-wrap gap-2 text-sm text-slate-900">
                                @foreach($usuario->roles()->wherePivot('estado_asignacion', 'activa')->get() as $rol)
                                    <span class="rounded-full bg-[#e8f6ea] px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#2b6a2f]">{{ $rol->nombre_rol }}</span>
                                @endforeach
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('perfil.show') }}" class="inline-flex items-center justify-center rounded-full border border-[#d8e2cf] bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#39A900] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#247200] shadow-sm">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="overflow-hidden rounded-[28px] border border-[#e6eadf] bg-[#fcfff7] p-6 shadow-[0_10px_30px_rgba(0,0,0,0.05)]">
                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Resumen rápido</p>
                <div class="mt-5 space-y-4">
                    <div class="flex items-center justify-between rounded-3xl bg-white p-4 shadow-sm">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Correo</p>
                            <p class="mt-2 font-semibold text-slate-900">{{ $usuario->correo }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between rounded-3xl bg-white p-4 shadow-sm">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Usuario</p>
                            <p class="mt-2 font-semibold text-slate-900">{{ $usuario->username }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between rounded-3xl bg-white p-4 shadow-sm">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Coordinación</p>
                            <p class="mt-2 font-semibold text-slate-900">{{ optional($usuario->coordinacion)->cargo ?? 'No asignada' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-[28px] border border-[#e6eadf] bg-white p-6 shadow-[0_10px_30px_rgba(0,0,0,0.05)]">
                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Sugerencias</p>
                <ul class="mt-5 space-y-3 text-sm text-slate-600">
                    <li class="flex items-start gap-3 rounded-3xl border border-[#eef1e8] bg-[#f8faf9] p-4">
                        <span class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-[#39A900]/10 text-[#39A900]"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg></span>
                        <div>
                            <p class="font-semibold text-slate-900">Mantén tu información actualizada</p>
                            <p class="text-slate-600">Es importante que tu correo institucional y nombres sean correctos.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3 rounded-3xl border border-[#eef1e8] bg-[#f8faf9] p-4">
                        <span class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-[#00324d]/10 text-[#00324d]"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg></span>
                        <div>
                            <p class="font-semibold text-slate-900">Controla tu acceso</p>
                            <p class="text-slate-600">Revisa tu último inicio de sesión y reporta anomalías.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </aside>
    </div>
</div>
@endsection
