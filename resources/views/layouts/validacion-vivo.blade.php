{{-- Validación en vivo de campos: mientras se escribe, el borde se marca rojo
     y el mensaje de ayuda explica la regla; al cumplirse, el borde se pone
     verde y el mensaje desaparece. Convención de marcado:

     <div data-campo>
         <input data-validar="minlen" data-min="2" ...>
         <p data-ayuda class="mt-1 text-xs font-medium text-gray-400">Mínimo 2 caracteres.</p>
     </div>

     Reglas soportadas (atributo data-validar):
       - minlen         (usa data-min)
       - digits         (usa data-min y data-max: solo números, largo entre ambos)
       - digits-exact   (usa data-len: solo números, largo exacto)
       - email          (formato usuario@dominio.ejemplo)
       - match          (usa data-target: debe ser igual al valor de ese selector)

     Un campo vacío siempre queda neutral (no se marca rojo antes de escribir). --}}
<script>
    (function () {
        function evaluarCampo(campo) {
            var contenedor = campo.closest('[data-campo]');
            var ayuda = contenedor ? contenedor.querySelector('[data-ayuda]') : null;
            var valor = campo.value;
            var estado = 'neutral';

            if (valor !== '') {
                switch (campo.dataset.validar) {
                    case 'minlen':
                        estado = valor.length >= parseInt(campo.dataset.min || '0', 10) ? 'valido' : 'invalido';
                        break;
                    case 'digits':
                        estado = (/^[0-9]+$/.test(valor)
                            && valor.length >= parseInt(campo.dataset.min || '0', 10)
                            && valor.length <= parseInt(campo.dataset.max || '99', 10)) ? 'valido' : 'invalido';
                        break;
                    case 'digits-exact':
                        estado = (/^[0-9]+$/.test(valor) && valor.length === parseInt(campo.dataset.len || '0', 10)) ? 'valido' : 'invalido';
                        break;
                    case 'email':
                        estado = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor) ? 'valido' : 'invalido';
                        break;
                    case 'match':
                        var objetivo = campo.dataset.target ? document.querySelector(campo.dataset.target) : null;
                        estado = (objetivo && valor === objetivo.value) ? 'valido' : 'invalido';
                        break;
                }
            }

            campo.classList.remove('border-gray-300', 'border-red-400', 'border-emerald-500', 'focus:border-[#39A900]', 'focus:border-red-400', 'focus:border-emerald-500', 'focus:border-gray-300');
            campo.classList.add(estado === 'invalido' ? 'border-red-400' : estado === 'valido' ? 'border-emerald-500' : 'border-gray-300');
            if (estado === 'invalido') {
                campo.classList.add('focus:border-red-400');
            } else if (estado === 'valido') {
                campo.classList.add('focus:border-emerald-500');
            }
            campo.setCustomValidity(estado === 'invalido' ? (campo.dataset.msgInvalido || 'Valor inválido.') : '');

            if (ayuda) {
                ayuda.classList.toggle('hidden', estado === 'valido');
                ayuda.classList.toggle('text-red-600', estado === 'invalido');
                ayuda.classList.toggle('text-gray-400', estado !== 'invalido');
            }
        }

        // Los campos "match" (confirmar contraseña) dependen de otro campo:
        // al cambiar el original, se revalida el que lo confirma.
        function revalidarDependientes(campo) {
            if (!campo.id) return;
            document.querySelectorAll('[data-validar="match"][data-target="#' + campo.id + '"]').forEach(evaluarCampo);
        }

        document.addEventListener('input', function (e) {
            if (!e.target.matches('[data-validar]')) return;
            evaluarCampo(e.target);
            revalidarDependientes(e.target);
        });
    })();
</script>
