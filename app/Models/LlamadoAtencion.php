<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LlamadoAtencion extends Model
{
    /**
     * La tabla asociada al modelo.
     */
    protected $table = 'llamado_atencion';

    /**
     * La clave primaria de la tabla.
     */
    protected $primaryKey = 'id_llamado';

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
        'id_instructor',
        'id_coordinacion',
        'id_usuario_reporta',
        'fecha_llamado',
        'tipo_llamado',
        'categoria',
        'asunto',
        'descripcion_hechos',
        'pruebas_aportadas',
        'estado_llamado',
        'observaciones',
    ];

    /**
     * Relación con el aprendiz asociado.
     */
    public function aprendiz(): BelongsTo
    {
        return $this->belongsTo(Aprendiz::class, 'id_aprendiz', 'id_aprendiz');
    }

    /**
     * Relación con el instructor que generó el llamado.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'id_instructor', 'id_instructor');
    }

    /**
     * Relación con la coordinación asignada (nullable).
     */
    public function coordinacion(): BelongsTo
    {
        return $this->belongsTo(Coordinacion::class, 'id_coordinacion', 'id_coordinacion');
    }

    /**
     * Relación con el usuario que reporta.
     */
    public function usuarioReporta(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_reporta', 'id_usuario');
    }

    /**
     * Faltas asociadas a este llamado de atención.
     */
    public function faltas(): HasMany
    {
        return $this->hasMany(Falta::class, 'id_llamado', 'id_llamado');
    }

    /**
     * Procesos disciplinarios originados por este llamado.
     */
    public function procesosDisciplinarios(): HasMany
    {
        return $this->hasMany(ProcesoDisciplinario::class, 'id_llamado', 'id_llamado');
    }
}
