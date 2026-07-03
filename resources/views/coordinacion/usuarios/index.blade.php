@extends('layouts.coordinador')

@section('titulo', 'Usuarios')

@section('contenido')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Usuarios del sistema</h2>
        <p class="mt-1 text-sm text-gray-500">Consulta todas las cuentas registradas, sus roles y el estado de acceso. La activación de aprendices e instructores se gestiona desde sus secciones.</p>
    </div>

    <form method="GET" action="{{ route('coordinacion.usuarios.index') }}" data-live-form class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <input type="text" name="buscar" value="{{ $buscar }}" data-live placeholder="Nombre, documento, correo o usuario..."
               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30 lg:col-span-2">
        <select name="rol" data-live class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            <option value="">Todos los roles</option>
            @foreach($roles as $r)
                <option value="{{ $r }}" @selected($rol === $r)>{{ $r }}</option>
            @endforeach
        </select>
        <select name="estado_usuario" data-live class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            <option value="">Todos los estados</option>
            <option value="activo" @selected($estado === 'activo')>Activo</option>
            <option value="inactivo" @selected($estado === 'inactivo')>Inactivo</option>
            <option value="bloqueado" @selected($estado === 'bloqueado')>Bloqueado</option>
        </select>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="responsive-cards w-full min-w-[760px] text-sm">
            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3">Usuario</th>
                    <th class="px-5 py-3">Documento</th>
                    <th class="px-5 py-3">Correo</th>
                    <th class="px-5 py-3">Roles</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3">Último acceso</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($usuarios as $u)
                    @php
                        $estadoBadge = match($u->estado_usuario) {
                            'activo'    => 'bg-[#39A900]/10 text-[#247200]',
                            'inactivo'  => 'bg-red-100 text-red-700',
                            'bloqueado' => 'bg-slate-200 text-slate-700',
                            default     => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3" data-label="Usuario">
                            <div class="flex items-center gap-3">
                                @if($u->fotoUrl())
                                    <img src="{{ $u->fotoUrl() }}" alt="Foto" class="h-9 w-9 shrink-0 rounded-full object-cover">
                                @else
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#39A900]/10 text-xs font-bold text-[#39A900]">
                                        {{ $u->iniciales() }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-gray-900">{{ trim($u->nombres.' '.$u->apellidos) }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ $u->username }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-gray-600" data-label="Documento">{{ $u->tipo_documento }} {{ $u->numero_documento }}</td>
                        <td class="px-5 py-3 text-gray-600" data-label="Correo">{{ $u->correo }}</td>
                        <td class="px-5 py-3" data-label="Roles">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($u->roles as $rolUsuario)
                                    <span class="rounded-full bg-[#00324d]/10 px-2.5 py-0.5 text-[11px] font-semibold text-[#00324d]">{{ $rolUsuario->nombre_rol }}</span>
                                @empty
                                    <span class="text-xs text-gray-400">Sin rol</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-5 py-3" data-label="Estado">
                            <span class="estado-badge inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $estadoBadge }}">{{ ucfirst($u->estado_usuario) }}</span>
                        </td>
                        <td class="px-5 py-3 text-gray-600" data-label="Último acceso">
                            {{ $u->ultimo_acceso ? $u->ultimo_acceso->locale('es')->diffForHumans() : 'Nunca' }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No se encontraron usuarios con los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($usuarios, 'links'))
        {{ $usuarios->links() }}
    @endif
</div>
@endsection
