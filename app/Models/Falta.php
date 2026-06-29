<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Falta extends Model
{
    /**
     * La tabla asociada al modelo.
     */
    protected $table = 'falta';

    /**
     * La clave primaria de la tabla.
     */
    protected $primaryKey = 'id_falta';

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
        'id_llamado',
        'id_aprendiz',
        'id_instructor',
        'tipo_falta',
        'descripcion_hechos',
        'fecha_ocurrencia',
        'principio_valor_infringido',
        'calificacion_falta',
        'estado_falta',
    ];

    /**
     * Relación con el llamado de atención.
     */
    public function llamadoAtencion(): BelongsTo
    {
        return $this->belongsTo(LlamadoAtencion::class, 'id_llamado', 'id_llamado');
    }

    /**
     * Relación con el aprendiz.
     */
    public function aprendiz(): BelongsTo
    {
        return $this->belongsTo(Aprendiz::class, 'id_aprendiz', 'id_aprendiz');
    }

    /**
     * Relación con el instructor.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'id_instructor', 'id_instructor');
    }
}
