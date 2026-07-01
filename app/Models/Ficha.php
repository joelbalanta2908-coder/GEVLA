<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ficha extends Model
{
    protected $table = 'ficha';

    protected $primaryKey = 'id_ficha';

    public $timestamps = false;

    protected $fillable = [
        'id_programa',
        'id_instructor_lider',
        'fecha_asignacion_lider',
        'numero_ficha',
        'modalidad',
        'estado_ficha',
        'fecha_inicio',
        'fecha_fin_programada',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin_programada' => 'date',
            'fecha_asignacion_lider' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Catálogos (alineados con los ENUM reales de la tabla `ficha`)
    |--------------------------------------------------------------------------
    */

    // Campo `modalidad` enum('presencial','virtual','distancia')
    public const MODALIDAD_PRESENCIAL = 'presencial';
    public const MODALIDAD_VIRTUAL    = 'virtual';
    public const MODALIDAD_DISTANCIA  = 'distancia';

    // Campo `estado_ficha` enum('en_ejecucion','terminada','cancelada')
    public const ESTADO_EN_EJECUCION = 'en_ejecucion';
    public const ESTADO_TERMINADA    = 'terminada';
    public const ESTADO_CANCELADA    = 'cancelada';

    /**
     * Modalidades disponibles con su etiqueta legible.
     *
     * @return array<string, string>
     */
    public static function modalidades(): array
    {
        return [
            self::MODALIDAD_PRESENCIAL => 'Presencial',
            self::MODALIDAD_VIRTUAL    => 'Virtual',
            self::MODALIDAD_DISTANCIA  => 'A distancia',
        ];
    }

    /**
     * Estados posibles de una ficha con su etiqueta legible.
     *
     * @return array<string, string>
     */
    public static function estados(): array
    {
        return [
            self::ESTADO_EN_EJECUCION => 'En ejecución',
            self::ESTADO_TERMINADA    => 'Terminada',
            self::ESTADO_CANCELADA    => 'Cancelada',
        ];
    }

    public function getModalidadLabelAttribute(): string
    {
        return self::modalidades()[$this->modalidad] ?? ucfirst((string) $this->modalidad);
    }

    public function getEstadoLabelAttribute(): string
    {
        return self::estados()[$this->estado_ficha] ?? ucfirst((string) str_replace('_', ' ', (string) $this->estado_ficha));
    }

    public function estaEnEjecucion(): bool
    {
        return $this->estado_ficha === self::ESTADO_EN_EJECUCION;
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaFormacion::class, 'id_programa', 'id_programa');
    }

    public function instructorLider(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'id_instructor_lider', 'id_instructor');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'id_ficha', 'id_ficha');
    }

    /**
     * Instructores asociados a la ficha (relación muchos a muchos vía pivote).
     */
    public function instructores(): BelongsToMany
    {
        return $this->belongsToMany(
            Instructor::class,
            'ficha_instructor',
            'id_ficha',
            'id_instructor'
        )->withPivot(['id_ficha_instructor', 'fecha_asignacion']);
    }

    /**
     * Historial de designaciones de instructor líder de esta ficha.
     */
    public function historialLider(): HasMany
    {
        return $this->hasMany(HistorialInstructorLider::class, 'id_ficha', 'id_ficha');
    }
}
