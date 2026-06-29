@extends($layout)

@section('titulo', 'Editar Mi Perfil')

@section('contenido')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('perfil.show') }}" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900">
        ← Volver a mi perfil
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-[#00324D]">Editar Datos Personales</h2>
        <p class="mt-1 text-sm text-gray-500">Actualiza tus nombres, apellidos y correo de contacto.</p>

        <form method="POST" action="{{ route('perfil.update') }}" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <label class="block text-sm font-bold text-gray-700">Nombres</label>
                    <input type="text" name="nombres" required value="{{ old('nombres', $usuario->nombres) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                </div>

                <div class="sm:col-span-1">
                    <label class="block text-sm font-bold text-gray-700">Apellidos</label>
                    <input type="text" name="apellidos" required value="{{ old('apellidos', $usuario->apellidos) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-bold text-gray-700">Correo Electrónico</label>
                    <input type="email" name="correo" required value="{{ old('correo', $usuario->correo) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-[#39A900] focus:ring-[#39A900]">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 mt-6">
                <a href="{{ route('perfil.show') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-[#39A900] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#247200]">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
