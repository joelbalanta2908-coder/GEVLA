{{-- Campanita de notificaciones del header. Cada usuario (de cualquier rol)
     recibe sus propias notificaciones en la tabla notificacion_usuario y el
     estado de lectura persiste en base de datos: al cerrar sesión y volver a
     entrar, las notificaciones vistas permanecen vistas. --}}
@php
    // Defensivo: si aún no se ha importado el módulo SQL de notificaciones,
    // la campanita se muestra vacía en lugar de romper la página.
    $notifTablaExiste = \Illuminate\Support\Facades\Schema::hasTable('notificacion_usuario');

    $itemsNotif = $notifTablaExiste
        ? \App\Models\NotificacionUsuario::where('id_usuario', auth()->id())
            ->orderBy('leida')
            ->orderByDesc('id_notificacion_usuario')
            ->limit(10)
            ->get()
        : collect();

    $noLeidas = $notifTablaExiste
        ? \App\Models\NotificacionUsuario::where('id_usuario', auth()->id())->where('leida', false)->count()
        : 0;

    $rutaVerTodas = match ($rolPanel ?? '') {
        'instructor'  => route('instructor.notificaciones.index'),
        'aprendiz'    => route('aprendiz.notificaciones.index'),
        'coordinador' => route('coordinacion.llamados.index'),
        default       => null,
    };
@endphp

