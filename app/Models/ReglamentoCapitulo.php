<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Capítulo del Reglamento del Aprendiz SENA.
 */
class ReglamentoCapitulo extends Model
{
    protected $table = 'reglamento_capitulo';

    protected $primaryKey = 'id_capitulo';

    public $timestamps = false;

    protected $fillable = [
        'id_reglamento',
        'numero_capitulo',
        'titulo',
        'descripcion',
    ];

    public function reglamento(): BelongsTo
    {
        return $this->belongsTo(ReglamentoAprendiz::class, 'id_reglamento', 'id_reglamento');
    }

    public function articulos(): HasMany
    {
        return $this->hasMany(ReglamentoArticulo::class, 'id_capitulo', 'id_capitulo')->orderBy('id_articulo');
    }
}
