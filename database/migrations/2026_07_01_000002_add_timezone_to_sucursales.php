<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega campo timezone a sucursales.
 * Default: America/Bogota (zona horaria de Colombia).
 * Permite que cada sucursal opere en su propia zona horaria
 * para calculos de anticipacion, no-shows y apertura/cierre.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->string('timezone', 60)->default('America/Bogota')->after('hora_cierre');
        });
    }

    public function down(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};
