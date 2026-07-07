<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Genera el reporte como archivo .xlsx REAL (Office Open XML) usando solo
 * ZipArchive, sin librerías externas. Al ser el formato nativo de Excel:
 * icono correcto, doble clic abre Excel y ninguna advertencia de formato.
 *
 * Estructura mínima del paquete: [Content_Types].xml, _rels/.rels,
 * xl/workbook.xml, xl/_rels/workbook.xml.rels, xl/styles.xml y
 * xl/worksheets/sheet1.xml (con cadenas inline para no requerir sharedStrings).
 */
class ReporteExcel
{
    /**
     * @param  array<int, array{label: string, value: string}>  $meta
     * @param  array<int, string>                               $encabezados
     * @param  array<int, array<int, mixed>>                    $filas
     */
    public static function generar(string $titulo, array $meta, array $encabezados, array $filas): string
    {
        $ruta = tempnam(sys_get_temp_dir(), 'gevla_xlsx_');
        if ($ruta === false) {
            throw new RuntimeException('No fue posible crear el archivo temporal del reporte.');
        }

        $zip = new ZipArchive();
        if ($zip->open($ruta, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No fue posible generar el paquete del reporte.');
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypes());
        $zip->addFromString('_rels/.rels', self::relsRaiz());
        $zip->addFromString('xl/workbook.xml', self::workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::relsWorkbook());
        $zip->addFromString('xl/styles.xml', self::estilos());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::hoja($titulo, $meta, $encabezados, $filas));
        $zip->close();

        $binario = (string) file_get_contents($ruta);
        @unlink($ruta);

        return $binario;
    }

    private static function contentTypes(): string
    {
        return <<<'XML'
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
            <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
            <Default Extension="xml" ContentType="application/xml"/>
            <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
            <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
            <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
        </Types>
        XML;
    }

    private static function relsRaiz(): string
    {
        return <<<'XML'
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
            <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
        </Relationships>
        XML;
    }

    private static function workbook(): string
    {
        return <<<'XML'
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
            <sheets>
                <sheet name="Reporte" sheetId="1" r:id="rId1"/>
            </sheets>
        </workbook>
        XML;
    }

    private static function relsWorkbook(): string
    {
        return <<<'XML'
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
            <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
            <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
        </Relationships>
        XML;
    }

    /**
     * Estilos del libro. Índices de cellXfs:
     *  0 normal · 1 marca · 2 submarca · 3 título · 4 etiqueta meta ·
     *  5 valor meta · 6 encabezado de tabla · 7 celda · 8 celda alterna.
     */
    private static function estilos(): string
    {
        return <<<'XML'
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
            <fonts count="7">
                <font><sz val="11"/><name val="Calibri"/></font>
                <font><b/><sz val="20"/><color rgb="FF39A900"/><name val="Calibri"/></font>
                <font><b/><sz val="8"/><color rgb="FF64748B"/><name val="Calibri"/></font>
                <font><b/><sz val="12"/><color rgb="FF0F172A"/><name val="Calibri"/></font>
                <font><b/><sz val="10"/><color rgb="FF334155"/><name val="Calibri"/></font>
                <font><sz val="10"/><color rgb="FF475569"/><name val="Calibri"/></font>
                <font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
            </fonts>
            <fills count="4">
                <fill><patternFill patternType="none"/></fill>
                <fill><patternFill patternType="gray125"/></fill>
                <fill><patternFill patternType="solid"><fgColor rgb="FF39A900"/><bgColor rgb="FF39A900"/></patternFill></fill>
                <fill><patternFill patternType="solid"><fgColor rgb="FFF6FAF3"/><bgColor rgb="FFF6FAF3"/></patternFill></fill>
            </fills>
            <borders count="3">
                <border><left/><right/><top/><bottom/><diagonal/></border>
                <border>
                    <left style="thin"><color rgb="FFD7DFD2"/></left>
                    <right style="thin"><color rgb="FFD7DFD2"/></right>
                    <top style="thin"><color rgb="FFD7DFD2"/></top>
                    <bottom style="thin"><color rgb="FFD7DFD2"/></bottom>
                    <diagonal/>
                </border>
                <border>
                    <left style="thin"><color rgb="FF2F8B00"/></left>
                    <right style="thin"><color rgb="FF2F8B00"/></right>
                    <top style="thin"><color rgb="FF2F8B00"/></top>
                    <bottom style="thin"><color rgb="FF2F8B00"/></bottom>
                    <diagonal/>
                </border>
            </borders>
            <cellStyleXfs count="1">
                <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
            </cellStyleXfs>
            <cellXfs count="9">
                <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
                <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>
                <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>
                <xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>
                <xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1"/>
                <xf numFmtId="0" fontId="5" fillId="0" borderId="0" xfId="0" applyFont="1"/>
                <xf numFmtId="0" fontId="6" fillId="2" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
                    <alignment vertical="center" wrapText="1"/>
                </xf>
                <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">
                    <alignment vertical="top" wrapText="1"/>
                </xf>
                <xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1">
                    <alignment vertical="top" wrapText="1"/>
                </xf>
            </cellXfs>
        </styleSheet>
        XML;
    }

