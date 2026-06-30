@extends($layout)

@section('titulo', 'Ayuda de Perfil')

@section('contenido')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="overflow-hidden rounded-[28px] border border-[#e6eadf] bg-white shadow-[0_12px_36px_rgba(0,0,0,0.08)]">
        <div class="border-b border-[#eef1e8] bg-[#fafbf8] px-8 py-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Centro de ayuda</p>
                    <h1 class="text-3xl font-extrabold text-slate-900">¿Necesitas ayuda con tu perfil?</h1>
                </div>
                <div class="inline-flex items-center gap-3 rounded-full bg-[#39A900]/10 px-4 py-2 text-sm font-semibold text-[#1f5a16]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 6v6l4 2" />
                        <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Soporte activo
                </div>
            </div>
        </div>

        <div class="px-8 py-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-[26px] border border-[#e6eadf] bg-[#f6faf4] p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Atención</p>
                    <h2 class="mt-3 text-xl font-extrabold text-slate-900">Coordinación</h2>
                    <p class="mt-2 text-sm text-slate-600">Si necesitas ayuda con datos institucionales o acceso al panel de coordinación, revisa tu rol y permisos.</p>
                </div>
                <div class="rounded-[26px] border border-[#e6eadf] bg-[#eef2ff] p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Privacidad</p>
                    <h2 class="mt-3 text-xl font-extrabold text-slate-900">Seguridad</h2>
                    <p class="mt-2 text-sm text-slate-600">No compartas tu contraseña. Actualiza tu correo y mantén tu cuenta segura.</p>
                </div>
                <div class="rounded-[26px] border border-[#e6eadf] bg-[#fff7ed] p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Actualización</p>
                    <h2 class="mt-3 text-xl font-extrabold text-slate-900">Soporte</h2>
                    <p class="mt-2 text-sm text-slate-600">Para fallos o dudas, envía un correo al área de soporte interno de tu centro de formación.</p>
                </div>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-2">
                <div class="rounded-[28px] bg-white p-6 shadow-[0_10px_30px_rgba(0,0,0,0.06)]">
                    <h2 class="text-lg font-bold text-slate-900">Recursos rápidos</h2>
                    <ul class="mt-5 space-y-4 text-sm text-slate-600">
                        <li class="flex items-start gap-3">
                            <span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-2xl bg-[#39A900]/10 text-[#39A900]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 4v16" />
                                    <path d="M4 12h16" />
                                </svg>
                            </span>
                            <div>
                                <p class="font-semibold text-slate-900">Actualizar datos</p>
                                <p class="text-slate-600">Modifica nombres, apellidos o correo desde el editor de perfil.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-2xl bg-[#00324d]/10 text-[#00324d]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 11c1.657 0 3-.895 3-2s-1.343-2-3-2-3 .895-3 2 1.343 2 3 2z" />
                                    <path d="M5 20c0-2.21 2.686-4 7-4s7 1.79 7 4" />
                                </svg>
                            </span>
                            <div>
                                <p class="font-semibold text-slate-900">Roles y permisos</p>
                                <p class="text-slate-600">Revisa qué roles tienes asignados y cómo impactan tu acceso.</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="rounded-[28px] bg-white p-6 shadow-[0_10px_30px_rgba(0,0,0,0.06)]">
                    <h2 class="text-lg font-bold text-slate-900">Contacto</h2>
                    <p class="mt-4 text-sm text-slate-600">¿Necesitas asistencia directa? Usa tu correo institucional para contactar al equipo de soporte de la plataforma.</p>
                    <div class="mt-6 space-y-4">
                        <div class="rounded-3xl border border-[#e6eadf] bg-[#f7f9fb] p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Correo de soporte</p>
                            <p class="mt-2 font-semibold text-slate-900">soporte@gevla.sena.edu.co</p>
                        </div>
                        <div class="rounded-3xl border border-[#e6eadf] bg-[#f7f9fb] p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Línea interna</p>
                            <p class="mt-2 font-semibold text-slate-900">(604) 123 4567</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('perfil.edit') }}" class="inline-flex items-center justify-center rounded-full bg-[#39A900] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#247200]">
                    Editar mi perfil
                </a>
                <a href="{{ route('perfil.show') }}" class="inline-flex items-center justify-center rounded-full border border-[#d8e2cf] bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Volver a mi perfil
                </a>
            </div>
        </div>
    </div>
</div>
@endsection