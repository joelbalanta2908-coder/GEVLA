<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de auditoría de cada designación/cambio de instructor líder de una
 * ficha. Solo lo escribe el módulo de fichas cuando el Coordinador Misional
 * asigna un líder.
 */
class HistorialInstructorLider extends Model
{
    protected $table = 'historial_instructor_lider';

    protected $primaryKey = 'id_historial_lider';

    public $timestamps = false;

    protected $fillable = [
        'id_ficha',
        'id_instructor_anterior',
        'id_instructor_nuevo',
        'id_usuario_registra',
        'fecha_cambio',
    ];

    protected function casts(): array
    {
        return [
            'fecha_cambio' => 'datetime',
        ];
    }

    public function ficha(): BelongsTo
    {
        return $this->belongsTo(Ficha::class, 'id_ficha', 'id_ficha');
    }

    public function instructorAnterior(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'id_instructor_anterior', 'id_instructor');
    }

    public function instructorNuevo(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'id_instructor_nuevo', 'id_instructor');
    }

    public function usuarioRegistra(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_registra', 'id_usuario');
    }
}
