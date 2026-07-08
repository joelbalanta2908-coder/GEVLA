@extends('layouts.coordinador')

@section('titulo', 'Editar instructor')

@section('contenido')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('coordinacion.docentes.show', $instructor->id_instructor) }}" class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900] ring-1 ring-[#39A900]/15 transition hover:bg-[#39A900]/20">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-900">Editar instructor</h2>
            <p class="text-sm text-gray-500">Actualiza los datos personales y de perfil de {{ trim($usuario->nombres . ' ' . $usuario->apellidos) }}.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('coordinacion.docentes.update', $instructor->id_instructor) }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @include('coordinacion.fichas._persona_campos', ['persona' => $usuario, 'esEdicion' => true, 'rolAncla' => \App\Support\Roles::INSTRUCTOR])

            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600">Código de instructor</label>
                <input type="text" name="codigo_instructor" value="{{ old('codigo_instructor', $instructor->codigo_instructor) }}" maxlength="30"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600">Área de formación <span class="font-normal text-gray-400">(opcional)</span></label>
                <input type="text" name="area_formacion" value="{{ old('area_formacion', $instructor->area_formacion) }}" maxlength="120" placeholder="Ej. Tecnología e Informática"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-gray-600">Tipo de docente <span class="font-normal text-gray-400">(opcional)</span></label>
                <select name="tipo_docente"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                    <option value="">No definido</option>
                    @foreach($tipos as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected(old('tipo_docente', $instructor->tipo_docente) === $valor)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
            <a href="{{ route('coordinacion.docentes.show', $instructor->id_instructor) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancelar</a>
            <button class="rounded-lg bg-[#39A900] px-5 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Guardar cambios</button>
        </div>
    </form>
</div>
@endsection
