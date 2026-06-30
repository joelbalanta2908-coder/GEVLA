<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReglamentoParagrafo extends Model
{
    protected $table = 'reglamento_paragrafo';

    protected $primaryKey = 'id_paragrafo';

    public $timestamps = false;

    protected $fillable = [
        'id_articulo',
        'numero_paragrafo',
        'contenido',
    ];

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(ReglamentoArticulo::class, 'id_articulo', 'id_articulo');
    }
}
