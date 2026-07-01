<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instructor extends Model
{
    /**
     * La tabla asociada al modelo.
     */
    protected $table = 'instructor';

    /**
     * La clave primaria de la tabla.
     */
    protected $primaryKey = 'id_instructor';

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
        'codigo_instructor',
        'area_formacion',
        'estado_instructor',
    ];

    /**
     * Relación con el usuario asociado.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Llamados de atención reportados por este instructor.
     */
    public function llamadosAtencion(): HasMany
    {
        return $this->hasMany(LlamadoAtencion::class, 'id_instructor', 'id_instructor');
    }

    /**
     * Faltas registradas por este instructor.
     */
    public function faltas(): HasMany
    {
        return $this->hasMany(Falta::class, 'id_instructor', 'id_instructor');
    }

    /**
     * Fichas (grupos) en las que este instructor es líder.
     */
    public function fichasLideradas(): HasMany
    {
        return $this->hasMany(Ficha::class, 'id_instructor_lider', 'id_instructor');
    }

    /**
     * Fichas a las que el instructor está asociado (relación muchos a muchos
     * vía la tabla pivote `ficha_instructor`). Incluye aquellas en las que es
     * líder, siempre que se haya registrado la asociación.
     */
    public function fichas(): BelongsToMany
    {
        return $this->belongsToMany(
            Ficha::class,
            'ficha_instructor',
            'id_instructor',
            'id_ficha'
        )->withPivot(['id_ficha_instructor', 'fecha_asignacion']);
    }
}
