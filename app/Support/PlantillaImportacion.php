<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Ficha;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Genera las plantillas de Excel para la carga masiva de usuarios
 * (aprendices, instructores y coordinadores).
 *
 * Las plantillas incluyen validaciones propias de Excel para reducir errores
 * antes de importar: el Tipo de documento es una lista desplegable con las
 * únicas opciones permitidas, y en la plantilla de aprendices la Ficha también
 * es una lista desplegable con las fichas activas del sistema.
 */
class PlantillaImportacion
{
    /** Etiquetas visibles del tipo de documento (las que ve el usuario en Excel). */
    public const TIPOS_DOCUMENTO = [
        'CC'  => 'Cédula de ciudadanía',
        'TI'  => 'Tarjeta de identidad',
        'CE'  => 'Cédula de extranjería',
        'PEP' => 'PEP',
        'PPT' => 'PPT (Permiso por Protección Temporal)',
        'PA'  => 'Pasaporte',
    ];

    /** Encabezados por tipo de plantilla. */
    public const ENCABEZADOS = [
        'aprendices'    => ['Nombres', 'Apellidos', 'Tipo de documento', 'Número de documento', 'Correo electrónico', 'Teléfono (Opcional)', 'Contraseña', 'Ficha'],
        'instructores'  => ['Nombres', 'Apellidos', 'Tipo de documento', 'Número de documento', 'Correo electrónico', 'Teléfono (Opcional)', 'Contraseña'],
        'coordinadores' => ['Nombres', 'Apellidos', 'Tipo de documento', 'Número de documento', 'Correo electrónico', 'Teléfono (Opcional)', 'Contraseña'],
    ];

    /** Filas con validación desplegable disponibles en la plantilla. */
    private const FILAS_VALIDADAS = 500;

    /**
     * Construye la plantilla y devuelve el binario .xlsx.
     *
     * @param  Collection<int, Ficha>|null  $fichas  Fichas a ofrecer en el
     *         desplegable (solo aprendices). Si es null se usan las activas.
     */
    public static function generar(string $tipo, ?Collection $fichas = null): string
    {
        $encabezados = self::ENCABEZADOS[$tipo] ?? self::ENCABEZADOS['aprendices'];

        $libro = new Spreadsheet();
        $hoja = $libro->getActiveSheet();
        $hoja->setTitle('Datos');

        // Encabezados con el verde institucional.
        foreach ($encabezados as $i => $titulo) {
            $celda = $hoja->getCell([$i + 1, 1]);
            $celda->setValue($titulo);
            $hoja->getColumnDimensionByColumn($i + 1)->setWidth(strlen($titulo) > 18 ? 34 : 24);
        }
        $rango = 'A1:' . $hoja->getCell([count($encabezados), 1])->getCoordinate();
        $hoja->getStyle($rango)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $hoja->getStyle($rango)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF39A900');
        $hoja->getStyle($rango)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $hoja->freezePane('A2');

        // Hoja oculta con los catálogos para los desplegables.
        $listas = $libro->createSheet();
        $listas->setTitle('Listas');
        foreach (array_values(self::TIPOS_DOCUMENTO) as $i => $etiqueta) {
            $listas->getCell([1, $i + 1])->setValue($etiqueta);
        }

        // Marca del tipo de plantilla: permite detectar en la importación si
        // se está usando la plantilla de un rol en la carga masiva de otro.
        $listas->getCell([4, 1])->setValue('plantilla:' . $tipo);

        $colTipoDoc = array_search('Tipo de documento', $encabezados, true) + 1;
        self::desplegable($hoja, $colTipoDoc, "'Listas'!\$A\$1:\$A\$" . count(self::TIPOS_DOCUMENTO), 'Selecciona un tipo de documento de la lista.');

        // Desplegable de fichas activas (solo plantilla de aprendices).
        $colFicha = array_search('Ficha', $encabezados, true);
        if ($colFicha !== false) {
            $fichas ??= Ficha::where('estado_ficha', 'en_ejecucion')->orderBy('numero_ficha')->get();
            $numeros = $fichas->pluck('numero_ficha')->values();

            if ($numeros->isNotEmpty()) {
                foreach ($numeros as $i => $numero) {
                    $listas->getCell([2, $i + 1])->setValueExplicit((string) $numero, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
                self::desplegable($hoja, $colFicha + 1, "'Listas'!\$B\$1:\$B\$" . $numeros->count(), 'Selecciona el número de una ficha activa.');
            }
        }

        $listas->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
        $libro->setActiveSheetIndex(0);

        ob_start();
        (new Xlsx($libro))->save('php://output');

        return (string) ob_get_clean();
    }

    /**
     * Aplica una validación de lista desplegable a toda una columna.
     */
    private static function desplegable(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $hoja, int $columna, string $formula, string $mensaje): void
    {
        $letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna);

        $validacion = new DataValidation();
        $validacion->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank(true)
            ->setShowDropDown(true)
            ->setShowErrorMessage(true)
            ->setErrorTitle('Valor no permitido')
            ->setError($mensaje)
            ->setFormula1($formula);

        $hoja->setDataValidation($letra . '2:' . $letra . (self::FILAS_VALIDADAS + 1), $validacion);
    }
}
