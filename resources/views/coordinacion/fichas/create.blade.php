@extends('layouts.coordinador')

@section('titulo', 'Nueva ficha')

@section('contenido')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('coordinacion.fichas.index') }}" class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#39A900]/10 text-[#39A900] ring-1 ring-[#39A900]/15 transition hover:bg-[#39A900]/20">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-900">Nueva ficha</h2>
            <p class="text-sm text-gray-500">Registra los datos de la ficha y su instructor líder inicial.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('coordinacion.fichas.store') }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf
        @include('coordinacion.fichas._form', ['ficha' => null])

        <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
            <a href="{{ route('coordinacion.fichas.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancelar</a>
            <button class="rounded-lg bg-[#39A900] px-5 py-2 text-sm font-semibold text-white hover:bg-[#2D8200]">Crear ficha</button>
        </div>
    </form>
</div>
@endsection
