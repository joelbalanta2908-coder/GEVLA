<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcesoDisciplinario extends Model
{
    /**
     * La tabla asociada al modelo.
     */
    protected $table = 'proceso_disciplinario';

    /**
     * La clave primaria de la tabla.
     */
    protected $primaryKey = 'id_proceso';

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
        'id_llamado',
        'etapa_actual',
        'fecha_inicio',
        'fecha_cierre',
        'estado_proceso',
        'observaciones',
    ];

    /**
     * Relación con el aprendiz.
     */
    public function aprendiz(): BelongsTo
    {
        return $this->belongsTo(Aprendiz::class, 'id_aprendiz', 'id_aprendiz');
    }

    /**
     * Relación con el llamado de atención que originó el proceso.
     */
    public function llamadoAtencion(): BelongsTo
    {
        return $this->belongsTo(LlamadoAtencion::class, 'id_llamado', 'id_llamado');
    }

    /**
     * Historial de avances del proceso disciplinario.
     */
    public function historial(): HasMany
    {
        return $this->hasMany(HistorialProcesoDisciplinario::class, 'id_proceso', 'id_proceso')
            ->orderBy('fecha_registro');
    }

    /**
     * Actas de coordinación asociadas al proceso.
     */
    public function actasCoordinacion(): HasMany
    {
        return $this->hasMany(ActaCoordinacion::class, 'id_proceso', 'id_proceso');
    }
}
