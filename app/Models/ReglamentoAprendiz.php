<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReglamentoAprendiz extends Model
{
    protected $table = 'reglamento_aprendiz';

    protected $primaryKey = 'id_reglamento';

    public $timestamps = false;

    protected $fillable = [
        'nombre_reglamento',
        'version',
        'fecha_vigencia',
        'descripcion',
    ];

    public function capitulos(): HasMany
    {
        return $this->hasMany(ReglamentoCapitulo::class, 'id_reglamento', 'id_reglamento');
    }
}
