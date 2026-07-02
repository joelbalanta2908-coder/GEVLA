{{-- Campanita de notificaciones dinámicas del header. Recibe $rolPanel
     ('coordinador' | 'instructor' | 'aprendiz' | 'admin') y muestra las
     novedades relevantes para ese rol. --}}
@php
    $itemsNotif = collect();

    switch ($rolPanel ?? '') {
        // Coordinador: llamados de atención pendientes de gestión.
        case 'coordinador':
            $itemsNotif = \App\Models\LlamadoAtencion::with(['aprendiz.usuario', 'instructor.usuario'])
                ->whereIn('estado_llamado', ['registrado', 'en_revision'])
                ->orderByDesc('fecha_llamado')
                ->orderByDesc('id_llamado')
                ->limit(6)
                ->get()
                ->map(fn ($l) => [
                    'titulo' => $l->estado_llamado === 'registrado' ? 'Llamado por revisar' : 'Llamado en revisión',
                    'texto'  => trim(($l->aprendiz?->usuario?->nombres ?? '') . ' ' . ($l->aprendiz?->usuario?->apellidos ?? '')) . ' — ' . $l->asunto,
                    'fecha'  => $l->fecha_llamado ? \Illuminate\Support\Carbon::parse($l->fecha_llamado) : null,
                    'url'    => route('coordinacion.llamados.show', $l->id_llamado),
                ]);
            break;

        // Instructor: notificaciones generadas a partir de sus llamados.
        case 'instructor':
            $instructorNotif = auth()->user()->instructor;
            if ($instructorNotif) {
                $itemsNotif = \App\Models\Notificacion::with('aprendiz.usuario')
                    ->whereHas('llamado', fn ($q) => $q->where('id_instructor', $instructorNotif->id_instructor))
                    ->orderByDesc('fecha_envio')
                    ->orderByDesc('id_notificacion')
                    ->limit(6)
                    ->get()
                    ->map(fn ($n) => [
                        'titulo' => str($n->tipo_notificacion)->replace('_', ' ')->ucfirst(),
                        'texto'  => $n->contenido_resumen
                            ?: 'Notificación para ' . trim(($n->aprendiz?->usuario?->nombres ?? '') . ' ' . ($n->aprendiz?->usuario?->apellidos ?? '')),
                        'fecha'  => $n->fecha_envio,
                        'url'    => route('instructor.notificaciones.index'),
                    ]);
            }
            break;

        // Aprendiz: sus notificaciones oficiales.
        case 'aprendiz':
            $aprendizNotif = auth()->user()->aprendiz;
            if ($aprendizNotif) {
                $itemsNotif = \App\Models\Notificacion::where('id_aprendiz', $aprendizNotif->id_aprendiz)
                    ->orderByDesc('fecha_envio')
                    ->orderByDesc('id_notificacion')
                    ->limit(6)
                    ->get()
                    ->map(fn ($n) => [
                        'titulo' => str($n->tipo_notificacion)->replace('_', ' ')->ucfirst(),
                        'texto'  => $n->contenido_resumen ?: 'Tienes una nueva notificación.',
                        'fecha'  => $n->fecha_envio,
                        'url'    => route('aprendiz.notificaciones.index'),
                    ]);
            }
            break;

        // Administrador: cuentas de usuario creadas recientemente.
        case 'admin':
            $itemsNotif = \App\Models\Usuario::orderByDesc('fecha_creacion')
                ->orderByDesc('id_usuario')
                ->limit(6)
                ->get()
                ->map(fn ($u) => [
                    'titulo' => 'Cuenta ' . ($u->estado_usuario === 'activo' ? 'activa' : ucfirst((string) $u->estado_usuario)),
                    'texto'  => 'Usuario: ' . trim($u->nombres . ' ' . $u->apellidos) . ($u->username ? " ({$u->username})" : ''),
                    'fecha'  => $u->fecha_creacion,
                    'url'    => route('admin.usuarios.index'),
                ]);
            break;
    }
@endphp

<div x-data="{ notifAbiertas: false }" class="relative" @keydown.escape.window="notifAbiertas = false">
    <button type="button" @click="notifAbiertas = !notifAbiertas"
            class="relative flex h-10 w-10 items-center justify-center rounded-full text-slate-500 transition hover:bg-[#39A900]/10 hover:text-[#39A900]"
            :class="notifAbiertas && 'bg-[#39A900]/10 text-[#39A900]'"
            aria-label="Notificaciones">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/>
        </svg>
        @if($itemsNotif->isNotEmpty())
            <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-[#ff6a13] px-1 text-[10px] font-black text-white ring-2 ring-white">
                {{ $itemsNotif->count() }}
            </span>
        @endif
    </button>

    <div x-show="notifAbiertas" x-cloak @click.outside="notifAbiertas = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute right-0 z-[90] mt-2 w-[min(92vw,22rem)] overflow-hidden rounded-2xl border border-[#e6eadf] bg-white shadow-[0_18px_45px_rgba(0,0,0,0.16)]">
        <div class="flex items-center justify-between border-b border-[#eef1e8] bg-[#fafbf8] px-4 py-3">
            <p class="text-sm font-extrabold text-slate-900">Notificaciones</p>
            <span class="rounded-full bg-[#39A900]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#247200]">{{ $itemsNotif->count() }}</span>
        </div>

        @if($itemsNotif->isEmpty())
            <div class="px-4 py-8 text-center">
                <svg class="mx-auto h-8 w-8 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/>
                </svg>
                <p class="mt-2 text-sm font-medium text-slate-400">No tienes notificaciones nuevas.</p>
            </div>
        @else
            <ul class="max-h-80 divide-y divide-[#f1f4ee] overflow-y-auto">
                @foreach($itemsNotif as $item)
                    <li>
                        <a href="{{ $item['url'] }}" class="flex items-start gap-3 px-4 py-3 transition hover:bg-[#fbfcf8]">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#39A900]/10 text-[#39A900]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/>
                                </svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-bold text-slate-900">{{ $item['titulo'] }}</span>
                                <span class="block text-xs text-slate-500" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $item['texto'] }}</span>
                                @if($item['fecha'])
                                    <span class="mt-0.5 block text-[11px] font-semibold text-slate-400">{{ $item['fecha']->locale('es')->diffForHumans() }}</span>
                                @endif
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        @if(($rolPanel ?? '') === 'instructor')
            <a href="{{ route('instructor.notificaciones.index') }}" class="block border-t border-[#eef1e8] bg-[#fafbf8] px-4 py-2.5 text-center text-xs font-bold text-[#39A900] transition hover:bg-[#39A900]/10">Ver todas las notificaciones</a>
        @elseif(($rolPanel ?? '') === 'aprendiz')
            <a href="{{ route('aprendiz.notificaciones.index') }}" class="block border-t border-[#eef1e8] bg-[#fafbf8] px-4 py-2.5 text-center text-xs font-bold text-[#39A900] transition hover:bg-[#39A900]/10">Ver todas las notificaciones</a>
        @elseif(($rolPanel ?? '') === 'coordinador')
            <a href="{{ route('coordinacion.llamados.index') }}" class="block border-t border-[#eef1e8] bg-[#fafbf8] px-4 py-2.5 text-center text-xs font-bold text-[#39A900] transition hover:bg-[#39A900]/10">Ver todos los llamados</a>
        @endif
    </div>
</div>
