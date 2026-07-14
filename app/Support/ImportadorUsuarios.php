<?php

declare(strict_types=1);

namespace App\Support;

use App\Http\Controllers\Concerns\CreaUsuarios;
use App\Models\Aprendiz;
use App\Models\Coordinacion;
use App\Models\Ficha;
use App\Models\Instructor;
use App\Models\Matricula;
use App\Models\Usuario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Carga masiva de usuarios desde Excel (aprendices, instructores y
 * coordinadores).
 *
 * Reglas de oro:
 *  · Se valida ABSOLUTAMENTE TODO el archivo antes de guardar nada.
 *  · La importación es atómica (transacción): si hay un solo error, no se
 *    registra ningún usuario y la base queda exactamente igual.
 *  · El alta reutiliza la MISMA lógica del registro individual (trait
 *    CreaUsuarios): mismo hash de contraseña, mismo rol, mismos perfiles.
 */
class ImportadorUsuarios
{
    use CreaUsuarios;

    /**
     * Importa el archivo y devuelve el resultado.
     *
     * @param  string  $tipo  aprendices | instructores | coordinadores
     * @param  array<int, int>|null  $fichasPermitidas  Si se indica (instructor),
     *         solo se permite matricular en esas fichas.
     * @return array{exito: bool, creados: int, errores: array<int, array{fila: int|string, campo: string, mensaje: string}>}
     */
    public function importar(string $tipo, UploadedFile $archivo, ?array $fichasPermitidas = null): array
    {
        // --- Lectura del archivo. Solo lectores de Excel reales (Xlsx/Xls):
        // así un archivo corrupto o disfrazado se rechaza con un error claro. ---
        try {
            $lector = IOFactory::createReader(strtolower((string) $archivo->getClientOriginalExtension()) === 'xls' ? 'Xls' : 'Xlsx');
            $lector->setReadDataOnly(true);
            $libro = $lector->load($archivo->getRealPath());
            $hoja = $libro->getSheet(0);
            $filas = $hoja->toArray(null, true, false, false); // valores crudos, sin formato
        } catch (\Throwable $e) {
            return $this->fallo([['fila' => '—', 'campo' => 'Archivo', 'mensaje' => 'El archivo no se pudo leer: está corrupto o no es un Excel válido.']]);
        }

        if (count($filas) < 1) {
            return $this->fallo([['fila' => '—', 'campo' => 'Archivo', 'mensaje' => 'El archivo está vacío.']]);
        }

        // --- La plantilla debe ser la del MISMO rol que se está importando:
        // la marca oculta de la plantilla oficial delata si se sube, por
        // ejemplo, la plantilla de aprendices en la carga de instructores. ---
        $marca = null;
        try {
            if ($libro->sheetNameExists('Listas')) {
                $marca = (string) $libro->getSheetByName('Listas')->getCell('D1')->getValue();
            }
        } catch (\Throwable $e) {
            $marca = null;
        }

        if ($marca !== null && str_starts_with($marca, 'plantilla:') && $marca !== 'plantilla:' . $tipo) {
            $otro = substr($marca, strlen('plantilla:'));

            return $this->fallo([[
                'fila' => '—',
                'campo' => 'Archivo',
                'mensaje' => 'Este archivo es la plantilla de ' . $otro . ' y estás en la carga masiva de ' . $tipo . '. Descarga y usa la plantilla de ' . $tipo . '.',
            ]]);
        }

        // --- Encabezados: deben ser exactamente los de la plantilla, sin
        // columnas adicionales (otra señal de plantilla equivocada). ---
        $esperados = PlantillaImportacion::ENCABEZADOS[$tipo];
        $filaEncabezados = (array) $filas[0];
        $recibidos = array_map(fn ($v) => $this->normalizarEncabezado((string) $v), array_slice($filaEncabezados, 0, count($esperados)));
        $esperadosNorm = array_map(fn ($v) => $this->normalizarEncabezado($v), $esperados);
        $columnasExtra = array_filter(array_slice($filaEncabezados, count($esperados)), fn ($v) => trim((string) $v) !== '');

        if ($recibidos !== $esperadosNorm || $columnasExtra !== []) {
            return $this->fallo([[
                'fila' => 1,
                'campo' => 'Encabezados',
                'mensaje' => 'Las columnas no coinciden con la plantilla de ' . $tipo . '. Se esperaban exactamente: ' . implode(', ', $esperados) . '. Descarga la plantilla oficial de ' . $tipo . ' y vuelve a intentarlo.',
            ]]);
        }

        // --- Normalización de filas (se ignoran las completamente vacías). ---
        $esAprendiz = $tipo === 'aprendices';
        $registros = [];
        foreach (array_slice($filas, 1, null, true) as $indice => $fila) {
            $numeroFila = $indice + 1; // número real en Excel

            $valores = array_map(fn ($v) => $this->aTexto($v), array_slice(array_pad((array) $fila, count($esperados), null), 0, count($esperados)));
            if (implode('', $valores) === '') {
                continue; // fila completamente vacía: no genera registro
            }

            $registros[] = [
                'fila'      => $numeroFila,
                'nombres'   => Texto::normalizarEspacios($valores[0]),
                'apellidos' => Texto::normalizarEspacios($valores[1]),
                'tipo_doc'  => Texto::normalizarEspacios($valores[2]),
                'documento' => Texto::normalizarEspacios($valores[3]),
                'correo'    => mb_strtolower(trim($valores[4])),
                'telefono'  => preg_replace('/\s+/', '', $valores[5]) ?? '',
                'password'  => $valores[6],
                'ficha'     => $esAprendiz ? Texto::normalizarEspacios($valores[7] ?? '') : null,
            ];
        }

        if ($registros === []) {
            return $this->fallo([['fila' => '—', 'campo' => 'Archivo', 'mensaje' => 'El archivo no contiene ningún registro para importar.']]);
        }

        // --- Validación completa de TODOS los registros. ---
        $errores = $this->validarRegistros($registros, $esAprendiz, $fichasPermitidas);

        if ($errores !== []) {
            return $this->fallo($errores);
        }

        // --- Importación atómica: o entran todos, o no entra ninguno. ---
        DB::transaction(function () use ($registros, $tipo) {
            foreach ($registros as $r) {
                $this->crearRegistro($tipo, $r);
            }
        });

        return ['exito' => true, 'creados' => count($registros), 'errores' => []];
    }

