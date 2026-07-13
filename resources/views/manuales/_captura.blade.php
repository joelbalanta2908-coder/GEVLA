{{-- Captura de pantalla del manual.
     - Si el archivo existe en public/manuales/capturas/, se incrusta la imagen real.
     - Si aún no se ha aportado, se muestra un espacio reservado (NUNCA una
       imagen de ejemplo o inventada), con el nombre exacto del archivo que falta. --}}
@php $src = \App\Support\ManualPdf::captura($archivo); @endphp
<div class="captura">
    @if($src)
        <img src="{{ $src }}" alt="{{ $pie ?? $archivo }}">
        @isset($pie)<div class="pie-captura">{{ $pie }}</div>@endisset
    @else
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="border:1.5px dashed #b8c4b4; background-color:#f7faf5; padding:26px 10px; text-align:center; color:#8a978a; font-size:8.5px;">
                    <span style="font-size:15px;">📷</span><br>
                    Espacio reservado para la captura<br>
                    <b style="color:#5a6b5a;">{{ $archivo }}</b>
                </td>
            </tr>
        </table>
        <div class="pie-captura">{{ $pie ?? 'Captura pendiente' }}</div>
    @endif
</div>
