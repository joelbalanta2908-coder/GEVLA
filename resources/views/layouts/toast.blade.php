{{-- Toast de notificación (login exitoso u otros mensajes flash tipo "toast").
     Elegante, con iconos específicos, animación suave y cierre automático después de 5 segundos.
     Se posiciona como fixed en dashboards o inline en el login. --}}
@if(session('toast'))
    @php
        $toastMensaje = session('toast');
        $toastType = session('toast_type', 'default');
        $toastTitulo = match($toastType) {
            'login' => '¡Bienvenido!',
            'logout' => '¡Hasta luego!',
            default => 'Listo',
        };
        $isInline = isset($inlineToast) && $inlineToast === true;
    @endphp
    <div
        x-data="{ show: false }"
        x-init="$nextTick(() => show = true); setTimeout(() => show = false, 5000)"
        x-show="show"
        x-cloak
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="{{ $isInline ? 'opacity-0 -translate-y-2' : 'translate-x-6 opacity-0' }}"
        x-transition:enter-end="{{ $isInline ? 'opacity-100 translate-y-0' : 'translate-x-0 opacity-100' }}"
        x-transition:leave="transform transition ease-in duration-300"
        x-transition:leave-start="{{ $isInline ? 'opacity-100 translate-y-0' : 'translate-x-0 opacity-100' }}"
        x-transition:leave-end="{{ $isInline ? 'opacity-0 -translate-y-2' : 'translate-x-6 opacity-0' }}"
        role="status" aria-live="polite"
        class="{{ $isInline ? 'mb-5' : 'fixed right-4 top-20 z-[100] w-[min(92vw,22rem)] sm:right-6 sm:top-20' }} overflow-hidden rounded-2xl border border-[#39A900]/20 bg-white shadow-[0_18px_45px_rgba(0,0,0,0.16)] {{ !$isInline ? 'w-[min(92vw,22rem)]' : '' }}">
        <div class="flex items-start gap-3 p-4">
            <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#39A900]/12 text-[#39A900]">
                @if($toastType === 'login')
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 7l-5 5m0 0l5 5m-5-5h12" />
                    </svg>
                @elseif($toastType === 'logout')
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                @else
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                @endif
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
        {{-- Barra de progreso del cierre automático (5 segundos) --}}
        <div class="h-1 w-full bg-[#39A900]/10">
            <div class="h-full bg-[#39A900]"
                 x-init="$nextTick(() => $el.style.width = '0%')"
                 style="width:100%; transition: width 5000ms linear;"></div>
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
                        // Retardo amplio para dar tiempo a terminar de escribir
                        // antes de actualizar la tabla.
                        timer = setTimeout(function () {
                            (form.requestSubmit ? form.requestSubmit() : form.submit());
                        }, evento === 'input' ? 900 : 0);
                    });
                });
            });
        }
        wire();
        document.addEventListener('DOMContentLoaded', wire);
    })();
</script>

{{-- Validación en vivo de campos personales: bloquea al escribir los
     caracteres no permitidos, en vez de esperar a que el usuario envíe el
     formulario y se entere por el mensaje de error. El backend (CreaUsuarios)
     sigue siendo la validación real; esto es solo una ayuda de UX. --}}
<script>
    document.addEventListener('input', function (e) {
        if (e.target.matches('[data-solo-letras]')) {
            e.target.value = e.target.value
                .replace(/[^A-Za-zÀ-ÖØ-öø-ÿ\s]/g, '') // solo letras y espacios
                .replace(/^\s+/, '')                    // sin espacio al inicio
                .replace(/\s{2,}/g, ' ')                 // espacios consecutivos -> uno solo
                // Mayúscula automática al inicio y después de cada espacio.
                .replace(/(^|\s)([a-zà-öø-ÿ])/g, function (todo, sep, letra) { return sep + letra.toUpperCase(); });
        }
        if (e.target.matches('[data-solo-numeros]')) {
            e.target.value = e.target.value.replace(/\D/g, '');
        }
    });

    // Al salir del campo se recorta también el espacio final (mientras se
    // escribe se conserva, porque separa la siguiente palabra). "blur" no
    // burbujea, por eso se escucha en fase de captura (tercer argumento true).
    document.addEventListener('blur', function (e) {
        if (e.target.matches('[data-solo-letras]') && e.target.value.endsWith(' ')) {
            e.target.value = e.target.value.trimEnd();
        }
    }, true);

    // Señal de posible cierre de pestaña: al ocultarse la página se avisa al
    // servidor (sendBeacon). Si era solo navegación interna, la siguiente
    // petición llega en segundos y no pasa nada; si la pestaña se cerró de
    // verdad, la sesión se invalida (middleware SeguridadSesion).
    (function () {
        if (window.__gevlaCierrePestana) return;
        window.__gevlaCierrePestana = true;
        window.addEventListener('pagehide', function (evento) {
            // Si la página queda "congelada" (bfcache) no es un cierre real.
            if (evento.persisted || !navigator.sendBeacon) return;
            var token = document.querySelector('meta[name="csrf-token"]');
            if (!token) return;
            var datos = new FormData();
            datos.append('_token', token.content);
            navigator.sendBeacon('{{ route('sesion.cerrando') }}', datos);
        });
    })();
</script>

@include('layouts.validacion-vivo')
