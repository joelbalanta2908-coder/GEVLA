<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    /**
     * La tabla asociada al modelo.
     */
    protected $table = 'rol';

    /**
     * La clave primaria de la tabla.
     */
    protected $primaryKey = 'id_rol';

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
        'nombre_rol',
    ];

    /**
     * Relación muchos a muchos con los usuarios.
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'usuario_rol',
            'id_rol',
            'id_usuario'
        )->withPivot('estado_asignacion');
    }
}
