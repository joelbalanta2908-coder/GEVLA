@extends('layouts.admin')

@section('titulo', 'Usuarios')

@section('contenido')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Cuentas de usuario</h2>
        <p class="mt-1 text-sm text-gray-500">Activa, desactiva o bloquea el acceso de los usuarios al sistema.</p>
    </div>

    <form method="GET" action="{{ route('admin.usuarios.index') }}" data-live-form class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input type="text" name="buscar" value="{{ $buscar }}" data-live placeholder="Buscar por nombre, correo, documento o usuario..."
               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm caret-[#39A900] focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30 sm:max-w-md">
        <select name="estado" data-live class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            <option value="">Todos los estados</option>
            @foreach($estados as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected($estado === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-[#39A900] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Filtrar</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="responsive-cards w-full min-w-[720px] text-sm">
            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3">Usuario</th>
                    <th class="px-5 py-3">Documento</th>
                    <th class="px-5 py-3">Roles</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3 text-right">Cambiar estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($usuarios as $u)
                    @php
                        $badge = match($u->estado_usuario) {
                            'activo'    => 'bg-[#39A900]/10 text-[#247200]',
                            'inactivo'  => 'bg-amber-100 text-amber-700',
                            'bloqueado' => 'bg-red-100 text-red-700',
                            default     => 'bg-gray-100 text-gray-600',
                        };
                        $esYo = (int) $u->id_usuario === (int) auth()->id();
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3" data-label="Usuario">
                            <p class="font-semibold text-gray-900">{{ trim($u->nombres.' '.$u->apellidos) }}</p>
                            <p class="text-xs text-gray-500">{{ $u->correo }}</p>
                        </td>
                        <td class="px-5 py-3 text-gray-600" data-label="Documento">{{ $u->tipo_documento }} {{ $u->numero_documento }}</td>
                        <td class="px-5 py-3" data-label="Roles">
                            @forelse($u->roles as $rol)
                                <span class="mr-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">{{ $rol->nombre_rol }}</span>
                            @empty
                                <span class="text-xs text-gray-400">—</span>
                            @endforelse
                        </td>
                        <td class="px-5 py-3" data-label="Estado">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $badge }}">{{ $estados[$u->estado_usuario] ?? ucfirst($u->estado_usuario) }}</span>
                        </td>
                        <td class="px-5 py-3 text-right" data-label="Cambiar estado">
                            @if($esYo)
                                <span class="text-xs text-gray-400">Tu cuenta</span>
                            @else
                                <form method="POST" action="{{ route('admin.usuarios.estado', $u->id_usuario) }}" class="flex items-center justify-end gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="estado_usuario" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                                        @foreach($estados as $valor => $etiqueta)
                                            <option value="{{ $valor }}" @selected($u->estado_usuario === $valor)>{{ $etiqueta }}</option>
                                        @endforeach
                                    </select>
                                    <button class="rounded-lg bg-[#39A900] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#2D8200]">Guardar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">No se encontraron usuarios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($usuarios, 'links'))
        {{ $usuarios->links() }}
    @endif
</div>
@endsection
