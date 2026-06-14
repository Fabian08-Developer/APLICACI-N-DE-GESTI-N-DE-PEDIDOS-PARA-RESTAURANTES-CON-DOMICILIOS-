<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega barrio_id a sesiones_cliente para registrar el barrio exacto
     * seleccionado por el cliente en el checkout de domicilio.
     * Esto permite calcular la tarifa dinámica de envío correctamente.
     */
    public function up(): void
    {
        Schema::table('sesiones_cliente', function (Blueprint $table) {
            $table->uuid('barrio_id')->nullable()->after('zona_id');
            $table->foreign('barrio_id')->references('id')->on('barrios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sesiones_cliente', function (Blueprint $table) {
            $table->dropForeign(['barrio_id']);
            $table->dropColumn('barrio_id');
        });
    }
};
