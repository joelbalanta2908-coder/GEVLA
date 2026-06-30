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
}
