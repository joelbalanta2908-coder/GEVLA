<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coordinacion extends Model
{
    /**
     * La tabla asociada al modelo.
     */
    protected $table = 'coordinacion';

    /**
     * La clave primaria de la tabla.
     */
    protected $primaryKey = 'id_coordinacion';

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
        'cargo',
        'dependencia',
        'estado_coordinacion',
    ];

    /**
     * Relación con el usuario asociado.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Llamados de atención asignados a esta coordinación.
     */
    public function llamadosAtencion(): HasMany
    {
        return $this->hasMany(LlamadoAtencion::class, 'id_coordinacion', 'id_coordinacion');
    }
}
