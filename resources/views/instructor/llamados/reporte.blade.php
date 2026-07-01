<!DOCTYPE html>
<html lang="es" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="UTF-8">
    <title>Mis Reportes - GEVLA SENA</title>
    <style>
        body { font-family: 'Segoe UI', Calibri, Arial, sans-serif; color: #1e293b; margin: 28px; }
        .encabezado { border-bottom: 3px solid #39A900; padding-bottom: 12px; margin-bottom: 18px; }
        .marca { color: #39A900; font-size: 22px; font-weight: 800; letter-spacing: 1px; }
        h1 { font-size: 18px; margin: 8px 0 4px; color: #0f172a; }
        .meta { font-size: 12px; color: #64748b; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 12px; }
        th { background: #39A900; color: #ffffff; text-align: left; padding: 9px 10px; border: 1px solid #2f8b00; }
        td { border: 1px solid #d7dfd2; padding: 8px 10px; vertical-align: top; }
        tr:nth-child(even) td { background: #f6faf3; }
        .estado { font-weight: 700; text-transform: capitalize; }
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

    <div class="encabezado">
        <div class="marca">GEVLA · SENA</div>
        <h1>Reporte de llamados de atención</h1>
        <div class="meta">
            Instructor: <strong>{{ $nombreInstructor !== '' ? $nombreInstructor : 'No registrado' }}</strong><br>
            Generado: {{ \Carbon\Carbon::now('America/Bogota')->locale('es')->translatedFormat('d \d\e F \d\e Y, h:i A') }}<br>
            Total de reportes: {{ $llamados->count() }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:48px;">#</th>
                <th style="width:90px;">Fecha</th>
                <th>Aprendiz</th>
                <th>Asunto</th>
                <th style="width:110px;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($llamados as $ll)
                <tr>
                    <td>{{ $ll->id_llamado }}</td>
                    <td>{{ \Carbon\Carbon::parse($ll->fecha_llamado)->format('d/m/Y') }}</td>
                    <td>{{ optional($ll->aprendiz->usuario)->nombres }} {{ optional($ll->aprendiz->usuario)->apellidos }}</td>
                    <td>{{ $ll->asunto }}</td>
                    <td class="estado">{{ str_replace('_', ' ', $ll->estado_llamado) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center; color:#94a3b8;">Sin llamados registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pie">Documento generado automáticamente por GEVLA — Sistema de Gestión de Llamados y Actas · SENA</div>

    @if($imprimir ?? false)
        <script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 350); });</script>
    @endif
</body>
</html>
