<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cada artículo del reglamento puede asociarse a una calificación de falta.
        if (! Schema::hasColumn('reglamento_articulo', 'calificacion')) {
            Schema::table('reglamento_articulo', function (Blueprint $table) {
                $table->enum('calificacion', ['leve', 'grave', 'muy_grave'])->nullable()->after('titulo');
            });
        }

        // El llamado de atención registra la calificación de la falta y el artículo infringido.
        Schema::table('llamado_atencion', function (Blueprint $table) {
            if (! Schema::hasColumn('llamado_atencion', 'calificacion_falta')) {
                $table->enum('calificacion_falta', ['leve', 'grave', 'muy_grave'])->nullable()->after('categoria');
            }
            if (! Schema::hasColumn('llamado_atencion', 'id_articulo')) {
                $table->integer('id_articulo')->nullable()->after('calificacion_falta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('llamado_atencion', function (Blueprint $table) {
            if (Schema::hasColumn('llamado_atencion', 'id_articulo')) {
                $table->dropColumn('id_articulo');
            }
            if (Schema::hasColumn('llamado_atencion', 'calificacion_falta')) {
                $table->dropColumn('calificacion_falta');
            }
        });

        if (Schema::hasColumn('reglamento_articulo', 'calificacion')) {
            Schema::table('reglamento_articulo', function (Blueprint $table) {
                $table->dropColumn('calificacion');
            });
        }
    }
};
