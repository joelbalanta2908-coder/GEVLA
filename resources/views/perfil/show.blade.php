@extends($layout)

@section('titulo', 'Mi Perfil')

@section('contenido')
@php
    $dashboardRoute = match (true) {
        $usuario->tieneRol('Coordinador') => route('coordinacion.dashboard'),
        $usuario->tieneRol('Instructor') => route('instructor.dashboard'),
        default => route('aprendiz.dashboard'),
    };

    // Correos: el de inicio de sesión (personal) y el institucional. El aprendiz
    // tiene ambos en su perfil; para instructor/coordinador el correo de acceso
    // es también el institucional.
    $ap = $usuario->aprendiz;
    $correoAcceso = $usuario->correo;
    $correoInstitucional = $ap?->correo_institucional ?: $usuario->correo;
    $correoPersonal = $ap?->correo_personal ?: $usuario->correo;

    $rolesActivos = $usuario->roles()->wherePivot('estado_asignacion', 'activa')->get();
    $estadoCuenta = $usuario->estado_usuario ?? 'desconocido';
    $estadoColor = match ($estadoCuenta) {
        'activo'    => 'bg-[#39A900]/10 text-[#1f5a16]',
        'inactivo'  => 'bg-red-100 text-red-700',
        'bloqueado' => 'bg-slate-200 text-slate-700',
        default     => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="mx-auto max-w-5xl space-y-6" x-data="{ editando: {{ $errors->any() ? 'true' : 'false' }} }">

    {{-- ============================================================
         ENCABEZADO: identidad del usuario + acciones principales.
    ============================================================= --}}
    <section class="overflow-hidden rounded-[28px] border border-[#e6eadf] bg-white shadow-[0_12px_40px_rgba(0,0,0,0.06)]">
        <div class="h-20 bg-gradient-to-r from-[#39A900] to-[#2d8200] sm:h-24"></div>
        <div class="px-5 pb-6 sm:px-8">
            <div class="-mt-10 flex flex-col gap-4 sm:-mt-12 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-end">
                    {{-- Contenedor con overflow-hidden: garantiza que la foto se
                         recorte limpiamente al marco redondeado sin asomarse. --}}
                    <div class="h-24 w-24 shrink-0 overflow-hidden rounded-3xl border-4 border-white bg-[#e8f7e7] shadow-md sm:h-28 sm:w-28">
                        @if($usuario->fotoUrl())
                            <img src="{{ $usuario->fotoUrl() }}" alt="Foto de perfil" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-3xl font-extrabold text-[#39A900]">
                                {{ $usuario->iniciales() }}
                            </div>
                        @endif
                    </div>
                    <div class="pb-1">
                        <h1 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ $usuario->nombres }} {{ $usuario->apellidos }}</h1>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            {{-- Muestra el rol ACTIVO de la sesión (el que se elige en el
                                 selector de roles), no un rol fijo. --}}
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#39A900]/10 px-3 py-1 text-xs font-bold text-[#1f5a16]">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 4v5c0 4.5-3 8-8 9-5-1-8-4.5-8-9V7l8-4z"/><path d="M9 12l2 2 4-4"/></svg>
                                {{ ($rolActivo ?? session('rol_activo')) ?: ($usuario->rolPrincipal() ?? 'Usuario del sistema') }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $estadoColor }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ ucfirst($estadoCuenta) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                    <button type="button" @click="editando = true"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#39A900] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#247200] sm:w-auto">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg>
                        Editar perfil
                    </button>
                    <a href="{{ $dashboardRoute }}" class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-[#d8e2cf] bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 sm:w-auto">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11.5 12 4l9 7.5M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9"/></svg>
                        Ir al panel
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         DATOS: cuenta (con AMBOS correos) + actividad y roles.
    ============================================================= --}}
    <div class="grid gap-6 lg:grid-cols-2">

        {{-- Información de la cuenta --}}
        <section class="rounded-[24px] border border-[#e6eadf] bg-white p-6 shadow-[0_10px_30px_rgba(0,0,0,0.04)]">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="m3 8 9 5 9-5"/></svg>
                </span>
                <h2 class="text-sm font-bold uppercase tracking-[0.14em] text-slate-500">Información de cuenta</h2>
            </div>

            <dl class="mt-5 space-y-4 text-sm">
                <div class="flex items-start justify-between gap-3">
                    <dt class="shrink-0 font-semibold text-slate-500">Usuario</dt>
                    <dd class="text-right font-semibold text-slate-900">{{ $usuario->username ?? 'No registrado' }}</dd>
                </div>

                {{-- Correo de inicio de sesión (personal) --}}
                <div class="rounded-2xl bg-[#f6faf4] p-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-[#39A900]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="m10 17 5-5-5-5"/><path d="M15 12H3"/></svg>
                        <dt class="text-xs font-bold uppercase tracking-[0.12em] text-[#1f5a16]">Correo de inicio de sesión</dt>
                    </div>
                    <dd class="mt-1.5 break-all font-semibold text-slate-900">{{ $correoAcceso ?? 'No registrado' }}</dd>
                    <p class="mt-0.5 text-xs text-slate-500">Es el correo con el que ingresas al sistema.</p>
                </div>

                {{-- Correo institucional --}}
                <div class="rounded-2xl bg-[#eef4f8] p-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-[#00324d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/></svg>
                        <dt class="text-xs font-bold uppercase tracking-[0.12em] text-[#00324d]">Correo institucional</dt>
                    </div>
                    <dd class="mt-1.5 break-all font-semibold text-slate-900">{{ $correoInstitucional ?: 'No registrado' }}</dd>
                    @if($ap && $correoPersonal && $correoPersonal !== $correoInstitucional)
                        <p class="mt-1.5 text-xs text-slate-500">Correo personal: <span class="break-all font-medium text-slate-700">{{ $correoPersonal }}</span></p>
                    @endif
                </div>

                <div class="flex items-start justify-between gap-3">
                    <dt class="shrink-0 font-semibold text-slate-500">Documento</dt>
                    <dd class="text-right font-semibold text-slate-900">{{ $usuario->tipo_documento }} {{ $usuario->numero_documento }}</dd>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <dt class="shrink-0 font-semibold text-slate-500">Teléfono</dt>
                    <dd class="text-right font-semibold text-slate-900">{{ $usuario->telefono ?: 'No registrado' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <dt class="shrink-0 font-semibold text-slate-500">Creado el</dt>
                    <dd class="text-right font-semibold text-slate-900">{{ $usuario->fecha_creacion ? $usuario->fecha_creacion->format('d/m/Y') : 'No disponible' }}</dd>
                </div>
            </dl>
        </section>

        {{-- Actividad y roles --}}
        <section class="rounded-[24px] border border-[#e6eadf] bg-white p-6 shadow-[0_10px_30px_rgba(0,0,0,0.04)]">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-[#00324d]/10 text-[#00324d]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 3"/></svg>
                </span>
                <h2 class="text-sm font-bold uppercase tracking-[0.14em] text-slate-500">Actividad y roles</h2>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-[#f8faf7] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Último acceso</p>
                    <p class="mt-1 text-base font-extrabold text-slate-900">{{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->locale('es')->diffForHumans() : 'Nunca' }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">{{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->format('d/m/Y h:i A') : '—' }}</p>
                </div>
                <div class="rounded-2xl bg-[#f8f8fb] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Hora local</p>
                    <p class="mt-1 text-base font-extrabold text-slate-900">{{ now()->timezone('America/Bogota')->format('h:i A') }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">Bogotá, Colombia</p>
                </div>
            </div>

            <div class="mt-4 rounded-2xl border border-[#eef1e8] p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Roles asignados</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @forelse($rolesActivos as $rol)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#39A900]/10 px-3 py-1 text-xs font-bold text-[#1f5a16]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#39A900]"></span>{{ $rol->nombre_rol }}
                        </span>
                    @empty
                        <span class="text-sm text-slate-400">Sin roles activos</span>
                    @endforelse
                </div>
            </div>

            {{-- Detalle según el rol ACTIVO (datos reales del perfil). Si el
                 usuario tiene varios perfiles, se muestra el del rol activo;
                 si ese no existe, el primero disponible. --}}
            @php
                $rolAhora = $rolActivo ?? session('rol_activo');
                $detalle = match (true) {
                    $rolAhora === \App\Support\Roles::COORDINADOR && (bool) $usuario->coordinacion => 'coordinador',
                    $rolAhora === \App\Support\Roles::INSTRUCTOR && (bool) $usuario->instructor    => 'instructor',
                    $rolAhora === \App\Support\Roles::APRENDIZ && (bool) $ap                        => 'aprendiz',
                    (bool) $usuario->coordinacion => 'coordinador',
                    (bool) $usuario->instructor   => 'instructor',
                    (bool) $ap                    => 'aprendiz',
                    default                       => null,
                };
            @endphp
            @if($detalle === 'coordinador')
                <div class="mt-4 flex items-center justify-between rounded-2xl bg-[#f9f7ef] p-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Coordinación</p>
                        <p class="mt-1 font-bold text-slate-900">{{ $usuario->coordinacion->cargo ?? 'No asignado' }}</p>
                        <p class="text-xs text-slate-500">{{ ucfirst($usuario->coordinacion->estado_coordinacion ?? '—') }}</p>
                    </div>
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-[#ff6a13]/10 text-[#ff6a13]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18v13H3zM8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </span>
                </div>
            @elseif($detalle === 'instructor')
                <div class="mt-4 flex items-center justify-between rounded-2xl bg-[#f9f7ef] p-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Instructor</p>
                        <p class="mt-1 font-bold text-slate-900">{{ $usuario->instructor->codigo_instructor }}</p>
                        <p class="text-xs text-slate-500">{{ $usuario->instructor->area_formacion ?? 'Sin área' }}</p>
                    </div>
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-[#00324d]/10 text-[#00324d]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    </span>
                </div>
            @elseif($detalle === 'aprendiz')
                <div class="mt-4 flex items-center justify-between rounded-2xl bg-[#f9f7ef] p-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Aprendiz</p>
                        <p class="mt-1 font-bold text-slate-900">{{ ['en_formacion' => 'En formación', 'aplazado' => 'Aplazado', 'cancelado' => 'Cancelado', 'certificado' => 'Certificado'][$ap->estado_academico] ?? ucfirst(str_replace('_', ' ', (string) $ap->estado_academico)) }}</p>
                        <p class="text-xs text-slate-500">Estado académico</p>
                    </div>
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-[#39A900]/10 text-[#39A900]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    </span>
                </div>
            @endif
        </section>
    </div>

    {{-- Consejo de seguridad --}}
    <div class="flex items-start gap-3 rounded-2xl border border-[#e6eadf] bg-[#f4f9ee] p-4 text-sm text-slate-600">
        <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 4v5c0 4.5-3 8-8 9-5-1-8-4.5-8-9V7l8-4z"/></svg>
        </span>
        <p><span class="font-semibold text-slate-900">Consejo de seguridad:</span> usa una contraseña segura y mantén tus datos actualizados si cambia tu correo. No compartas tu contraseña con nadie.</p>
    </div>

    {{-- ------------------------------------------------------------------
         Sección FIRMA: cada usuario (instructor, coordinador o aprendiz)
         registra aquí la imagen de su firma manuscrita. El sistema la usa
         automáticamente al generar los documentos de llamados de atención.
         La imagen es PRIVADA: solo el dueño puede verla.
    ------------------------------------------------------------------- --}}
    @php $tieneFirma = \App\Support\Firmas::tiene($usuario); @endphp
    <section class="overflow-hidden rounded-[28px] border border-[#e6eadf] bg-white shadow-[0_10px_30px_rgba(0,0,0,0.05)]">
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
    <section class="overflow-hidden rounded-[28px] border border-[#e6eadf] bg-white shadow-[0_10px_30px_rgba(0,0,0,0.05)]">
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
        class="fixed inset-0 z-[70] flex items-start justify-center overflow-y-auto p-4 pt-12">
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
