<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Utilidades de normalización de texto libre (nombres, apellidos y demás
 * campos donde se escriben nombres de personas).
 */
final class Texto
{
    /**
     * Recorta los espacios al inicio y al final, y colapsa cualquier
     * secuencia de espacios internos (incluyendo tabs/saltos) en uno solo.
     *
     * Ejemplo: "  John   Fredy  " -> "John Fredy".
     */
    public static function normalizarEspacios(?string $valor): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $valor) ?? '');
    }
}
