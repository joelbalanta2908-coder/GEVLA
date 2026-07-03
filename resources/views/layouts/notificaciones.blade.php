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
                    ->where('estado_notificacion', 'enviada')
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
                    ->where('estado_notificacion', 'enviada')
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
@php $__marcarUrl = route('notificaciones.marcar_recibidas'); $__csrf = csrf_token(); @endphp

    <div x-data="{
        notifAbiertas: false,
        notificacionesRevisadas: 0,
        dropdownStyle: '',
        teleported: false,
        async marcar(){ try{ await fetch('{{$__marcarUrl}}',{ method:'POST', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':'{{$__csrf}}','Accept':'application/json' }, body: JSON.stringify({}) }); }catch(e){ console.error(e); } },
        abrirToggle(){ this.notifAbiertas = !this.notifAbiertas; if(this.notifAbiertas){ this.marcar(); this.notificacionesRevisadas = {{ $itemsNotif->count() }}; this.$nextTick(() => this.posicionar()); } },
        posicionar(){
            try{
                const btn = this.$refs.toggleBtn;
                const dd = this.$refs.dropdown;
                if(!btn || !dd) return;
                // Ensure dropdown is appended to body to escape any stacking context
                if(!this.teleported){
                    document.body.appendChild(dd);
                    this.teleported = true;
                }
                const btnRect = btn.getBoundingClientRect();
                const ddW = dd.offsetWidth || Math.min(window.innerWidth - 16, 350);
                let left = Math.round(btnRect.right - ddW);
                if(left < 8) left = 8;
                const top = Math.round(btnRect.bottom + 8 + window.scrollY);
                this.dropdownStyle = `position:fixed; z-index:2147483647; left:${left}px; top:${top}px;`;
                // Reposition on next tick in case fonts or content changed
                setTimeout(() => {
                    const btnR = btn.getBoundingClientRect();
                    let l2 = Math.round(btnR.right - (dd.offsetWidth || ddW));
                    if(l2 < 8) l2 = 8;
                    this.dropdownStyle = `position:fixed; z-index:2147483647; left:${l2}px; top:${Math.round(btnR.bottom + 8 + window.scrollY)}px;`;
                }, 50);
            }catch(e){ console.error(e); }
        }
    }" class="relative" @keydown.escape.window="notifAbiertas = false">
    <button x-ref="toggleBtn" type="button" @click="abrirToggle()"
            class="relative flex h-10 w-10 items-center justify-center rounded-full text-slate-500 transition hover:bg-[#39A900]/10 hover:text-[#39A900]"
            :class="notifAbiertas && 'bg-[#39A900]/10 text-[#39A900]'"
            aria-label="Notificaciones">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/>
        </svg>
        @if($itemsNotif->isNotEmpty())
                        <span x-show="notificacionesRevisadas < {{ $itemsNotif->count() }}" x-cloak
                                    class="absolute right-0 top-0 transform translate-x-1/2 -translate-y-1/2 flex h-5 w-5 items-center justify-center rounded-full bg-[#ff6a13] text-[10px] font-black text-white ring-2 ring-white transition-all duration-300 animate-pulse">
                                <span x-text="Math.max({{ $itemsNotif->count() }} - notificacionesRevisadas, 0)"></span>
                        </span>
        @endif
    </button>

        <div x-show="notifAbiertas" x-cloak @click.outside="notifAbiertas = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-ref="dropdown"
            :style="dropdownStyle"
            class="z-[9999999] w-[min(92vw,22rem)] max-h-[75vh] overflow-auto rounded-2xl border border-[#e6eadf] bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-[#eef1e8] bg-[#fafbf8] px-4 py-3">
            <p class="text-sm font-extrabold text-slate-900">Notificaciones</p>
            <span x-show="notificacionesRevisadas < {{ $itemsNotif->count() }}" x-cloak
                  class="rounded-full bg-[#ff6a13]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#ff6a13] transition-all duration-300"
                  x-text="({{ $itemsNotif->count() }} - notificacionesRevisadas) + ' sin revisar'"></span>
            <span x-show="notificacionesRevisadas >= {{ $itemsNotif->count() }}" x-cloak
                  class="rounded-full bg-[#39A900]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#247200]">Todas revisadas</span>
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
                @foreach($itemsNotif as $idx => $item)
                    <li>
                        <a href="{{ $item['url'] }}" 
                           @click="notificacionesRevisadas = Math.min(notificacionesRevisadas + 1, {{ $itemsNotif->count() }})"
                           class="flex items-start gap-3 px-4 py-3 transition hover:bg-[#fbfcf8]">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#39A900]/10 text-[#39A900] transition-all duration-300 hover:scale-110">
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