    /**
     * Hoja con la marca, los metadatos y la tabla del reporte.
     *
     * @param  array<int, array{label: string, value: string}>  $meta
     * @param  array<int, string>                               $encabezados
     * @param  array<int, array<int, mixed>>                    $filas
     */
    private static function hoja(string $titulo, array $meta, array $encabezados, array $filas): string
    {
        $columnas = count($encabezados);
        $ultimaLetra = self::letra($columnas - 1);

        // Anchos: primera columna angosta, últimas dos más anchas (asunto/estado).
        $cols = '<cols>';
        for ($i = 0; $i < $columnas; $i++) {
            $ancho = $i === 0 ? 6 : ($i >= $columnas - 2 ? 26 : 17);
            $n = $i + 1;
            $cols .= "<col min=\"{$n}\" max=\"{$n}\" width=\"{$ancho}\" customWidth=\"1\"/>";
        }
        $cols .= '</cols>';

        $filaN = 0;
        $xml = '';
        $merges = [];

        // Marca institucional y título del reporte (celdas combinadas).
        $xml .= self::filaTexto(++$filaN, [[0, 'GEVLA', 1]], 26);
        $merges[] = 'A' . $filaN . ':' . $ultimaLetra . $filaN;
        $xml .= self::filaTexto(++$filaN, [[0, 'SENA · GESTIÓN DISCIPLINARIA Y FORMATIVA', 2]]);
        $merges[] = 'A' . $filaN . ':' . $ultimaLetra . $filaN;
        $xml .= self::filaTexto(++$filaN, [[0, $titulo, 3]], 20);
        $merges[] = 'A' . $filaN . ':' . $ultimaLetra . $filaN;

        // Metadatos (etiqueta en negrilla + valor combinado hasta el final).
        foreach ($meta as $m) {
            $xml .= self::filaTexto(++$filaN, [[0, $m['label'] . ':', 4], [1, $m['value'], 5]]);
            if ($columnas > 2) {
                $merges[] = 'B' . $filaN . ':' . $ultimaLetra . $filaN;
            }
        }

        $xml .= '<row r="' . (++$filaN) . '"/>';

        // Encabezados de la tabla.
        $celdas = [];
        foreach ($encabezados as $i => $h) {
            $celdas[] = [$i, $h, 6];
        }
        $xml .= self::filaTexto(++$filaN, $celdas, 22);

        // Datos con sombreado alterno.
        foreach ($filas as $indice => $fila) {
            $estilo = $indice % 2 === 1 ? 8 : 7;
            $celdas = [];
            foreach (array_values($fila) as $i => $valor) {
                $celdas[] = [$i, $valor, $estilo];
            }
            $xml .= self::filaTexto(++$filaN, $celdas);
        }

        if ($filas === []) {
            $xml .= self::filaTexto(++$filaN, [[0, 'Sin registros.', 7]]);
            $merges[] = 'A' . $filaN . ':' . $ultimaLetra . $filaN;
        }

        $mergeXml = '<mergeCells count="' . count($merges) . '">';
        foreach ($merges as $ref) {
            $mergeXml .= '<mergeCell ref="' . $ref . '"/>';
        }
        $mergeXml .= '</mergeCells>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . $cols
            . '<sheetData>' . $xml . '</sheetData>'
            . $mergeXml
            . '<pageSetup orientation="landscape"/>'
            . '</worksheet>';
    }

    /**
     * Construye una fila. Cada celda es [índiceColumna, valor, índiceEstilo];
     * los valores numéricos se escriben como números y el resto como texto.
     *
     * @param  array<int, array{0: int, 1: mixed, 2: int}>  $celdas
     */
    private static function filaTexto(int $fila, array $celdas, ?int $alto = null): string
    {
        $atributos = 'r="' . $fila . '"' . ($alto ? ' ht="' . $alto . '" customHeight="1"' : '');
        $xml = "<row {$atributos}>";

        foreach ($celdas as [$col, $valor, $estilo]) {
            $ref = self::letra($col) . $fila;

            if (is_int($valor) || is_float($valor) || (is_string($valor) && preg_match('/^\d+(\.\d+)?$/', $valor) === 1)) {
                $xml .= "<c r=\"{$ref}\" s=\"{$estilo}\"><v>{$valor}</v></c>";
            } else {
                $texto = htmlspecialchars((string) $valor, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $xml .= "<c r=\"{$ref}\" s=\"{$estilo}\" t=\"inlineStr\"><is><t xml:space=\"preserve\">{$texto}</t></is></c>";
            }
        }

        return $xml . '</row>';
    }

    /**
     * Letra de columna estilo Excel (0 → A, 25 → Z, 26 → AA...).
     */
    private static function letra(int $indice): string
    {
        $letra = '';
        $n = $indice + 1;

        while ($n > 0) {
            $resto = ($n - 1) % 26;
            $letra = chr(65 + $resto) . $letra;
            $n = intdiv($n - 1, 26);
        }

        return $letra;
    }
}
