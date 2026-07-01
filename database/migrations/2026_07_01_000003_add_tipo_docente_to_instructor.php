<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indica si el instructor dicta una materia (formación específica) o una
 * competencia transversal. Se usa en el módulo de Docentes del coordinador.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instructor', function (Blueprint $table) {
            if (! Schema::hasColumn('instructor', 'tipo_docente')) {
                $table->enum('tipo_docente', ['materia', 'transversal'])->nullable()->after('area_formacion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('instructor', function (Blueprint $table) {
            if (Schema::hasColumn('instructor', 'tipo_docente')) {
                $table->dropColumn('tipo_docente');
            }
        });
    }
};
