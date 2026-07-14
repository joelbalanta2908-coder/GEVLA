<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Matricula extends Model
{
    protected $table = 'matricula';

    protected $primaryKey = 'id_matricula';

    public $timestamps = false;

    protected $fillable = [
        'id_aprendiz',
        'id_ficha',
        'fecha_matricula',
        'estado_matricula',
        'es_vocero',
        'tipo_vocero',
    ];

    protected function casts(): array
    {
        return [
            'fecha_matricula' => 'date',
            'es_vocero' => 'boolean',
        ];
    }

    public function aprendiz(): BelongsTo
    {
        return $this->belongsTo(Aprendiz::class, 'id_aprendiz', 'id_aprendiz');
    }

    public function ficha(): BelongsTo
    {
        return $this->belongsTo(Ficha::class, 'id_ficha', 'id_ficha');
    }

    /**
     * Matricula un aprendiz en una ficha garantizando que NUNCA quede activo en
     * dos fichas a la vez: si ya tenía una matrícula activa en otra ficha, esa
     * se marca como "retirada" (es un traslado), y la de la ficha destino queda
     * activa (reactivándola si ya existía). Devuelve la matrícula activa final.
     *
     * Debe invocarse dentro de una transacción por parte del llamador.
     */
    public static function matricularUnica(int $idAprendiz, int $idFicha, ?string $fecha = null): self
    {
        // Traslado: cierra cualquier otra matrícula activa del aprendiz.
        self::where('id_aprendiz', $idAprendiz)
            ->where('id_ficha', '!=', $idFicha)
            ->where('estado_matricula', 'activa')
            ->update(['estado_matricula' => 'retirada']);

        // Activa (o reactiva) la matrícula en la ficha destino.
        return self::updateOrCreate(
            ['id_aprendiz' => $idAprendiz, 'id_ficha' => $idFicha],
            ['fecha_matricula' => $fecha ?? now()->toDateString(), 'estado_matricula' => 'activa']
        );
    }

    /**
     * Indica si el aprendiz ya tiene una matrícula activa en una ficha distinta
     * a la indicada (para avisar que la asociación implicará un traslado).
     */
    public static function tieneOtraFichaActiva(int $idAprendiz, int $idFicha): bool
    {
        return self::where('id_aprendiz', $idAprendiz)
            ->where('id_ficha', '!=', $idFicha)
            ->where('estado_matricula', 'activa')
            ->exists();
    }
}
