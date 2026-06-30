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
}
