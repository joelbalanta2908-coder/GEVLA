<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo del Llamado de Atención.
 *
 * Las reglas de negocio aquí implementadas se basan en el Reglamento del
 * Aprendiz SENA (Acuerdo 09 de 2024), especialmente en el Artículo 46
 * "Tipos de medidas formativas", que regula los llamados de atención
 * académicos y disciplinarios, y en los campos reales de la tabla
 * `llamado_atencion`.
 */
class LlamadoAtencion extends Model
{
    protected $table = 'llamado_atencion';

    protected $primaryKey = 'id_llamado';

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
        'calificacion_falta',
        'id_articulo',
        'asunto',
        'descripcion_hechos',
        'pruebas_aportadas',
        'estado_llamado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_llamado' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Catálogos (alineados exactamente con los ENUM de la base de datos)
    |--------------------------------------------------------------------------
    */

    // Campo `tipo_llamado` enum('llamado_escrito','acondicionamiento','cancelacion_matricula')
    public const TIPO_LLAMADO_ESCRITO   = 'llamado_escrito';
    public const TIPO_ACONDICIONAMIENTO = 'acondicionamiento';
    public const TIPO_CANCELACION       = 'cancelacion_matricula';

    // Campo `categoria` enum('academico','disciplinario')
    public const CATEGORIA_ACADEMICO     = 'academico';
    public const CATEGORIA_DISCIPLINARIO = 'disciplinario';

    // Campo `calificacion_falta` enum('leve','grave','muy_grave') — Art. 42 del reglamento
    public const CALIFICACION_LEVE      = 'leve';
    public const CALIFICACION_GRAVE     = 'grave';
    public const CALIFICACION_GRAVISIMA = 'muy_grave';

    // Campo `estado_llamado` enum('registrado','en_revision','notificado','cerrado','cancelado')
    public const ESTADO_REGISTRADO  = 'registrado';
    public const ESTADO_EN_REVISION = 'en_revision';
    public const ESTADO_NOTIFICADO  = 'notificado';
    public const ESTADO_CERRADO     = 'cerrado';
    public const ESTADO_CANCELADO   = 'cancelado';

    /**
     * Número máximo de llamados de atención por categoría antes de pasar al
     * plan de mejoramiento (Reglamento Acuerdo 09 de 2024, Art. 46).
     */
    public const MAX_LLAMADOS_REGLAMENTARIOS = 2;

    /**
     * Tipos de llamado con su etiqueta legible.
     *
     * @return array<string, string>
     */
    public static function tipos(): array
    {
        return [
            self::TIPO_LLAMADO_ESCRITO   => 'Llamado escrito',
            self::TIPO_ACONDICIONAMIENTO => 'Acondicionamiento',
            self::TIPO_CANCELACION       => 'Cancelación de matrícula',
        ];
    }

    /**
     * Categorías de la falta que origina el llamado.
     *
     * @return array<string, string>
     */
    public static function categorias(): array
    {
        return [
            self::CATEGORIA_ACADEMICO     => 'Académico',
            self::CATEGORIA_DISCIPLINARIO => 'Disciplinario',
        ];
    }

    /**
     * Calificaciones de la falta (Art. 42 del reglamento).
     * En la base de datos "gravísima" se almacena como 'muy_grave'.
     *
     * @return array<string, string>
     */
    public static function calificaciones(): array
    {
        return [
            self::CALIFICACION_LEVE      => 'Leve',
            self::CALIFICACION_GRAVE     => 'Grave',
            self::CALIFICACION_GRAVISIMA => 'Gravísima',
        ];
    }

