{{-- Estilos compartidos de los manuales PDF (compatibles con Dompdf:
     tablas, bloques y colores planos; sin flexbox ni grid).

     Densidad: el contenido FLUYE de forma continua (no se fuerza un salto de
     página por capítulo) para aprovechar cada hoja y evitar páginas casi
     vacías. Solo la portada y el índice ocupan su propia página. Los bloques
     (tablas, cajas, pasos, diagramas, capturas) evitan partirse entre páginas
     y los títulos nunca quedan huérfanos al final de una hoja. --}}
<style>
    @page { margin: 62px 48px 60px 48px; }
    * { box-sizing: border-box; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 9.6px; color: #24312a; margin: 0; line-height: 1.5; }

    /* ---------- Portada ---------- */
    .portada { page-break-after: always; text-align: center; }
    .portada-banda { background-color: #39A900; height: 10px; width: 100%; }
    .portada-logo { margin-top: 90px; }
    .portada-logo img { height: 110px; }
    .portada-kicker { margin-top: 46px; font-size: 11px; letter-spacing: 4px; color: #6b7a70; text-transform: uppercase; }
    .portada-titulo { margin-top: 8px; font-size: 40px; font-weight: bold; color: #00324D; }
    .portada-sub { margin-top: 4px; font-size: 17px; color: #39A900; font-weight: bold; }
    .portada-desc { margin: 26px auto 0; width: 430px; font-size: 10.5px; color: #55655c; }
    .portada-meta { margin: 58px auto 0; width: 360px; border-top: 2px solid #39A900; padding-top: 12px; font-size: 9.5px; color: #55655c; text-align: left; }
    .portada-meta b { color: #00324D; }

    /* ---------- Índice ---------- */
    .indice { page-break-after: always; }
    .indice-titulo { font-size: 18px; color: #00324D; border-bottom: 2.5px solid #39A900; padding-bottom: 6px; margin-bottom: 12px; }
    table.indice-tabla { width: 100%; border-collapse: collapse; }
    table.indice-tabla td { padding: 4px 6px; border-bottom: 0.8px dotted #d5ddd4; font-size: 10px; }
    table.indice-tabla td.n { width: 30px; color: #39A900; font-weight: bold; }

    /* ---------- Títulos (flujo continuo, sin salto por capítulo) ---------- */
    h1.capitulo { font-size: 16px; color: #00324D; border-bottom: 2.5px solid #39A900; padding-bottom: 5px; margin: 22px 0 9px; page-break-after: avoid; }
    h1.capitulo:first-of-type { margin-top: 0; }
    h1.capitulo .num { color: #39A900; }
    h2 { font-size: 11.5px; color: #1f5a16; margin: 13px 0 5px; page-break-after: avoid; }
    h3 { font-size: 10.3px; color: #00324D; margin: 10px 0 4px; page-break-after: avoid; }
    p { margin: 0 0 7px; text-align: justify; }
    ul, ol { margin: 0 0 8px 4px; padding-left: 16px; }
    li { margin-bottom: 2.5px; text-align: justify; }
    b, strong { color: #00324D; }
    .verde { color: #39A900; }

    /* ---------- Tablas de contenido ---------- */
    table.datos { width: 100%; border-collapse: collapse; margin: 5px 0 11px; page-break-inside: avoid; }
    table.datos th { background-color: #39A900; color: #ffffff; font-size: 8.8px; text-transform: uppercase; letter-spacing: 0.6px; padding: 5px 7px; text-align: left; }
    table.datos td { border: 0.8px solid #dfe6dd; padding: 5px 7px; font-size: 9px; vertical-align: top; }
    table.datos tr.alt td { background-color: #f6faf4; }
    table.datos td.clave { font-weight: bold; color: #00324D; width: 32%; background-color: #f6faf4; }
    table.datos tr { page-break-inside: avoid; }

    /* ---------- Cajas ---------- */
    .caja { border: 1px solid #dfe6dd; border-left: 4px solid #39A900; background-color: #f6faf4; padding: 8px 10px; margin: 8px 0 11px; font-size: 9px; page-break-inside: avoid; }
    .caja b { color: #1f5a16; }
    .caja-alerta { border-left-color: #d97706; background-color: #fffbeb; }
    .caja-alerta b { color: #92400e; }
    .caja-info { border-left-color: #00324D; background-color: #eef4f8; }
    .caja-info b { color: #00324D; }

    /* ---------- Pasos ---------- */
    table.pasos { width: 100%; border-collapse: collapse; margin: 5px 0 11px; page-break-inside: avoid; }
    table.pasos td { border-bottom: 0.8px solid #eef1e8; padding: 5px 7px; font-size: 9.2px; vertical-align: top; }
    table.pasos td.paso { width: 26px; color: #ffffff; background-color: #39A900; font-weight: bold; text-align: center; }
    table.pasos tr { page-break-inside: avoid; }

    /* ---------- Capturas ---------- */
    .captura { margin: 9px 0 12px; text-align: center; page-break-inside: avoid; }
    .captura img { max-width: 100%; border: 1px solid #d5ddd4; }
    .captura .pie-captura { margin-top: 4px; font-size: 8px; color: #6b7a70; font-style: italic; }

    /* ---------- Diagramas (bloques) ---------- */
    table.diagrama { width: 100%; border-collapse: separate; border-spacing: 4px; margin: 8px 0 6px; page-break-inside: avoid; }
    table.diagrama td { border: 1.2px solid #39A900; background-color: #f6faf4; text-align: center; padding: 8px 6px; font-size: 8.8px; color: #1f5a16; font-weight: bold; }
    table.diagrama td.flecha { border: none; background-color: transparent; color: #6b7a70; font-size: 12px; width: 22px; }
    table.diagrama td.neutro { border-color: #00324D; background-color: #eef4f8; color: #00324D; }
    .pie-diagrama { text-align: center; font-size: 8px; color: #6b7a70; font-style: italic; margin: 0 0 12px; }

    code, .codigo { font-family: "DejaVu Sans Mono", monospace; font-size: 8.4px; background-color: #f1f5f0; border: 0.8px solid #e0e7de; padding: 0.5px 3px; }
    .bloque-codigo { font-family: "DejaVu Sans Mono", monospace; font-size: 8.2px; background-color: #0f2419; color: #d7f2cd; padding: 9px 11px; margin: 6px 0 12px; line-height: 1.6; page-break-inside: avoid; }
</style>
