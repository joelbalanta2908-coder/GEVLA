<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramaFormacion extends Model
{
    protected $table = 'programa_formacion';

    protected $primaryKey = 'id_programa';

    public $timestamps = false;

    protected $fillable = [
        'codigo_programa',
        'nombre_programa',
        'nivel',
        'duracion_meses',
    ];

    public function fichas(): HasMany
    {
        return $this->hasMany(Ficha::class, 'id_programa', 'id_programa');
    }
}
