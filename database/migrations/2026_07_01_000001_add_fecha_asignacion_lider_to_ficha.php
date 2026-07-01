<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registra la fecha en la que se designó al instructor líder actual de la ficha
 * (requisito del módulo de asignación de instructor líder).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ficha', function (Blueprint $table) {
            if (! Schema::hasColumn('ficha', 'fecha_asignacion_lider')) {
                $table->date('fecha_asignacion_lider')->nullable()->after('id_instructor_lider');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ficha', function (Blueprint $table) {
            if (Schema::hasColumn('ficha', 'fecha_asignacion_lider')) {
                $table->dropColumn('fecha_asignacion_lider');
            }
        });
    }
};
