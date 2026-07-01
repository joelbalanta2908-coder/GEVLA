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

    /**
     * Niveles de formación disponibles (enum de la columna `nivel`).
     *
     * @return array<string, string>
     */
    public static function niveles(): array
    {
        return [
            'tecnico'   => 'Técnico',
            'tecnologo' => 'Tecnólogo',
            'auxiliar'  => 'Auxiliar',
            'operario'  => 'Operario',
        ];
    }

    public function getNivelLabelAttribute(): string
    {
        return self::niveles()[$this->nivel] ?? ucfirst((string) $this->nivel);
    }

    public function fichas(): HasMany
    {
        return $this->hasMany(Ficha::class, 'id_programa', 'id_programa');
    }
}
