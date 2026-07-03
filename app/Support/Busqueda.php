<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Utilidades para búsquedas flexibles (con inferencia).
 *
 * En lugar de exigir el texto exacto, el término se divide en palabras y cada
 * palabra debe coincidir parcialmente con alguno de los campos buscados. Así,
 * «juan diaz», «diaz juan» o «jua di» encuentran a «Juan Díaz» (la colación
 * utf8mb4_general_ci de la base ya ignora tildes y mayúsculas).
 */
final class Busqueda
{
    /**
     * Divide el término de búsqueda en palabras significativas.
     *
     * @return array<int, string>
     */
    public static function tokens(?string $texto): array
    {
        $tokens = preg_split('/\s+/', trim((string) $texto), -1, PREG_SPLIT_NO_EMPTY);

        return $tokens === false ? [] : array_values($tokens);
    }
}
