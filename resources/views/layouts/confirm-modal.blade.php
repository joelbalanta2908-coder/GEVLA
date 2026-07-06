{{-- Modal de confirmación del sistema. Reemplaza los confirm() nativos del navegador.
     Uso: en cualquier <form> añadir data-confirm="mensaje" y, opcionalmente,
     data-confirm-title="Título" y data-confirm-btn="Texto del botón". --}}
{{-- Estilos críticos inline: el modal se centra y cubre la pantalla aunque el
     CSS compilado no tenga alguna utilidad, y su z-index le gana a la barra
     lateral. El JS lo teletransporta al <body> para escapar de contenedores
     con transform/backdrop-filter que rompen position:fixed. --}}
<div id="gevla-confirm" role="dialog" aria-modal="true" aria-labelledby="gevla-confirm-titulo"
     style="display:none; position:fixed; top:0; right:0; bottom:0; left:0; z-index:2147483000; align-items:center; justify-content:center; padding:16px;">
    <div data-confirm-cancelar style="position:absolute; top:0; right:0; bottom:0; left:0; background:rgba(0,0,0,0.5);"></div>
    <div class="relative overflow-hidden rounded-[28px] border border-[#e6eadf] bg-white shadow-[0_30px_80px_rgba(0,0,0,0.25)]"
         style="position:relative; width:100%; max-width:28rem; background:#ffffff; border-radius:28px; overflow:hidden;">
        <div class="flex items-start gap-4 px-6 pt-6">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 9v4m0 4h.01"/>
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                </svg>
            </span>
            <div class="min-w-0">
                <h3 id="gevla-confirm-titulo" class="text-lg font-extrabold text-slate-900" data-confirm-titulo>Confirmar acción</h3>
                <p class="mt-1 text-sm text-slate-500" data-confirm-mensaje>¿Deseas continuar?</p>
            </div>
        </div>
        <div class="mt-6 flex flex-col-reverse gap-3 border-t border-[#eef1e8] bg-[#fafbf8] px-6 py-4 sm:flex-row sm:justify-end">
            <button type="button" data-confirm-cancelar
                    class="inline-flex items-center justify-center rounded-full border border-[#d8e2cf] bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Cancelar
            </button>
            <button type="button" data-confirm-aceptar
                    class="inline-flex items-center justify-center rounded-full bg-red-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-700">
                Sí, continuar
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('gevla-confirm');
        if (!modal || modal.__wired) return;
        modal.__wired = true;

        // Teletransporte al <body>: si el modal queda dentro de un contenedor
        // con transform o backdrop-filter, el position:fixed se calcula contra
        // ese contenedor (no contra la ventana) y el modal aparece descuadrado
        // y por debajo de la barra lateral. Colgado del body con un z-index
        // máximo, siempre cubre toda la pantalla.
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        var titulo = modal.querySelector('[data-confirm-titulo]');
        var mensaje = modal.querySelector('[data-confirm-mensaje]');
        var btnAceptar = modal.querySelector('[data-confirm-aceptar]');
        var formPendiente = null;

        function abrir(form) {
            formPendiente = form;
            titulo.textContent = form.getAttribute('data-confirm-title') || 'Confirmar acción';
            mensaje.textContent = form.getAttribute('data-confirm') || '¿Deseas continuar?';
            btnAceptar.textContent = form.getAttribute('data-confirm-btn') || 'Sí, continuar';
            modal.style.display = 'flex';
            btnAceptar.focus();
        }

        function cerrar() {
            formPendiente = null;
            modal.style.display = 'none';
        }

        document.addEventListener('submit', function (e) {
            var form = e.target.closest('form[data-confirm]');
            if (!form || form.__confirmado) return;
            e.preventDefault();
            abrir(form);
        }, true);

        btnAceptar.addEventListener('click', function () {
            if (!formPendiente) return;
            var form = formPendiente;
            form.__confirmado = true;
            cerrar();
            (form.requestSubmit ? form.requestSubmit() : form.submit());
        });

        modal.querySelectorAll('[data-confirm-cancelar]').forEach(function (el) {
            el.addEventListener('click', cerrar);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') cerrar();
        });
    })();
</script>
