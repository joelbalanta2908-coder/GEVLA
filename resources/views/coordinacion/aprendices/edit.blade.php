@extends('layouts.coordinador')

@section('titulo', 'Editar aprendiz')

@section('contenido')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('coordinacion.aprendices.show', $aprendiz->id_aprendiz) }}" class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900] ring-1 ring-[#39A900]/15 transition hover:bg-[#39A900]/20">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-900">Editar aprendiz</h2>
            <p class="text-sm text-gray-500">Actualiza los datos personales y académicos de {{ trim($usuario->nombres . ' ' . $usuario->apellidos) }}.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('coordinacion.aprendices.update', $aprendiz->id_aprendiz) }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @include('coordinacion.fichas._persona_campos', ['persona' => $usuario, 'esEdicion' => true, 'rolAncla' => \App\Support\Roles::APRENDIZ])

            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600">Correo institucional <span class="font-normal text-gray-400">(opcional)</span></label>
                <input type="email" name="correo_institucional" value="{{ old('correo_institucional', $aprendiz->correo_institucional) }}" maxlength="120" placeholder="usuario@soy.sena.edu.co"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600">Estado académico</label>
                <select name="estado_academico" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                    @foreach($estadosAcademicos as $estado)
                        <option value="{{ $estado }}" @selected(old('estado_academico', $aprendiz->estado_academico) === $estado)>{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
            <a href="{{ route('coordinacion.aprendices.show', $aprendiz->id_aprendiz) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancelar</a>
            <button class="rounded-lg bg-[#39A900] px-5 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Guardar cambios</button>
        </div>
    </form>
</div>
@endsection
