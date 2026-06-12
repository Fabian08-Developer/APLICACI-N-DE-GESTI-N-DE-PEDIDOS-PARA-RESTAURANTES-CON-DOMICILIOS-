<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega coordenadas opcionales a la tabla barrios.
     * Permite ubicar cada barrio en el mapa interactivo.
     */
    public function up(): void
    {
        Schema::table('barrios', function (Blueprint $table) {
            $table->decimal('latitud', 10, 8)->nullable()->after('nombre');
            $table->decimal('longitud', 11, 8)->nullable()->after('latitud');
        });
    }

    public function down(): void
    {
        Schema::table('barrios', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud']);
        });
    }
};
