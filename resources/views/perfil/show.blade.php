@extends($layout)

@section('titulo', 'Mi Perfil')

@section('contenido')
@php
    $dashboardRoute = match (true) {
        $usuario->tieneRol('Coordinador') => route('coordinacion.dashboard'),
        $usuario->tieneRol('Instructor') => route('instructor.dashboard'),
        default => route('aprendiz.dashboard'),
    };
@endphp
<div class="mx-auto max-w-6xl space-y-6" x-data="{ editando: {{ $errors->any() ? 'true' : 'false' }} }">
    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <section class="overflow-hidden rounded-[30px] border border-[#e6eadf] bg-white shadow-[0_12px_40px_rgba(0,0,0,0.06)]">
            <div class="border-b border-[#eef1e8] bg-[#fafbf8] px-8 py-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        @if($usuario->fotoUrl())
                            <img src="{{ $usuario->fotoUrl() }}" alt="Foto de perfil" class="h-20 w-20 shrink-0 rounded-3xl object-cover shadow-sm">
                        @else
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-3xl bg-[#e8f7e7] text-3xl font-extrabold text-[#39A900] shadow-sm">
                                {{ $usuario->iniciales() }}
                            </div>
                        @endif
                        <div>
                            <p class="text-sm uppercase tracking-[0.28em] text-slate-400">Perfil profesional</p>
                            <h1 class="text-3xl font-extrabold text-slate-900">{{ $usuario->nombres }} {{ $usuario->apellidos }}</h1>
                            <p class="mt-1 text-sm font-medium text-slate-500">{{ $usuario->rolPrincipal() ?? 'Usuario del sistema' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-2 rounded-full border border-[#d8e2cf] bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                            <svg class="h-4 w-4 text-[#39A900]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M9 12l2 2 4-4" />
                            </svg>
                            {{ ucfirst($usuario->estado_usuario ?? 'Desconocido') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-[#39A900]/10 px-3 py-2 text-sm font-semibold text-[#1f5a16]">
                            <svg class="h-4 w-4 text-[#39A900]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3l8 4v5c0 4.5-3 8-8 9-5-1-8-4.5-8-9V7l8-4z" />
                                <path d="M9 12l2 2 4-4" />
                            </svg>
                            {{ $usuario->rolPrincipal() ?? 'Sin rol principal' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="px-8 py-8">
                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="rounded-[24px] bg-[#f6faf4] p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Información de cuenta</p>
                        <dl class="mt-4 space-y-4 text-sm text-slate-700">
                            <div>
                                <dt class="font-semibold text-slate-900">Usuario</dt>
                                <dd>{{ $usuario->username ?? 'No registrado' }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-slate-900">Correo institucional</dt>
                                <dd>{{ $usuario->correo ?? 'No registrado' }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-slate-900">Creado el</dt>
                                <dd>{{ $usuario->fecha_creacion ? $usuario->fecha_creacion->format('d/m/Y') : 'No disponible' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-[24px] bg-[#f7f8fb] p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Actividad reciente</p>
                        <dl class="mt-4 space-y-4 text-sm text-slate-700">
                            <div>
                                <dt class="font-semibold text-slate-900">Último acceso</dt>
                                <dd>{{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->format('d/m/Y h:i A') : 'No disponible' }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-slate-900">Hora local</dt>
                                <dd>{{ now()->timezone('America/Bogota')->format('h:i A') }} · Bogotá</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-slate-900">Roles asignados</dt>
                                <dd class="flex flex-wrap gap-2 mt-2">
                                    @foreach($usuario->roles()->wherePivot('estado_asignacion', 'activa')->get() as $rol)
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-600">{{ $rol->nombre_rol }}</span>
                                    @endforeach
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="mt-8 rounded-[28px] bg-white p-6 shadow-[0_8px_30px_rgba(0,0,0,0.04)]">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Detalles adicionales</p>
                            <h2 class="mt-2 text-xl font-extrabold text-slate-900">Información profesional</h2>
                        </div>
                        <div class="text-sm text-slate-500">Vista completa del perfil</div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl border border-slate-100 bg-[#f9faf9] p-5">
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Coordinación</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ optional($usuario->coordinacion)->cargo ?? 'No aplica' }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ optional($usuario->coordinacion)->estado_coordinacion ?? 'No disponible' }}</p>
                        </div>
                        <div class="rounded-3xl border border-[#eef1e8] bg-[#f9faf9] p-5">
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Perfil</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $usuario->nombres ? $usuario->nombres . ' ' . $usuario->apellidos : 'Sin nombre' }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ ucfirst($usuario->estado_usuario ?? 'Estado desconocido') }}</p>
                        </div>
                        <div class="rounded-3xl border border-[#eef1e8] bg-[#f9faf9] p-5">
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Permisos rápidos</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">Acceso a coordinación</p>
                            <p class="mt-1 text-sm text-slate-500">Control de actas, llamados y procesos disciplinarios.</p>
                        </div>
                        <div class="rounded-3xl border border-[#eef1e8] bg-[#f9faf9] p-5">
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Acción</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">Mantén tus datos actualizados</p>
                            <p class="mt-1 text-sm text-slate-500">Asegúrate de que tu cuenta esté siempre lista para administrar el sistema.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Consejo de seguridad y acciones: apilados en móvil, en fila en pantallas grandes. --}}
            <div class="mt-6 flex flex-col gap-4 px-5 pb-6 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="w-full rounded-3xl bg-[#f4f9ee] p-5 text-sm text-slate-600 lg:max-w-md">
                    <p class="font-semibold text-slate-900">Consejo de seguridad</p>
                    <p class="mt-2 break-words">Usa una contraseña segura y actualiza tus datos si cambia tu correo institucional.</p>
                </div>
                <div class="flex w-full flex-col gap-3 sm:flex-row sm:flex-wrap lg:w-auto lg:shrink-0 lg:justify-end">
                    <button type="button" @click="editando = !editando"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#39A900] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#247200] shadow-sm sm:w-auto">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9" />
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" />
                        </svg>
                        <span x-text="editando ? 'Ocultar edición' : 'Editar perfil'">Editar perfil</span>
                    </button>
                    <a href="{{ $dashboardRoute }}" class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-[#d8e2cf] bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 shadow-sm sm:w-auto">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 11.5 12 4l9 7.5M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9" />
                        </svg>
                        Ir al Panel
                    </a>
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="overflow-hidden rounded-[28px] border border-[#e6eadf] bg-white p-6 shadow-[0_10px_30px_rgba(0,0,0,0.05)]">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Resumen rápido</p>
                <div class="mt-5 space-y-4">
                    <div class="flex items-center justify-between rounded-3xl bg-[#f8faf7] px-4 py-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Acceso</p>
                            <p class="mt-1 text-lg font-extrabold text-slate-900">{{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->locale('es')->diffForHumans() : 'Nunca' }}</p>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-[#39A900]/10 text-[#39A900]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 8v4l3 3" />
                                <circle cx="12" cy="12" r="9" />
                            </svg>
                        </span>
                    </div>
                    <div class="flex items-center justify-between rounded-3xl bg-[#f8f8fb] px-4 py-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Roles activos</p>
                            <p class="mt-1 text-lg font-extrabold text-slate-900">{{ $usuario->roles()->wherePivot('estado_asignacion','activa')->count() }}</p>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-[#00324d]/10 text-[#00324d]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </span>
                    </div>
                    <div class="flex items-center justify-between rounded-3xl bg-[#f9f7ef] px-4 py-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Coordinación</p>
                            <p class="mt-1 text-lg font-extrabold text-slate-900">{{ optional($usuario->coordinacion)->cargo ?? 'No asignada' }}</p>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-[#ff6a13]/10 text-[#ff6a13]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 7h18v13H3zM8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-[28px] border border-[#e6eadf] bg-[#f5faf4] p-6 shadow-[0_10px_30px_rgba(0,0,0,0.04)]">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Funciones de perfil</p>
                <ul class="mt-5 space-y-3 text-sm text-slate-700">
                    <li class="flex items-center gap-3 rounded-3xl border border-[#e6eadf] bg-white px-4 py-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-[#39A900]/10 text-[#39A900]"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"/><path d="M6 20v-1c0-2.21 1.79-4 4-4h4c2.21 0 4 1.79 4 4v1"/></svg></span>
                        <div>
                            <p class="font-semibold text-slate-900">Gestión de cuenta</p>
                            <p class="text-slate-500">Actualiza tus datos de contacto y acceso.</p>
                        </div>
                    </li>
                    <li class="flex items-center gap-3 rounded-3xl border border-[#e6eadf] bg-white px-4 py-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-[#00324d]/10 text-[#00324d]"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 11c1.657 0 3-.895 3-2s-1.343-2-3-2-3 .895-3 2 1.343 2 3 2z"/><path d="M5 20c0-2.21 2.686-4 7-4s7 1.79 7 4"/></svg></span>
                        <div>
                            <p class="font-semibold text-slate-900">Roles y permisos</p>
                            <p class="text-slate-500">Revisa los roles activos y accesos del sistema.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </aside>
    </div>

    {{-- ------------------------------------------------------------------
         Sección FIRMA: cada usuario (instructor, coordinador o aprendiz)
         registra aquí la imagen de su firma manuscrita. El sistema la usa
         automáticamente al generar los documentos de llamados de atención.
         La imagen es PRIVADA: solo el dueño puede verla.
    ------------------------------------------------------------------- --}}
    @php $tieneFirma = \App\Support\Firmas::tiene($usuario); @endphp
    <section class="mt-6 overflow-hidden rounded-[28px] border border-[#e6eadf] bg-white shadow-[0_10px_30px_rgba(0,0,0,0.05)]">
        <div class="border-b border-[#eef1e8] bg-[#fafbf8] px-6 py-5 sm:px-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Firma</p>
            <h2 class="mt-1 text-xl font-extrabold text-slate-900">Mi firma para documentos</h2>
            <p class="mt-1 text-sm text-slate-500">
                Esta firma se insertará automáticamente en los documentos de llamados de atención que te correspondan
                (como {{ implode(' / ', \App\Support\Roles::disponiblesPara($usuario)) ?: 'usuario' }}).
                Solo tú puedes ver esta imagen.
            </p>
        </div>

        <div class="flex flex-col gap-6 px-6 py-6 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
            {{-- Firma actual --}}
            <div class="flex items-center gap-5">
                @if($tieneFirma)
                    <div class="flex h-28 w-56 items-center justify-center overflow-hidden rounded-2xl border border-[#e6eadf] bg-[#f8faf6] p-2">
                        <img src="{{ route('perfil.firma.ver') }}?v={{ time() }}" alt="Mi firma" class="max-h-full max-w-full object-contain">
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">Firma registrada</p>
                        <p class="text-xs text-slate-500">Puedes reemplazarla subiendo una nueva o eliminarla.</p>
                    </div>
                @else
                    <div class="flex h-28 w-56 items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 text-center text-xs text-gray-400">
                        Aún no has registrado tu firma
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">Sin firma registrada</p>
                        <p class="text-xs text-slate-500">Sube una imagen de tu firma, preferiblemente PNG con fondo transparente.</p>
                    </div>
                @endif
            </div>

            {{-- Acciones: subir/reemplazar y eliminar --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="POST" action="{{ route('perfil.firma.guardar') }}" enctype="multipart/form-data" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    @csrf
                    <input type="file" name="firma" required accept="image/png,image/jpeg,image/webp"
                           class="block w-full max-w-xs text-sm text-slate-600 file:mr-3 file:rounded-full file:border-0 file:bg-[#39A900]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#247200] hover:file:bg-[#39A900]/20">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#39A900] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#247200]">
                        {{ $tieneFirma ? 'Reemplazar firma' : 'Registrar firma' }}
                    </button>
                </form>
                @if($tieneFirma)
                    <form method="POST" action="{{ route('perfil.firma.eliminar') }}"
                          data-confirm="¿Eliminar tu firma registrada? Los documentos nuevos no podrán firmarse hasta que registres otra."
                          data-confirm-title="Eliminar firma" data-confirm-btn="Sí, eliminar">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-full border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-bold text-red-600 transition hover:bg-red-100 sm:w-auto">
                            Eliminar
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <p class="border-t border-[#eef1e8] bg-[#fafbf8] px-6 py-3 text-xs text-slate-400 sm:px-8">
            PNG, JPG o WEBP · máximo 2 MB · idealmente la firma en tinta oscura sobre fondo transparente o blanco.
        </p>
    </section>

    {{-- ------------------------------------------------------------------
         Sección CAMBIAR CONTRASEÑA: disponible para todos los roles.
    ------------------------------------------------------------------- --}}
    <section class="mt-6 overflow-hidden rounded-[28px] border border-[#e6eadf] bg-white shadow-[0_10px_30px_rgba(0,0,0,0.05)]">
        <div class="border-b border-[#eef1e8] bg-[#fafbf8] px-6 py-5 sm:px-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Seguridad</p>
            <h2 class="mt-1 text-xl font-extrabold text-slate-900">Cambiar contraseña</h2>
            <p class="mt-1 text-sm text-slate-500">Necesitas tu contraseña actual para definir una nueva (mínimo 6 caracteres).</p>
        </div>

        <form method="POST" action="{{ route('perfil.password.cambiar') }}" class="grid grid-cols-1 gap-4 px-6 py-6 sm:grid-cols-3 sm:px-8">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Contraseña actual</label>
                <div class="relative">
                    <input type="password" name="password_actual" id="password_actual" required autocomplete="current-password"
                           class="w-full rounded-2xl border border-[#d9e4d4] bg-[#f8faf6] px-4 py-3 pr-10 text-sm text-slate-900 shadow-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/20">
                    <button type="button" data-ver-password="#password_actual" title="Ver contraseña"
                            class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-gray-400 transition hover:text-[#39A900]">
                        <svg data-ojo-abierto class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg data-ojo-cerrado class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>
                    </button>
                </div>
                @error('password_actual')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Nueva contraseña</label>
                <div class="relative">
                    <input type="password" name="password_nueva" id="password_nueva" required minlength="6" autocomplete="new-password"
                           class="w-full rounded-2xl border border-[#d9e4d4] bg-[#f8faf6] px-4 py-3 pr-10 text-sm text-slate-900 shadow-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/20">
                    <button type="button" data-ver-password="#password_nueva" title="Ver contraseña"
                            class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-gray-400 transition hover:text-[#39A900]">
                        <svg data-ojo-abierto class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg data-ojo-cerrado class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>
                    </button>
                </div>
                @error('password_nueva')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Confirmar nueva contraseña</label>
                <div class="relative">
                    <input type="password" name="password_nueva_confirmation" id="password_nueva_confirmation" required minlength="6" autocomplete="new-password"
                           class="w-full rounded-2xl border border-[#d9e4d4] bg-[#f8faf6] px-4 py-3 pr-10 text-sm text-slate-900 shadow-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/20">
                    <button type="button" data-ver-password="#password_nueva_confirmation" title="Ver contraseña"
                            class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-gray-400 transition hover:text-[#39A900]">
                        <svg data-ojo-abierto class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg data-ojo-cerrado class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>
                    </button>
                </div>
            </div>

            <div class="sm:col-span-3 flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#39A900] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#247200]">
                    Actualizar contraseña
                </button>
            </div>
        </form>
    </section>

    <script>
        // Alternar visibilidad de contraseñas (botón del ojo) en Mi Perfil.
        if (!window.__gevlaVerPassword) {
            window.__gevlaVerPassword = true;
            document.addEventListener('click', function (e) {
                var boton = e.target.closest('[data-ver-password]');
                if (!boton) return;
                var campo = document.querySelector(boton.getAttribute('data-ver-password'));
                if (!campo) return;
                var visible = campo.type === 'text';
                campo.type = visible ? 'password' : 'text';
                boton.querySelector('[data-ojo-abierto]').classList.toggle('hidden', !visible);
                boton.querySelector('[data-ojo-cerrado]').classList.toggle('hidden', visible);
            });
        }
    </script>

        {{-- Edición de perfil en modal --}}
        <div x-show="editando" x-cloak x-transition.opacity @keydown.escape.window="editando = false"
            class="fixed inset-0 z-[70] flex items-start justify-center pt-12 p-4">
        <div class="absolute inset-0 bg-black/50" @click="editando = false"></div>
        <section x-show="editando" x-transition.scale.origin.top
                 class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-[30px] border border-[#e6eadf] bg-white shadow-[0_30px_80px_rgba(0,0,0,0.25)]">
        <div class="flex items-start justify-between gap-4 border-b border-[#eef1e8] bg-[#fafbf8] px-8 py-6">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Detalles de la cuenta</p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-900">Editar mi perfil</h2>
                <p class="mt-1 text-sm text-slate-500">Actualiza tus datos personales y tu foto de perfil.</p>
            </div>
            <button type="button" @click="editando = false" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>

        <div class="px-8 py-8">
            <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data" class="space-y-8"
                  x-data="{ preview: '{{ $usuario->fotoUrl() }}' }">
                @csrf
                @method('PUT')

                {{-- Foto de perfil --}}
                <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-center">
                    <template x-if="preview">
                        <img :src="preview" alt="Vista previa" class="h-24 w-24 rounded-3xl object-cover shadow-sm">
                    </template>
                    <template x-if="!preview">
                        <div class="flex h-24 w-24 items-center justify-center rounded-3xl bg-[#e8f7e7] text-3xl font-extrabold text-[#39A900] shadow-sm">
                            {{ $usuario->iniciales() }}
                        </div>
                    </template>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Foto de perfil</label>
                        <input type="file" name="foto_perfil" accept="image/*"
                               @change="const f=$event.target.files[0]; if(f){ preview = URL.createObjectURL(f); }"
                               class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-[#39A900] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#247200]">
                        <p class="text-xs text-slate-400">JPG, PNG o WEBP · máximo 2 MB.</p>
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="space-y-2" data-campo>
                        <label class="block text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Nombres</label>
                        <input type="text" name="nombres" required value="{{ old('nombres', $usuario->nombres) }}"
                               minlength="2" maxlength="100" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]+" data-solo-letras
                               data-validar="minlen" data-min="2" data-msg-invalido="Mínimo 2 caracteres."
                               title="Solo letras y espacios, sin números ni caracteres especiales"
                               class="w-full rounded-2xl border border-[#d9e4d4] bg-[#f8faf6] px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/20">
                        <p data-ayuda class="text-xs font-medium text-gray-400">Mínimo 2 caracteres, solo letras.</p>
                    </div>
                    <div class="space-y-2" data-campo>
                        <label class="block text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Apellidos</label>
                        <input type="text" name="apellidos" required value="{{ old('apellidos', $usuario->apellidos) }}"
                               minlength="2" maxlength="100" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]+" data-solo-letras
                               data-validar="minlen" data-min="2" data-msg-invalido="Mínimo 2 caracteres."
                               title="Solo letras y espacios, sin números ni caracteres especiales"
                               class="w-full rounded-2xl border border-[#d9e4d4] bg-[#f8faf6] px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/20">
                        <p data-ayuda class="text-xs font-medium text-gray-400">Mínimo 2 caracteres, solo letras.</p>
                    </div>
                </div>

                <div class="space-y-2" data-campo>
                    <label class="block text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Correo electrónico</label>
                    <input type="email" name="correo" required value="{{ old('correo', $usuario->correo) }}"
                           data-validar="email" data-msg-invalido="Debe ser un correo válido (con @ y dominio)."
                           title="Debe ser un correo válido con @"
                           class="w-full rounded-2xl border border-[#d9e4d4] bg-[#f8faf6] px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/20">
                    <p data-ayuda class="text-xs font-medium text-gray-400">Debe ser un correo válido, ejemplo: nombre@dominio.com.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button type="button" @click="editando = false"
                            class="inline-flex items-center justify-center rounded-full border border-[#d8e2cf] bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-full bg-[#39A900] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#247200]">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
        </section>
    </div>
</div>
@endsection
