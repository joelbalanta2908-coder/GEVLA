@extends('layouts.coordinador')

@section('titulo', 'Editar coordinador')

@section('contenido')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('coordinacion.coordinadores.index') }}" class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900] ring-1 ring-[#39A900]/15 transition hover:bg-[#39A900]/20">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-900">Editar coordinador</h2>
            <p class="text-sm text-gray-500">Actualiza los datos personales y de perfil de {{ trim($usuario->nombres . ' ' . $usuario->apellidos) }}.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('coordinacion.coordinadores.update', $coordinador->id_coordinacion) }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @include('coordinacion.fichas._persona_campos', ['persona' => $usuario, 'esEdicion' => true, 'rolAncla' => \App\Support\Roles::COORDINADOR])

            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600">Cargo</label>
                <input type="text" name="cargo" value="{{ old('cargo', $coordinador->cargo) }}" maxlength="100" placeholder="Ej. Coordinador Misional"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600">Dependencia <span class="font-normal text-gray-400">(opcional)</span></label>
                <input type="text" name="dependencia" value="{{ old('dependencia', $coordinador->dependencia) }}" maxlength="120" placeholder="Ej. Coordinación Académica"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
            <a href="{{ route('coordinacion.coordinadores.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancelar</a>
            <button class="rounded-lg bg-[#39A900] px-5 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Guardar cambios</button>
        </div>
    </form>

    {{-- Zona de eliminación: la opción de eliminar vive aquí (no en el listado).
         Nunca se muestra sobre la propia cuenta. --}}
    @unless((int) $usuario->id_usuario === (int) auth()->id())
        @php $nombreCompleto = trim($usuario->nombres . ' ' . $usuario->apellidos); @endphp
        <div class="rounded-2xl border border-red-200 bg-red-50/60 p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold text-red-700">Eliminar coordinador</h3>
                    <p class="mt-0.5 text-xs text-red-600/80">
                        Solo es posible si no tiene llamados gestionados a su nombre; en ese caso usa «Inactivar».
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                <form method="POST" action="{{ route('coordinacion.coordinadores.destroy', $coordinador->id_coordinacion) }}" class="shrink-0"
                      data-confirm="¿Eliminar definitivamente al coordinador {{ $nombreCompleto }}? Esta acción no se puede deshacer."
                      data-confirm-title="Eliminar coordinador" data-confirm-btn="Sí, eliminar">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Eliminar coordinador
                    </button>
                </form>
            </div>
        </div>
    @endunless
</div>
@endsection
