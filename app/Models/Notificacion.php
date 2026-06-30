<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificacion';

    protected $primaryKey = 'id_notificacion';

    public $timestamps = false;

    protected $fillable = [
        'id_aprendiz',
        'id_acta',
        'id_falta',
        'id_llamado',
        'tipo_notificacion',
        'fecha_envio',
        'medio_envio',
        'contenido_resumen',
        'estado_notificacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_envio' => 'date',
        ];
    }

    public function aprendiz(): BelongsTo
    {
        return $this->belongsTo(Aprendiz::class, 'id_aprendiz', 'id_aprendiz');
    }

    public function llamado(): BelongsTo
    {
        return $this->belongsTo(LlamadoAtencion::class, 'id_llamado', 'id_llamado');
    }

    public function acta(): BelongsTo
    {
        return $this->belongsTo(ActaCoordinacion::class, 'id_acta', 'id_acta');
    }
}
