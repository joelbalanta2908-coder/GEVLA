<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla pivote que permite asociar varios instructores a una misma ficha
 * (relación muchos a muchos). El instructor líder se sigue guardando en
 * `ficha.id_instructor_lider`, pero siempre debe estar también en esta tabla.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ficha_instructor')) {
            return;
        }

        Schema::create('ficha_instructor', function (Blueprint $table) {
            $table->integer('id_ficha_instructor', true); // PK autoincremental (INT con signo, igual que el resto del esquema)
            $table->integer('id_ficha');
            $table->integer('id_instructor');
            $table->date('fecha_asignacion')->nullable();

            $table->unique(['id_ficha', 'id_instructor'], 'uq_ficha_instructor');

            $table->foreign('id_ficha', 'fk_ficha_instructor_ficha')
                ->references('id_ficha')->on('ficha')
                ->cascadeOnDelete();

            $table->foreign('id_instructor', 'fk_ficha_instructor_instructor')
                ->references('id_instructor')->on('instructor')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ficha_instructor');
    }
};
