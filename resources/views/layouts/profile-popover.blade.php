@php
    $usuario = auth()->user();
@endphp

<div x-cloak
     x-show="profileMenuOpen"
     x-transition.origin.top.right
     @click.away="profileMenuOpen = false"
    class="absolute right-0 top-full z-50 mt-2.5 w-72 overflow-hidden rounded-3xl border border-[#dce3d5] bg-white shadow-[0_18px_40px_rgba(0,0,0,0.12)]">
    <div class="space-y-2 bg-[#f8faf7] px-4 py-4">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Mi cuenta</p>
        <div class="flex items-center gap-3 rounded-2xl bg-white px-4 py-3 shadow-sm">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#39A900]/10 text-sm font-black text-[#39A900]">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z" />
                    <path d="M6 20v-1c0-2.21 1.79-4 4-4h4c2.21 0 4 1.79 4 4v1" />
                </svg>
            </span>
            <div>
                <p class="text-sm font-bold text-slate-900">{{ $usuario->nombres }} {{ $usuario->apellidos }}</p>
                <p class="text-xs uppercase tracking-[0.22em] text-slate-400">{{ $usuario->rolPrincipal() ?? 'Usuario' }}</p>
            </div>
        </div>
    </div>

    <div class="space-y-1 px-2 py-2">
        <a href="{{ route('perfil.show') }}" class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-[#39A900]/10 text-[#39A900]">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z" />
                    <path d="M6 20v-1c0-2.21 1.79-4 4-4h4c2.21 0 4 1.79 4 4v1" />
                </svg>
            </span>
            <span>Ver mi perfil</span>
        </a>
        @if(!$usuario->tieneRol('Aprendiz') || $usuario->tieneRol('Coordinador') || $usuario->tieneRol('Instructor'))
            <a href="{{ route('perfil.edit') }}" class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-[#00324d]/10 text-[#00324d]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9" />
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" />
                    </svg>
                </span>
                <span>Editar perfil</span>
            </a>
        @endif
        <a href="{{ route('perfil.help') }}" class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-[#ffb703]/10 text-[#ffb703]">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 6v6l4 2" />
                    <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </span>
            <span>Soporte y ayuda</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="group flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 17h4" />
                        <path d="M14 7V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-2" />
                        <path d="M13 12h8m0 0-3-3m3 3-3 3" />
                    </svg>
                </span>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>
</div>

<div x-cloak
     x-show="profileModalOpen"
     x-transition.opacity
     @keydown.escape.window="profileModalOpen = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
    <div class="absolute inset-0 bg-black/45" @click="profileModalOpen = false"></div>

    <div class="relative w-full max-w-xl overflow-hidden rounded-[24px] border border-[#e5eadf] bg-white shadow-[0_30px_80px_rgba(0,0,0,0.18)]">
        <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5 sm:px-7">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#39A900]/10 text-base font-black text-[#39A900]">
                    {{ substr($usuario->nombres ?? 'U', 0, 1) }}{{ substr($usuario->apellidos ?? '', 0, 1) }}
                </div>
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900">Mi perfil</h3>
                    <p class="text-sm text-slate-500">{{ $usuario->rolPrincipal() ?? 'Usuario del sistema' }}</p>
                </div>
            </div>
            <button type="button" @click="profileModalOpen = false" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <div class="px-6 py-5 sm:px-7">
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-2xl bg-slate-50/80 p-3.5">
                    <dt class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Nombres</dt>
                    <dd class="mt-1 text-base font-semibold text-slate-900">{{ $usuario->nombres ?? 'No registrado' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50/80 p-3.5">
                    <dt class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Apellidos</dt>
                    <dd class="mt-1 text-base font-semibold text-slate-900">{{ $usuario->apellidos ?? 'No registrado' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50/80 p-3.5">
                    <dt class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Correo</dt>
                    <dd class="mt-1 break-all text-base font-semibold text-slate-900">{{ $usuario->correo ?? 'No registrado' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50/80 p-3.5">
                    <dt class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Usuario</dt>
                    <dd class="mt-1 text-base font-semibold text-slate-900">{{ $usuario->username ?? 'No registrado' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50/80 p-3.5">
                    <dt class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Estado</dt>
                    <dd class="mt-1 text-base font-semibold text-slate-900">{{ ucfirst($usuario->estado_usuario ?? 'No registrado') }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50/80 p-3.5">
                    <dt class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Último acceso</dt>
                    <dd class="mt-1 text-base font-semibold text-slate-900">{{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->format('d/m/Y h:i A') : 'No registrado' }}</dd>
                </div>
            </dl>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 sm:px-7">
            <a href="{{ route('perfil.show') }}" class="rounded-full border border-[#d8e2cf] px-5 py-2.5 text-sm font-bold text-[#39A900] transition hover:bg-[#f5f9ef]">
                Ver perfil completo
            </a>
            <button type="button" @click="profileModalOpen = false" class="rounded-full bg-[#39A900] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#247200]">
                Cerrar
            </button>
        </div>
    </div>
</div>