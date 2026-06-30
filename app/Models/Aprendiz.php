<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aprendiz extends Model
{
    /**
     * La tabla asociada al modelo.
     */
    protected $table = 'aprendiz';

    /**
     * La clave primaria de la tabla.
     */
    protected $primaryKey = 'id_aprendiz';

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
        'id_usuario',
        'correo_institucional',
        'correo_personal',
        'estado_academico',
        'tiene_apoyo_sostenimiento',
    ];

    /**
     * Relación con el usuario asociado.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Llamados de atención del aprendiz.
     */
    public function llamadosAtencion(): HasMany
    {
        return $this->hasMany(LlamadoAtencion::class, 'id_aprendiz', 'id_aprendiz');
    }

    /**
     * Faltas registradas al aprendiz.
     */
    public function faltas(): HasMany
    {
        return $this->hasMany(Falta::class, 'id_aprendiz', 'id_aprendiz');
    }

    /**
     * Actas de coordinación del aprendiz.
     */
    public function actasCoordinacion(): HasMany
    {
        return $this->hasMany(ActaCoordinacion::class, 'id_aprendiz', 'id_aprendiz');
    }

    /**
     * Procesos disciplinarios del aprendiz.
     */
    public function procesosDisciplinarios(): HasMany
    {
        return $this->hasMany(ProcesoDisciplinario::class, 'id_aprendiz', 'id_aprendiz');
    }

    /**
     * Matrículas del aprendiz (lo vinculan a fichas/grupos).
     */
    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'id_aprendiz', 'id_aprendiz');
    }

    /**
     * Notificaciones dirigidas al aprendiz.
     */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'id_aprendiz', 'id_aprendiz');
    }
}