    /**
     * Valida todos los registros y devuelve la lista de errores (vacía si todo
     * está correcto), con número de fila, campo y descripción.
     *
     * @param  array<int, array<string, mixed>>  $registros
     * @return array<int, array{fila: int|string, campo: string, mensaje: string}>
     */
    private function validarRegistros(array $registros, bool $esAprendiz, ?array $fichasPermitidas): array
    {
        $errores = [];
        $documentosVistos = [];
        $correosVistos = [];

        // Catálogo de fichas activas por número (una sola consulta).
        $fichasActivas = $esAprendiz
            ? Ficha::where('estado_ficha', 'en_ejecucion')->get()->keyBy(fn (Ficha $f) => (string) $f->numero_ficha)
            : collect();

        foreach ($registros as $r) {
            $fila = $r['fila'];

            // Nombres y apellidos: mismas reglas que el registro individual.
            foreach (['Nombres' => $r['nombres'], 'Apellidos' => $r['apellidos']] as $campo => $valor) {
                if ($valor === '') {
                    $errores[] = ['fila' => $fila, 'campo' => $campo, 'mensaje' => 'Es obligatorio.'];
                } elseif (mb_strlen($valor) < 2 || mb_strlen($valor) > 100) {
                    $errores[] = ['fila' => $fila, 'campo' => $campo, 'mensaje' => 'Debe tener entre 2 y 100 caracteres.'];
                } elseif (! preg_match('/^[\pL\s]+$/u', $valor)) {
                    $errores[] = ['fila' => $fila, 'campo' => $campo, 'mensaje' => 'Solo puede contener letras y espacios (sin números ni caracteres especiales).'];
                }
            }

            // Tipo de documento: por etiqueta (desplegable) o por código.
            $tipoDocumento = $this->codigoTipoDocumento($r['tipo_doc']);
            if ($tipoDocumento === null) {
                $errores[] = ['fila' => $fila, 'campo' => 'Tipo de documento', 'mensaje' => $r['tipo_doc'] === '' ? 'Es obligatorio.' : 'Valor inválido: usa la lista desplegable de la plantilla.'];
            }

            // Número de documento: valida según el tipo (CC/TI/CE/PEP vs PPT/PA).
            $errorDocumento = $this->validarNumeroDocumento($r['documento'], $tipoDocumento);
            if ($errorDocumento !== null) {
                $errores[] = ['fila' => $fila, 'campo' => 'Número de documento', 'mensaje' => $errorDocumento];
            } elseif (isset($documentosVistos[$r['documento']])) {
                $errores[] = ['fila' => $fila, 'campo' => 'Número de documento', 'mensaje' => 'Está repetido dentro del archivo (también aparece en la fila ' . $documentosVistos[$r['documento']] . ').'];
            } elseif (Usuario::where('numero_documento', $r['documento'])->exists()) {
                $errores[] = ['fila' => $fila, 'campo' => 'Número de documento', 'mensaje' => 'El documento ya se encuentra registrado en el sistema.'];
            } else {
                $documentosVistos[$r['documento']] = $fila;
            }

            // Correo electrónico (será el usuario de inicio de sesión).
            if ($r['correo'] === '') {
                $errores[] = ['fila' => $fila, 'campo' => 'Correo electrónico', 'mensaje' => 'Es obligatorio.'];
            } elseif (! filter_var($r['correo'], FILTER_VALIDATE_EMAIL) || mb_strlen($r['correo']) > 120) {
                $errores[] = ['fila' => $fila, 'campo' => 'Correo electrónico', 'mensaje' => 'No tiene un formato de correo válido.'];
            } elseif (isset($correosVistos[$r['correo']])) {
                $errores[] = ['fila' => $fila, 'campo' => 'Correo electrónico', 'mensaje' => 'Está repetido dentro del archivo (también aparece en la fila ' . $correosVistos[$r['correo']] . ').'];
            } elseif (Usuario::where('correo', $r['correo'])->exists()) {
                $errores[] = ['fila' => $fila, 'campo' => 'Correo electrónico', 'mensaje' => 'El correo ya existe en el sistema.'];
            } else {
                $correosVistos[$r['correo']] = $fila;
            }

            // Teléfono (opcional, pero si viene debe ser de 10 dígitos).
            if ($r['telefono'] !== '' && ! preg_match('/^\d{10}$/', $r['telefono'])) {
                $errores[] = ['fila' => $fila, 'campo' => 'Teléfono', 'mensaje' => 'Debe contener exactamente 10 dígitos, solo números.'];
            }

            // Contraseña.
            if ($r['password'] === '') {
                $errores[] = ['fila' => $fila, 'campo' => 'Contraseña', 'mensaje' => 'Es obligatoria.'];
            } elseif (mb_strlen($r['password']) < 6 || mb_strlen($r['password']) > 255) {
                $errores[] = ['fila' => $fila, 'campo' => 'Contraseña', 'mensaje' => 'Debe contener mínimo 6 caracteres.'];
            }

            // Ficha (solo aprendices): debe existir, estar activa y (para el
            // instructor) ser una de las fichas donde imparte clases.
            if ($esAprendiz) {
                $ficha = $fichasActivas->get((string) $r['ficha']);
                if ($r['ficha'] === '') {
                    $errores[] = ['fila' => $fila, 'campo' => 'Ficha', 'mensaje' => 'Es obligatoria.'];
                } elseif (! $ficha) {
                    $errores[] = ['fila' => $fila, 'campo' => 'Ficha', 'mensaje' => 'La ficha indicada no existe o no está activa.'];
                } elseif ($fichasPermitidas !== null && ! in_array((int) $ficha->id_ficha, $fichasPermitidas, true)) {
                    $errores[] = ['fila' => $fila, 'campo' => 'Ficha', 'mensaje' => 'Solo puedes matricular aprendices en las fichas donde impartes clases.'];
                }
            }
        }

        return $errores;
    }

