<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActaCoordinacion extends Model
{
    /**
     * La tabla asociada al modelo.
     */
    protected $table = 'acta_coordinacion';

    /**
     * La clave primaria de la tabla.
     */
    protected $primaryKey = 'id_acta';

    /**
     * Indica si el modelo debe manejar timestamps automáticos.
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_aprendiz',
        'id_falta',
        'id_proceso',
        'tipo_acta',
        'numero_acta',
        'fecha_expedicion',
        'fecha_notificacion_personal',
        'fecha_firmeza',
        'sancion_descripcion',
        'meses_inhabilitacion',
        'estado_acta',
    ];

    /**
     * Relación con el aprendiz.
     */
    public function aprendiz(): BelongsTo
    {
        return $this->belongsTo(Aprendiz::class, 'id_aprendiz', 'id_aprendiz');
    }

    /**
     * Relación con la falta.
     */
    public function falta(): BelongsTo
    {
        return $this->belongsTo(Falta::class, 'id_falta', 'id_falta');
    }

    /**
     * Relación con el proceso disciplinario (nullable).
     */
    public function procesoDisciplinario(): BelongsTo
    {
        return $this->belongsTo(ProcesoDisciplinario::class, 'id_proceso', 'id_proceso');
    }
}
