<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('adicion_producto');
        Schema::dropIfExists('adicion_categoria');
        Schema::dropIfExists('adiciones_catalogo');
        Schema::dropIfExists('barrio_domiciliario');
        Schema::dropIfExists('password_recovery_codes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No recrearemos estas tablas ya que su eliminación es definitiva y parte
        // de la limpieza y refactorización del modelo de datos de la base de datos.
    }
};
