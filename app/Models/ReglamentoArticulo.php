<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Artículo del Reglamento del Aprendiz SENA.
 *
 * Cada artículo puede estar asociado a una calificación de falta
 * (leve, grave, muy_grave) para alimentar el formulario de llamados.
 */
class ReglamentoArticulo extends Model
{
    protected $table = 'reglamento_articulo';

    protected $primaryKey = 'id_articulo';

    public $timestamps = false;

    protected $fillable = [
        'id_capitulo',
        'numero_articulo',
        'titulo',
        'calificacion',
        'contenido',
    ];

    public function scopeDeCalificacion(Builder $query, string $calificacion): Builder
    {
        return $query->where('calificacion', $calificacion);
    }

    public function capitulo(): BelongsTo
    {
        return $this->belongsTo(ReglamentoCapitulo::class, 'id_capitulo', 'id_capitulo');
    }

    public function paragrafos(): HasMany
    {
        return $this->hasMany(ReglamentoParagrafo::class, 'id_articulo', 'id_articulo');
    }

    /**
     * Separa el contenido en un encabezado y una lista numerada, para que la
     * vista pueda mostrar cada numeral en su propia línea con el término
     * inicial en negrita, en vez de un párrafo gigante. Devuelve null cuando
     * el artículo no tiene una lista numerada (se muestra como un solo
     * párrafo, igual que antes).
     *
     * Un marcador válido es "N) " que NO esté precedido de un paréntesis de
     * apertura: así "7) Justificar..." se reconoce como ítem de lista, pero
     * "ocho (8) días" (una aclaración numérica dentro del texto) no se
     * confunde con un marcador.
     *
     * @return array{intro: string, items: array<int, array{numero: int, termino: ?string, texto: string}>}|null
     */
    public function getListaAttribute(): ?array
    {
        $contenido = (string) $this->contenido;

        if (! preg_match('/(?<!\()\b1\)\s/', $contenido)) {
            return null;
        }

        preg_match_all('/(?<!\()(\d+)\)\s+/', $contenido, $marcadores, PREG_OFFSET_CAPTURE);

        if (count($marcadores[0]) < 2) {
            // Un solo marcador no es una lista real (podría ser una coincidencia aislada).
            return null;
        }

        $primerMarcador = $marcadores[0][0];
        $intro = trim(substr($contenido, 0, $primerMarcador[1]));

        $items = [];
        $totalMarcadores = count($marcadores[0]);

        for ($i = 0; $i < $totalMarcadores; $i++) {
            $numero = (int) $marcadores[1][$i][0];
            $inicioTexto = $marcadores[0][$i][1] + strlen($marcadores[0][$i][0]);
            $finTexto = $i + 1 < $totalMarcadores ? $marcadores[0][$i + 1][1] : strlen($contenido);
            $texto = trim(substr($contenido, $inicioTexto, $finTexto - $inicioTexto));

            $termino = null;
            $posDosPuntos = mb_strpos($texto, ':');
            if ($posDosPuntos !== false && $posDosPuntos <= 90) {
                $termino = trim(mb_substr($texto, 0, $posDosPuntos));
                $texto = trim(mb_substr($texto, $posDosPuntos + 1));
            }

            $items[] = ['numero' => $numero, 'termino' => $termino, 'texto' => $texto];
        }

        return ['intro' => $intro, 'items' => $items];
    }
}