    /**
     * Crea un registro ya validado, reutilizando la misma lógica del alta
     * individual (usuario + rol + perfil + matrícula si aplica).
     *
     * @param  array<string, mixed>  $r
     */
    private function crearRegistro(string $tipo, array $r): void
    {
        $datos = [
            'nombres'          => $r['nombres'],
            'apellidos'        => $r['apellidos'],
            'tipo_documento'   => $this->codigoTipoDocumento($r['tipo_doc']),
            'numero_documento' => $r['documento'],
            'correo'           => $r['correo'],
            'telefono'         => $r['telefono'] !== '' ? $r['telefono'] : null,
            'password'         => $r['password'], // crearUsuarioConRol la encripta con Hash::make
        ];

        match ($tipo) {
            'aprendices' => $this->crearAprendizMasivo($datos, $r),
            'instructores' => $this->crearInstructorMasivo($datos),
            'coordinadores' => $this->crearCoordinadorMasivo($datos),
        };
    }

    /** @param array<string, mixed> $datos @param array<string, mixed> $r */
    private function crearAprendizMasivo(array $datos, array $r): void
    {
        $usuario = $this->crearUsuarioConRol($datos, Roles::APRENDIZ);

        $aprendiz = Aprendiz::create([
            'id_usuario'                => $usuario->id_usuario,
            'correo_institucional'      => $datos['correo'],
            'correo_personal'           => $datos['correo'],
            'estado_academico'          => 'en_formacion',
            'tiene_apoyo_sostenimiento' => 0,
        ]);

        $ficha = Ficha::where('numero_ficha', $r['ficha'])->where('estado_ficha', 'en_ejecucion')->firstOrFail();

        // Matrícula única: un aprendiz no puede estar activo en dos fichas.
        Matricula::matricularUnica($aprendiz->id_aprendiz, $ficha->id_ficha);
    }

