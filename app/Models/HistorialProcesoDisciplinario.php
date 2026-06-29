<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialProcesoDisciplinario extends Model
{
    /**
     * La tabla asociada al modelo.
     */
    protected $table = 'historial_proceso_disciplinario';

    /**
     * La clave primaria de la tabla.
     */
    protected $primaryKey = 'id_historial';

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
        'id_proceso',
        'etapa',
        'fecha_registro',
        'id_usuario_registra',
        'descripcion',
        'resultado',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_registro' => 'datetime',
        ];
    }

    /**
     * Relación con el proceso disciplinario.
     */
    public function procesoDisciplinario(): BelongsTo
    {
        return $this->belongsTo(ProcesoDisciplinario::class, 'id_proceso', 'id_proceso');
    }

    /**
     * Relación con el usuario que registró el avance.
     */
    public function usuarioRegistra(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_registra', 'id_usuario');
    }
}