    /**
     * Estados posibles del llamado de atención.
     *
     * @return array<string, string>
     */
    public static function estados(): array
    {
        return [
            self::ESTADO_REGISTRADO  => 'Registrado',
            self::ESTADO_EN_REVISION => 'En revisión',
            self::ESTADO_NOTIFICADO  => 'Notificado',
            self::ESTADO_CERRADO     => 'Cerrado',
            self::ESTADO_CANCELADO   => 'Cancelado',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Accesores de etiquetas legibles
    |--------------------------------------------------------------------------
    */

    public function getTipoLabelAttribute(): string
    {
        return self::tipos()[$this->tipo_llamado] ?? ucfirst((string) str_replace('_', ' ', (string) $this->tipo_llamado));
    }

    public function getCategoriaLabelAttribute(): string
    {
        return self::categorias()[$this->categoria] ?? ucfirst((string) $this->categoria);
    }

    public function getCalificacionLabelAttribute(): string
    {
        return self::calificaciones()[$this->calificacion_falta] ?? '—';
    }

    public function getEstadoLabelAttribute(): string
    {
        return self::estados()[$this->estado_llamado] ?? ucfirst((string) str_replace('_', ' ', (string) $this->estado_llamado));
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes de consulta
    |--------------------------------------------------------------------------
    */

    public function scopeAcademicos(Builder $query): Builder
    {
        return $query->where('categoria', self::CATEGORIA_ACADEMICO);
    }

    public function scopeDisciplinarios(Builder $query): Builder
    {
        return $query->where('categoria', self::CATEGORIA_DISCIPLINARIO);
    }

    public function scopeEnEstado(Builder $query, string $estado): Builder
    {
        return $query->where('estado_llamado', $estado);
    }

    public function scopeDeAprendiz(Builder $query, int $idAprendiz): Builder
    {
        return $query->where('id_aprendiz', $idAprendiz);
    }

    /**
     * Llamados que cuentan para el reglamento: todos menos los cancelados.
     */
    public function scopeVigentes(Builder $query): Builder
    {
        return $query->where('estado_llamado', '!=', self::ESTADO_CANCELADO);
    }

    /*
    |--------------------------------------------------------------------------
    | Reglas del reglamento (Art. 46 — llamados de atención)
    |--------------------------------------------------------------------------
    */

    /**
     * Cuenta los llamados de atención vigentes de un aprendiz.
     * Los cancelados no se contabilizan. Opcionalmente filtra por categoría.
     */
    public static function contarLlamadosDeAprendiz(int $idAprendiz, ?string $categoria = null): int
    {
        return static::query()
            ->deAprendiz($idAprendiz)
            ->vigentes()
            ->when($categoria !== null, fn (Builder $q) => $q->where('categoria', $categoria))
            ->count();
    }

    /**
     * Indica si todavía es posible registrar un nuevo llamado de atención de
     * la categoría dada para el aprendiz (máximo 2 según Art. 46).
     */
    public static function puedeRegistrarseNuevoLlamado(int $idAprendiz, string $categoria): bool
    {
        return static::contarLlamadosDeAprendiz($idAprendiz, $categoria) < self::MAX_LLAMADOS_REGLAMENTARIOS;
    }

    /**
     * Número de orden de este llamado dentro de su categoría para el aprendiz
     * (1 = primer llamado, 2 = segundo llamado, ...). Los cancelados no cuentan.
     */
    public function numeroOrden(): int
    {
        return static::query()
            ->deAprendiz((int) $this->id_aprendiz)
            ->where('categoria', $this->categoria)
            ->vigentes()
            ->where('id_llamado', '<=', (int) $this->id_llamado)
            ->count();
    }

    /**
     * El segundo llamado de atención debe ir acompañado de orientaciones
     * académicas escritas (categoría académica) o de recomendaciones de
     * mejoramiento disciplinario (categoría disciplinaria). Art. 46.
     */
    public function requiereAcompanamiento(): bool
    {
        return $this->numeroOrden() === self::MAX_LLAMADOS_REGLAMENTARIOS;
    }

    /**
     * Indica si el aprendiz ya agotó los llamados de atención permitidos para
     * la categoría de este llamado y, por tanto, procede el plan de
     * mejoramiento (académico o disciplinario).
     */
    public function alcanzoLimiteReglamentario(): bool
    {
        return static::contarLlamadosDeAprendiz((int) $this->id_aprendiz, $this->categoria)
            >= self::MAX_LLAMADOS_REGLAMENTARIOS;
    }

    /**
     * Describe la siguiente acción reglamentaria sugerida según el número de
     * llamados que lleva el aprendiz en esta categoría.
     */
    public function siguienteAccionReglamentaria(): string
    {
        $orden = $this->numeroOrden();
        $esAcademico = $this->categoria === self::CATEGORIA_ACADEMICO;

        return match (true) {
            $orden <= 1 => 'Primer llamado de atención registrado. Puede emitirse un segundo llamado si la conducta persiste.',
            $orden === self::MAX_LLAMADOS_REGLAMENTARIOS => $esAcademico
                ? 'Segundo llamado: debe acompañarse de orientaciones académicas escritas (Art. 46).'
                : 'Segundo llamado: debe acompañarse de recomendaciones de mejoramiento disciplinario (Art. 46).',
            default => $esAcademico
                ? 'Se agotaron los llamados de atención. Procede un plan de mejoramiento académico (Art. 46).'
                : 'Se agotaron los llamados de atención. Procede un plan de mejoramiento disciplinario (Art. 46).',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers de estado del flujo
    |--------------------------------------------------------------------------
    */

    public function estaRegistrado(): bool
    {
        return $this->estado_llamado === self::ESTADO_REGISTRADO;
    }

    public function estaCerrado(): bool
    {
        return $this->estado_llamado === self::ESTADO_CERRADO;
    }

    public function estaCancelado(): bool
    {
        return $this->estado_llamado === self::ESTADO_CANCELADO;
    }

    /**
     * El llamado sigue en curso (ni cerrado ni cancelado).
     */
    public function estaActivo(): bool
    {
        return ! in_array($this->estado_llamado, [self::ESTADO_CERRADO, self::ESTADO_CANCELADO], true);
    }

    /**
     * Solo puede editarse mientras esté en estado "registrado" (antes de que
     * coordinación lo tome en revisión).
     */
    public function puedeEditarse(): bool
    {
        return $this->estaRegistrado();
    }

    /**
     * Indica si este llamado ya derivó en un proceso disciplinario.
     */
    public function generoProcesoDisciplinario(): bool
    {
        return $this->procesosDisciplinarios()->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function aprendiz(): BelongsTo
    {
        return $this->belongsTo(Aprendiz::class, 'id_aprendiz', 'id_aprendiz');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'id_instructor', 'id_instructor');
    }

    public function coordinacion(): BelongsTo
    {
        return $this->belongsTo(Coordinacion::class, 'id_coordinacion', 'id_coordinacion');
    }

    public function usuarioReporta(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_reporta', 'id_usuario');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(ReglamentoArticulo::class, 'id_articulo', 'id_articulo');
    }

    public function faltas(): HasMany
    {
        return $this->hasMany(Falta::class, 'id_llamado', 'id_llamado');
    }

    public function procesosDisciplinarios(): HasMany
    {
        return $this->hasMany(ProcesoDisciplinario::class, 'id_llamado', 'id_llamado');
    }
}
