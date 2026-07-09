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

    /**
     * Resalta (subraya) en un texto las palabras del término buscado, para
     * mostrar en los resultados qué coincidió. El texto se escapa primero
     * (seguro frente a HTML) y cada coincidencia se envuelve en <mark>.
     * Ignora mayúsculas y tildes: buscar "plagio" resalta "Plagió".
     */
    public static function resaltar(?string $texto, ?string $termino): string
    {
        $texto = (string) $texto;
        $seguro = e($texto);

        $tokens = self::tokens($termino);
        if ($texto === '' || $tokens === []) {
            return $seguro;
        }

        // Cada letra del token acepta también sus variantes con tilde, para
        // que la coincidencia funcione igual que la colación de la base.
        $equivalencias = [
            'a' => '[aáà]', 'e' => '[eéè]', 'i' => '[ií]', 'o' => '[oó]', 'u' => '[uúü]', 'n' => '[nñ]',
        ];

        $patrones = array_map(function (string $token) use ($equivalencias): string {
            $letras = preg_split('//u', mb_strtolower($token), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $regex = '';
            foreach ($letras as $letra) {
                // Si el usuario escribió la letra CON tilde, se lleva a su base
                // para que también encuentre la versión sin tilde.
                $base = strtr($letra, ['á' => 'a', 'à' => 'a', 'é' => 'e', 'è' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
                $regex .= $equivalencias[$base] ?? preg_quote($letra, '/');
            }

            return $regex;
        }, $tokens);

        $patron = '/(' . implode('|', array_filter($patrones)) . ')/iu';

        return (string) preg_replace(
            $patron,
            '<mark class="rounded bg-[#39A900]/20 px-0.5 font-semibold text-[#1e6a00] underline decoration-[#39A900] decoration-2 underline-offset-2">$1</mark>',
            $seguro
        );
    }
}
