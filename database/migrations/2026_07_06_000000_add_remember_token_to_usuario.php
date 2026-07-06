<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columna remember_token en usuario: es donde Laravel guarda la llave del
 * "Recordarme" del login. Sin ella, marcar la casilla rompe el inicio de
 * sesión. Guardada con hasColumn para bases ya importadas por SQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('usuario', 'remember_token')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->rememberToken()->after('password_hash');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('usuario', 'remember_token')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        }
    }
};