    /** @param array<string, mixed> $datos */
    private function crearInstructorMasivo(array $datos): void
    {
        $usuario = $this->crearUsuarioConRol($datos, Roles::INSTRUCTOR);

        Instructor::create([
            'id_usuario'        => $usuario->id_usuario,
            'codigo_instructor' => $this->generarCodigoInstructorParaRolAdicional(),
            'estado_instructor' => 'activo',
        ]);
    }

    /** @param array<string, mixed> $datos */
    private function crearCoordinadorMasivo(array $datos): void
    {
        $usuario = $this->crearUsuarioConRol($datos, Roles::COORDINADOR);

        Coordinacion::create([
            'id_usuario'          => $usuario->id_usuario,
            'cargo'               => 'Coordinador',
            'estado_coordinacion' => 'activo',
        ]);
    }

    /**
     * Convierte la etiqueta (o el código) del tipo de documento a su código,
     * o null si no es un valor permitido.
     */
    private function codigoTipoDocumento(string $valor): ?string
    {
        $valor = trim($valor);
        if ($valor === '') {
            return null;
        }

        // Por código directo (CC, TI, CE, PEP, PPT, PA).
        $mayus = mb_strtoupper($valor);
        if (array_key_exists($mayus, PlantillaImportacion::TIPOS_DOCUMENTO)) {
            return $mayus;
        }

        // Por etiqueta del desplegable (sin distinguir mayúsculas/tildes).
        foreach (PlantillaImportacion::TIPOS_DOCUMENTO as $codigo => $etiqueta) {
            if ($this->normalizarEncabezado($etiqueta) === $this->normalizarEncabezado($valor)) {
                return $codigo;
            }
        }

        return null;
    }

    /**
     * Valida el número de documento según su tipo.
     * Retorna el mensaje de error o null si es válido.
     */
    private function validarNumeroDocumento(string $documento, ?string $tipoDocumento): ?string
    {
        if ($documento === '') {
            return 'Es obligatorio.';
        }

        if (in_array($tipoDocumento, ['CC', 'TI', 'CE', 'PEP'], true)) {
            // Solo números, entre 6 y 10 dígitos
            if (! preg_match('/^[0-9]{6,10}$/', $documento)) {
                return 'Debe contener entre 6 y 10 dígitos numéricos, sin espacios ni caracteres especiales.';
            }
        } else {
            // Para PPT y PA (Pasaporte): alfanuméricos, entre 6 y 20 caracteres
            if (! preg_match('/^[A-Za-z0-9]{6,20}$/', $documento)) {
                return 'Debe contener entre 6 y 20 caracteres alfanuméricos, sin espacios ni caracteres especiales.';
            }
        }

        return null;
    }

    /**
     * Normaliza un texto para compararlo sin distinguir mayúsculas, tildes ni
     * espacios repetidos (encabezados y etiquetas del desplegable).
     */
    private function normalizarEncabezado(string $texto): string
    {
        $texto = mb_strtolower(Texto::normalizarEspacios($texto));

        return strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    }

    /**
     * Convierte cualquier valor de celda a texto plano, evitando la notación
     * científica de Excel en números largos (documentos, teléfonos).
     */
    private function aTexto(mixed $valor): string
    {
        if ($valor === null) {
            return '';
        }
        if (is_float($valor) || is_int($valor)) {
            return rtrim(rtrim(number_format((float) $valor, 2, '.', ''), '0'), '.');
        }

        return trim((string) $valor);
    }

    /**
     * Resultado de importación fallida (no se registró nada).
     *
     * @param  array<int, array{fila: int|string, campo: string, mensaje: string}>  $errores
     * @return array{exito: bool, creados: int, errores: array<int, array{fila: int|string, campo: string, mensaje: string}>}
     */
    private function fallo(array $errores): array
    {
        return ['exito' => false, 'creados' => 0, 'errores' => $errores];
    }
}
