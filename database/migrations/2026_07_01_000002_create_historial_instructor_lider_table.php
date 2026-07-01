<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Auditoría de los cambios de instructor líder de cada ficha. Cada vez que el
 * Coordinador Misional designa (o cambia) el líder se registra una fila con el
 * instructor anterior, el nuevo y el usuario que hizo el cambio.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('historial_instructor_lider')) {
            return;
        }

        Schema::create('historial_instructor_lider', function (Blueprint $table) {
            $table->integer('id_historial_lider', true);
            $table->integer('id_ficha');
            $table->integer('id_instructor_anterior')->nullable();
            $table->integer('id_instructor_nuevo');
            $table->integer('id_usuario_registra');
            $table->dateTime('fecha_cambio')->useCurrent();

            $table->foreign('id_ficha', 'fk_hist_lider_ficha')
                ->references('id_ficha')->on('ficha')
                ->cascadeOnDelete();

            $table->foreign('id_instructor_anterior', 'fk_hist_lider_anterior')
                ->references('id_instructor')->on('instructor')
                ->nullOnDelete();

            $table->foreign('id_instructor_nuevo', 'fk_hist_lider_nuevo')
                ->references('id_instructor')->on('instructor');

            $table->foreign('id_usuario_registra', 'fk_hist_lider_usuario')
                ->references('id_usuario')->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_instructor_lider');
    }
};
