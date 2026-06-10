<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sesiones_cliente', function (Blueprint $table) {
            $table->decimal('latitud', 10, 8)->nullable()->after('direccion_cliente');
            $table->decimal('longitud', 11, 8)->nullable()->after('latitud');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->decimal('latitud_entrega', 10, 8)->nullable()->after('direccion_entrega');
            $table->decimal('longitud_entrega', 11, 8)->nullable()->after('latitud_entrega');
        });

        Schema::table('perfiles_domiciliario', function (Blueprint $table) {
            $table->decimal('latitud', 10, 8)->nullable()->after('estado');
            $table->decimal('longitud', 11, 8)->nullable()->after('latitud');
            $table->timestamp('ultima_ubicacion_en')->nullable()->after('longitud');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesiones_cliente', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud']);
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['latitud_entrega', 'longitud_entrega']);
        });

        Schema::table('perfiles_domiciliario', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud', 'ultima_ubicacion_en']);
        });
    }
};
