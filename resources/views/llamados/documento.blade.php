<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Llamado de atención N.° {{ $llamado->id_llamado }} - GEVLA SENA</title>
    <link rel="icon" type="image/png" href="https://oficinavirtualderadicacion.sena.edu.co/oficinavirtual/Resources/logoSenaNaranja.png">
    {{-- Maquetado con tablas y estilos simples: la misma vista sirve para el
         navegador (modo imprimir) y para Dompdf (PDF real descargable). --}}
    <style>
        body { font-family: 'DejaVu Sans', 'Segoe UI', Calibri, Arial, sans-serif; color: #1e293b; margin: 34px; font-size: 12px; line-height: 1.6; }
        .noprint { margin-bottom: 18px; }
        .btn { background: #39A900; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 13px; }
        table.encabezado { width: 100%; border-collapse: collapse; }
        table.encabezado td { border: 1px solid #1e293b; padding: 8px 14px; vertical-align: middle; }
        table.encabezado td.logo { width: 110px; text-align: center; }
        .enc-titulo { font-weight: 700; font-size: 12px; }
        .enc-datos { font-size: 10px; line-height: 1.6; }
        .campo { margin: 12px 0 0; }
        .campo strong { text-transform: uppercase; }
        .motivo { margin-top: 20px; }
        .motivo .etiqueta { font-weight: 700; font-style: italic; text-decoration: underline; }
        .motivo p { margin: 8px 0; }
        .articulo .lit { font-weight: 700; text-decoration: underline; }
        .aviso { margin-top: 16px; }
        table.firmas { width: 100%; border-collapse: collapse; margin-top: 52px; }
        table.firmas td { width: 50%; vertical-align: bottom; padding: 0 22px 0 0; }
        .firma-imagen { height: 70px; }
        .firma-imagen img { height: 62px; }
        .firma-linea { border-top: 2px solid #1e293b; margin-top: 4px; padding-top: 4px; font-weight: 700; }
        .firma-meta { font-size: 10px; color: #475569; margin: 2px 0 0; }
        .pie { margin-top: 30px; font-size: 9px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 8px; }
        @media print { .noprint { display: none !important; } body { margin: 0; } }
    </style>
</head>
<body>
    @if($imprimir ?? false)
        <div class="noprint">
            <button class="btn" onclick="window.print()">🖨 Imprimir / Guardar como PDF</button>
        </div>
    @endif

    @php
        $rutaLogo = public_path('img/logo-sena-verde.png');
        $logoBase64 = is_file($rutaLogo) ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($rutaLogo)) : null;
        $usuarioAprendiz = $llamado->aprendiz?->usuario;
        $esEscrito = $llamado->tipo_llamado === \App\Models\LlamadoAtencion::TIPO_LLAMADO_ESCRITO;
    @endphp

    {{-- Encabezado institucional (formato F002-008-25 V01) --}}
    <table class="encabezado">
        <tr>
            <td class="logo">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="SENA" width="60">
                @else
                    <strong>SENA</strong>
                @endif
            </td>
            <td>
                <div class="enc-titulo">LLAMADO DE ATENCIÓN</div>
                <div class="enc-datos">
                    F002-008-25 / Versión 01<br>
                    Proceso: Ejecución de la formación<br>
                    Procedimiento: Gestión de proyectos formativos
                </div>
            </td>
        </tr>
    </table>

    <p class="campo"><strong>Ciudad y Fecha:</strong> {{ \Carbon\Carbon::parse($llamado->fecha_llamado)->translatedFormat('d \d\e F \d\e Y') }}</p>
    <p class="campo"><strong>Centro de Formación:</strong> Complejo Tecnológico Agroindustrial, Pecuario y Turístico</p>
    <p class="campo"><strong>Nombre del Aprendiz:</strong> {{ trim(($usuarioAprendiz->nombres ?? '') . ' ' . ($usuarioAprendiz->apellidos ?? '')) ?: '—' }}</p>
    <p class="campo"><strong>Identificación:</strong> {{ $usuarioAprendiz->numero_documento ?? '—' }}</p>
    <p class="campo"><strong>Programa de Formación:</strong> {{ $ficha?->programa?->nombre_programa ?? '—' }}</p>
    <p class="campo"><strong>N.º Ficha de Caracterización:</strong> {{ $ficha?->numero_ficha ?? '—' }}</p>

    <div class="motivo">
        <p class="etiqueta">MOTIVO:</p>
        <p style="text-decoration: underline;">{{ $llamado->asunto }}</p>
        <p>{{ $llamado->descripcion_hechos }}</p>

        @if($llamado->articulo)
            <div class="articulo">
                <p><strong>Reglamento del Aprendiz SENA (Acuerdo 09 de 2024):</strong></p>
                <p><span class="lit">{{ $llamado->articulo->numero_articulo }}:</span> {{ $llamado->articulo->titulo }}</p>
            </div>
        @endif
    </div>

    <p class="aviso">
        Este llamado de atención verbal {{ $esEscrito ? '____' : '_X__' }} Escrito {{ $esEscrito ? '_X__' : '____' }} espera de su parte un cambio
        radical en este aspecto y le recuerda que de continuar con esta situación se le puede condicionar o cancelar el
        registro de matrícula.
    </p>

    {{-- Bloques de firma: instructor y aprendiz (como el formato oficial) y
         coordinación cuando el flujo lo requirió. Cada firma sale con su
         imagen incrustada y la fecha/hora exactas en que se firmó. --}}
    <table class="firmas">
        <tr>
            @foreach([\App\Models\FirmaLlamado::ROL_INSTRUCTOR => 'Instructor', \App\Models\FirmaLlamado::ROL_APRENDIZ => 'Aprendiz'] as $rol => $etiqueta)
                @php $f = $firmas[$rol] ?? ['registro' => null, 'imagen' => null]; @endphp
                <td>
                    <div class="firma-imagen">
                        @if($f['registro'] && $f['imagen'])
                            <img src="{{ $f['imagen'] }}" alt="Firma {{ $etiqueta }}">
                        @endif
                    </div>
                    <div class="firma-linea">{{ $etiqueta }}</div>
                    @if($f['registro'])
                        <p class="firma-meta">
                            {{ trim(($f['registro']->usuario->nombres ?? '') . ' ' . ($f['registro']->usuario->apellidos ?? '')) }}<br>
                            Firmado el {{ $f['registro']->fecha_firma->translatedFormat('d \d\e F \d\e Y, h:i a') }}
                        </p>
                    @else
                        <p class="firma-meta">Pendiente de firma</p>
                    @endif
                </td>
            @endforeach
        </tr>
    </table>

    @php $fc = $firmas[\App\Models\FirmaLlamado::ROL_COORDINADOR] ?? ['registro' => null, 'imagen' => null]; @endphp
    @if($fc['registro'])
        <table class="firmas" style="margin-top: 38px;">
            <tr>
                <td>
                    <div class="firma-imagen">
                        @if($fc['imagen'])
                            <img src="{{ $fc['imagen'] }}" alt="Firma Coordinación">
                        @endif
                    </div>
                    <div class="firma-linea">Coordinación</div>
                    <p class="firma-meta">
                        {{ trim(($fc['registro']->usuario->nombres ?? '') . ' ' . ($fc['registro']->usuario->apellidos ?? '')) }}<br>
                        Firmado el {{ $fc['registro']->fecha_firma->translatedFormat('d \d\e F \d\e Y, h:i a') }}
                    </p>
                </td>
                <td></td>
            </tr>
        </table>
    @endif

    <div class="pie">
        Documento generado automáticamente por GEVLA a partir del llamado de atención N.° {{ $llamado->id_llamado }} ·
        Las firmas incrustadas corresponden a las registradas por cada usuario en su perfil y quedan trazadas con fecha y hora en el sistema.
    </div>

    @if($imprimir ?? false)
        <script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 350); });</script>
    @endif
</body>
</html>
