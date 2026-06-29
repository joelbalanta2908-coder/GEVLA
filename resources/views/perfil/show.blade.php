@extends($layout)

@section('titulo', 'Mi Perfil')

@section('contenido')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-5">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-xl font-bold text-[#39A900] ring-4 ring-white shadow-sm">
                    {{ substr($usuario->nombres ?? 'U', 0, 1) }}{{ substr($usuario->apellidos ?? '', 0, 1) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $usuario->nombres }} {{ $usuario->apellidos }}</h2>
                    <p class="text-sm text-gray-500">{{ $usuario->rolPrincipal() ?? 'Usuario del Sistema' }}</p>
                </div>
            </div>
        </div>

        <div class="px-6 py-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-xs font-medium uppercase text-gray-400">Nombres</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $usuario->nombres ?? 'No registrado' }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-xs font-medium uppercase text-gray-400">Apellidos</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $usuario->apellidos ?? 'No registrado' }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-xs font-medium uppercase text-gray-400">Correo Electrónico</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $usuario->correo }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-xs font-medium uppercase text-gray-400">Nombre de Usuario</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $usuario->username }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-xs font-medium uppercase text-gray-400">Estado de Cuenta</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($usuario->estado_usuario === 'activo')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                Activo
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                Inactivo/Bloqueado
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-xs font-medium uppercase text-gray-400">Último Acceso</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->format('d/m/Y h:i A') : 'No registrado' }}</dd>
                </div>
            </dl>
        </div>
        
        @if($usuario->tieneRol('Coordinador') || $usuario->tieneRol('Instructor'))
            <div class="border-t border-gray-100 bg-gray-50 px-6 py-4 flex justify-end">
                <a href="{{ route('perfil.edit') }}" class="rounded-lg bg-[#39A900] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#247200] shadow-sm flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Editar Datos
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
