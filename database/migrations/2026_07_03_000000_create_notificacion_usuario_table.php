<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notificaciones internas por usuario (campanita del panel) con estado de
 * lectura persistente. Equivalente SQL: database/sql/modulo_notificaciones.sql
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notificacion_usuario')) {
            return;
        }

        Schema::create('notificacion_usuario', function (Blueprint $table) {
            $table->increments('id_notificacion_usuario');
            $table->unsignedInteger('id_usuario');
            $table->string('titulo', 150);
            $table->string('mensaje', 500)->nullable();
            $table->string('url', 255)->nullable();
            $table->boolean('leida')->default(false);
            $table->dateTime('fecha_creacion')->useCurrent();

            $table->index(['id_usuario', 'leida'], 'idx_notif_usuario_leida');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion_usuario');
    }
};
