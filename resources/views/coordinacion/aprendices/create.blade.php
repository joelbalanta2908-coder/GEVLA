@extends('layouts.coordinador')

@section('titulo', 'Nuevo aprendiz')

@section('contenido')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('coordinacion.aprendices.index') }}" class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900] ring-1 ring-[#39A900]/15 transition hover:bg-[#39A900]/20">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-900">Nuevo aprendiz</h2>
            <p class="text-sm text-gray-500">Registra al aprendiz y, si quieres, matricúlalo en una ficha.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('coordinacion.aprendices.store') }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @include('coordinacion.fichas._persona_campos')

            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-gray-600">Correo institucional <span class="font-normal text-gray-400">(opcional)</span></label>
                <input type="email" name="correo_institucional" value="{{ old('correo_institucional') }}" maxlength="120"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-gray-600">Matricular en ficha <span class="font-normal text-gray-400">(opcional)</span></label>
                <select name="id_ficha"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#39A900] focus:outline-none focus:ring-2 focus:ring-[#39A900]/30">
                    <option value="">Sin matricular por ahora</option>
                    @foreach($fichas as $ficha)
                        <option value="{{ $ficha->id_ficha }}" @selected((string) old('id_ficha', $fichaSeleccionada) === (string) $ficha->id_ficha)>
                            Ficha {{ $ficha->numero_ficha }} — {{ optional($ficha->programa)->nombre_programa }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
            <a href="{{ route('coordinacion.aprendices.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancelar</a>
            <button class="rounded-lg bg-[#39A900] px-5 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Crear aprendiz</button>
        </div>
    </form>
</div>
@endsection
