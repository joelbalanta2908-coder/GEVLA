<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ficha extends Model
{
    protected $table = 'ficha';

    protected $primaryKey = 'id_ficha';

    public $timestamps = false;

    protected $fillable = [
        'id_programa',
        'id_instructor_lider',
        'numero_ficha',
        'modalidad',
        'estado_ficha',
        'fecha_inicio',
        'fecha_fin_programada',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin_programada' => 'date',
        ];
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaFormacion::class, 'id_programa', 'id_programa');
    }

    public function instructorLider(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'id_instructor_lider', 'id_instructor');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'id_ficha', 'id_ficha');
    }
}
