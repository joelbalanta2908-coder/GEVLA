<!DOCTYPE html>
<html lang="es" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }} - GEVLA SENA</title>
    <link rel="icon" type="image/png" href="https://oficinavirtualderadicacion.sena.edu.co/oficinavirtual/Resources/logoSenaNaranja.png">
    {{-- La tabla y sus celdas usan SOLO estilos inline: Word no interpreta bien
         los selectores CSS (th, td, nth-child) y deformaba el documento. --}}
    <style>
        body { font-family: 'Segoe UI', Calibri, Arial, sans-serif; color: #1e293b; margin: 28px; }
        .encabezado { border-bottom: 3px solid #39A900; padding-bottom: 12px; margin-bottom: 18px; }
        .marca { color: #39A900; font-size: 26px; font-weight: 800; letter-spacing: 2px; }
        h1 { font-size: 18px; margin: 8px 0 4px; color: #0f172a; }
        .meta { font-size: 12px; color: #64748b; line-height: 1.6; }
        .pie { margin-top: 22px; font-size: 11px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .barra { margin-bottom: 18px; }
        .btn { background: #39A900; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 13px; }
        @media print { .noprint { display: none !important; } body { margin: 0; } }
    </style>
</head>
<body>
    @if($imprimir ?? false)
        <div class="barra noprint">
            <button class="btn" onclick="window.print()">🖨 Imprimir / Guardar como PDF</button>
        </div>
    @endif

    @php
        // El logo solo se incrusta en la vista de navegador (imprimir/PDF):
        // Word y Excel no muestran imágenes en base64 y saldría un icono roto.
        $rutaLogo = public_path('img/logo-sena-verde.png');
        $logoBase64 = ($imprimir ?? false) && is_file($rutaLogo)
            ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($rutaLogo))
            : null;
    @endphp
    <div class="encabezado">
        <table border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin:0;">
            <tr>
                @if($logoBase64)
                    <td style="border:none; padding:0 10px 0 0; vertical-align:middle;">
                        <img src="{{ $logoBase64 }}" alt="SENA" width="58" style="display:block;">
                    </td>
                @endif
                <td style="border:none; padding:0; vertical-align:middle;">
                    <div class="marca">GEVLA</div>
                    <div style="font-size:11px; color:#64748b; letter-spacing:3px; font-weight:700;">SENA &middot; GESTI&Oacute;N DISCIPLINARIA Y FORMATIVA</div>
                </td>
            </tr>
        </table>
        <h1>{{ $titulo }}</h1>
        <div class="meta">
            @foreach($meta as $m)
                <strong>{{ $m['label'] }}:</strong> {{ $m['value'] }}<br>
            @endforeach
        </div>
    </div>

    <table border="1" cellspacing="0" cellpadding="6" style="border-collapse:collapse; width:100%; font-size:12px; border:1px solid #d7dfd2;">
        <thead>
            <tr>
                @foreach($encabezados as $h)
                    <th style="background:#39A900; color:#ffffff; text-align:left; padding:8px 9px; border:1px solid #2f8b00; font-size:12px;">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($filas as $fila)
                <tr>
                    @foreach($fila as $celda)
                        <td style="border:1px solid #d7dfd2; padding:7px 9px; vertical-align:top; font-size:12px;{{ $loop->parent->iteration % 2 === 0 ? ' background:#f6faf3;' : '' }}">{{ $celda }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($encabezados) }}" style="border:1px solid #d7dfd2; padding:10px; text-align:center; color:#94a3b8;">Sin registros.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pie">Documento generado automáticamente por GEVLA — Sistema de Gestión de Llamados y Actas · SENA</div>

    @if($imprimir ?? false)
        <script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 350); });</script>
    @endif
</body>
</html>
