{{-- Toast de notificación (login exitoso u otros mensajes flash tipo "toast").
     Elegante, con icono de éxito, animación suave y cierre automático. --}}
@if(session('toast'))
    @php
        $toastMensaje = session('toast');
        $toastTitulo  = 'Listo';
    @endphp
    <div
        x-data="{ show: false }"
        x-init="$nextTick(() => show = true); setTimeout(() => show = false, 4500)"
        x-show="show"
        x-cloak
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="translate-x-6 opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transform transition ease-in duration-300"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="translate-x-6 opacity-0"
        role="status" aria-live="polite"
        class="fixed right-4 top-20 z-[100] w-[min(92vw,22rem)] overflow-hidden rounded-2xl border border-[#39A900]/20 bg-white shadow-[0_18px_45px_rgba(0,0,0,0.16)] sm:right-6 sm:top-20">
        <div class="flex items-start gap-3 p-4">
            <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#39A900]/12 text-[#39A900]">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-extrabold text-slate-900">{{ $toastTitulo }}</p>
                <p class="mt-0.5 text-sm font-medium text-slate-500">{{ $toastMensaje }}</p>
            </div>
            <button type="button" @click="show = false" aria-label="Cerrar notificación"
                    class="shrink-0 rounded-full p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
        {{-- Barra de progreso del cierre automático --}}
        <div class="h-1 w-full bg-[#39A900]/10">
            <div class="h-full bg-[#39A900]"
                 x-init="$nextTick(() => $el.style.width = '0%')"
                 style="width:100%; transition: width 4500ms linear;"></div>
        </div>
    </div>
@endif

{{-- Búsqueda en tiempo real: cualquier <form data-live-form> se envía solo al
     escribir (con retardo) o al cambiar un select/fecha marcados con data-live. --}}
<script>
    (function () {
        function wire() {
            document.querySelectorAll('form[data-live-form]').forEach(function (form) {
                if (form.__liveWired) return;
                form.__liveWired = true;
                var timer = null;
                form.querySelectorAll('[data-live]').forEach(function (el) {
                    var evento = (el.tagName === 'SELECT' || el.type === 'date') ? 'change' : 'input';
                    el.addEventListener(evento, function () {
                        clearTimeout(timer);
                        timer = setTimeout(function () {
                            (form.requestSubmit ? form.requestSubmit() : form.submit());
                        }, evento === 'input' ? 450 : 0);
                    });
                });
            });
        }
        wire();
        document.addEventListener('turbo:load', wire);
    })();
</script>