<div x-data="{
        notifAbiertas: false,
        noLeidas: {{ $noLeidas }},
        dropdownStyle: '',
        teleported: false,
        csrf: '{{ csrf_token() }}',
        abrirToggle() {
            this.notifAbiertas = !this.notifAbiertas;
            if (this.notifAbiertas) this.$nextTick(() => this.posicionar());
        },
        // Marca una notificación puntual como vista y lo persiste en la base de datos.
        async marcarUna(id, elemento) {
            try {
                const r = await fetch('/notificaciones/' + id + '/leida', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                    keepalive: true,
                });
                if (r.ok && elemento && elemento.dataset.leida === '0') {
                    elemento.dataset.leida = '1';
                    elemento.classList.remove('bg-[#f4f9ee]');
                    const punto = elemento.querySelector('[data-punto]');
                    if (punto) punto.remove();
                    const boton = elemento.querySelector('[data-marcar]');
                    if (boton) boton.remove();
                    this.noLeidas = Math.max(this.noLeidas - 1, 0);
                }
            } catch (e) { console.error(e); }
        },
        // Marca todas las notificaciones como vistas (persistente).
        async marcarTodas() {
            try {
                const r = await fetch('{{ route('notificaciones.todas') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                });
                if (r.ok) {
                    this.$refs.dropdown.querySelectorAll('[data-notif]').forEach((el) => {
                        el.dataset.leida = '1';
                        el.classList.remove('bg-[#f4f9ee]');
                        const punto = el.querySelector('[data-punto]');
                        if (punto) punto.remove();
                        const boton = el.querySelector('[data-marcar]');
                        if (boton) boton.remove();
                    });
                    this.noLeidas = 0;
                }
            } catch (e) { console.error(e); }
        },
        posicionar() {
            try {
                const btn = this.$refs.toggleBtn;
                const dd = this.$refs.dropdown;
                if (!btn || !dd) return;
                if (!this.teleported) { document.body.appendChild(dd); this.teleported = true; }
                const btnRect = btn.getBoundingClientRect();
                const ddW = dd.offsetWidth || Math.min(window.innerWidth - 16, 350);
                let left = Math.round(btnRect.right - ddW);
                if (left < 8) left = 8;
                this.dropdownStyle = `position:fixed; z-index:2147483647; left:${left}px; top:${Math.round(btnRect.bottom + 8)}px;`;
            } catch (e) { console.error(e); }
        }
    }" class="relative" @keydown.escape.window="notifAbiertas = false">

    <button x-ref="toggleBtn" type="button" @click="abrirToggle()"
            class="relative flex h-10 w-10 items-center justify-center rounded-full text-slate-500 transition hover:bg-[#39A900]/10 hover:text-[#39A900]"
            :class="notifAbiertas && 'bg-[#39A900]/10 text-[#39A900]'"
            aria-label="Notificaciones">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/>
        </svg>
        {{-- Contador persistente de no leídas (viene de la base de datos) --}}
        <span x-show="noLeidas > 0" x-cloak x-text="noLeidas"
              class="absolute right-0 top-0 flex h-5 min-w-[1.25rem] -translate-y-1/2 translate-x-1/2 transform items-center justify-center rounded-full bg-[#ff6a13] px-1 text-[10px] font-black text-white ring-2 ring-white"></span>
    </button>

    <div x-show="notifAbiertas" x-cloak @click.outside="notifAbiertas = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-ref="dropdown"
         :style="dropdownStyle"
         class="z-[9999999] max-h-[75vh] w-[min(92vw,24rem)] overflow-auto rounded-2xl border border-[#e6eadf] bg-white shadow-xl">

        <div class="flex items-center justify-between gap-2 border-b border-[#eef1e8] bg-[#fafbf8] px-4 py-3">
            <p class="text-sm font-extrabold text-slate-900">Notificaciones</p>
            <span x-show="noLeidas > 0" x-cloak
                  class="rounded-full bg-[#ff6a13]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#ff6a13]"
                  x-text="noLeidas + ' sin revisar'"></span>
            <span x-show="noLeidas === 0" x-cloak
                  class="rounded-full bg-[#39A900]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#247200]">Todas revisadas</span>
        </div>

        @if(! $notifTablaExiste)
            <div class="px-4 py-8 text-center">
                <p class="text-sm font-medium text-slate-400">El módulo de notificaciones aún no está instalado.</p>
                <p class="mt-1 text-xs text-slate-400">Importa database/sql/modulo_notificaciones.sql en la base de datos.</p>
            </div>
        @elseif($itemsNotif->isEmpty())
            <div class="px-4 py-8 text-center">
                <svg class="mx-auto h-8 w-8 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/>
                </svg>
                <p class="mt-2 text-sm font-medium text-slate-400">No tienes notificaciones.</p>
            </div>
        @else
            <ul class="max-h-80 divide-y divide-[#f1f4ee] overflow-y-auto">
                @foreach($itemsNotif as $n)
                    <li data-notif data-leida="{{ $n->leida ? '1' : '0' }}" class="{{ $n->leida ? '' : 'bg-[#f4f9ee]' }}">
                        <div class="flex items-start gap-3 px-4 py-3">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#39A900]/10 text-[#39A900]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/>
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                {{-- Al abrir el enlace, la notificación también queda marcada como vista --}}
                                <a href="{{ $n->url ?: '#' }}"
                                   @click="marcarUna({{ $n->id_notificacion_usuario }}, $el.closest('[data-notif]'))"
                                   class="block">
                                    <span class="flex items-center gap-2 text-sm font-bold text-slate-900">
                                        @unless($n->leida)
                                            <span data-punto class="h-2 w-2 shrink-0 rounded-full bg-[#ff6a13]"></span>
                                        @endunless
                                        <span class="truncate">{{ $n->titulo }}</span>
                                    </span>
                                    @if($n->mensaje)
                                        <span class="mt-0.5 block text-xs text-slate-500" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $n->mensaje }}</span>
                                    @endif
                                    <span class="mt-0.5 block text-[11px] font-semibold text-slate-400">{{ $n->fecha_creacion?->locale('es')->diffForHumans() }}</span>
                                </a>
                                @unless($n->leida)
                                    <button type="button" data-marcar
                                            @click.stop="marcarUna({{ $n->id_notificacion_usuario }}, $el.closest('[data-notif]'))"
                                            class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-[11px] font-bold text-[#247200] ring-1 ring-[#39A900]/30 transition hover:bg-[#39A900]/10">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                                        Marcar como vista
                                    </button>
                                @endunless
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="flex items-center justify-between gap-2 border-t border-[#eef1e8] bg-[#fafbf8] px-4 py-2.5">
            @if($notifTablaExiste && $itemsNotif->isNotEmpty())
                <button type="button" @click="marcarTodas()" x-show="noLeidas > 0" x-cloak
                        class="text-xs font-bold text-[#39A900] transition hover:text-[#247200]">
                    Marcar todas como vistas
                </button>
            @endif
            @if($rutaVerTodas)
                <a href="{{ $rutaVerTodas }}" class="ml-auto text-xs font-bold text-slate-500 transition hover:text-[#39A900]">
                    {{ ($rolPanel ?? '') === 'coordinador' ? 'Ver llamados' : 'Ver todas' }}
                </a>
            @endif
        </div>
    </div>
</div>
